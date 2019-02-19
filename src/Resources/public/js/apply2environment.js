jQuery.noConflict();

(function ($) {

    /**
     * @author Sascha Weidner
     * @brief Replace input dropdown with list 
     */

    "use strict";

    var pluginName = 'select',
        standardOptions = {
            template: null,
            debug: false,
            enabled: true,
            equalValues: true,
            container: null,
            item: null,
            maxOffset: 99999,
            label: '%s',
            options: '%s',
            closeOnSelect: true,
            updated: function ($li, selectObj) {},
        },
        PluginClass = function () {

            var selfObj = this,
                img = null;
            this.item = false;

            this.initOptions = new Object(standardOptions);

            this.init = function (elem) {
                selfObj = this;

                if (!this.container) {
                    this.container = $('body');
                }

                this.elem = elem;
                this.item = $(elem);
                this.container = $(this.container);

                if (this.item.data('label') !== undefined) {
                    this.label = this.item.data('label');
                }

                this.loaded();
            };

            this.disable = function () {
                clearTimeout(selfObj.scrollTimeOut);
                selfObj.enabled = false;
            };

            this.enable = function () {
                selfObj.enabled = true;
            };

            this.loaded = function () {
                var $label = null,
                    $ul = null,
                    selectHeight = 0,
                    containerHeight = (selfObj.container.offset() !== undefined ? selfObj.container.offset().top : 0) + selfObj.container.height();
  
                if (!selfObj.enabled) {
                    return;
                }

                if (!selfObj.template) {
                    var selected = selfObj.item.find('option:selected');
                    
                    if (selected.length > 0) {
                        selected = selected.html();
                    } else {
                        selected = selfObj.item.find('option').eq(0).html();
                    }

                    selfObj.template = '<div class="ui-select">';
                    selfObj.template += '<span class="label">' + selfObj.label.replace('%s', selected) + '</span>';
                    selfObj.template += '<ul>';
                    $.each(selfObj.item.find('option'), function (key, option) {
                        var $option = $(option);
                        selfObj.template += '<li' + (selected == $option.html() ? ' data-selected="true"' : '') + ' data-value="' + $option.prop('value') + '">' + selfObj.options.replace('%s', $option.html()) + '</li>';
                    });

                    selfObj.template += '</ul></div>';
                }

                selfObj.$template = $(selfObj.template).insertAfter(selfObj.item);
                selfObj.item.hide();
                selfObj.item.data('ui-select', 1).get(0).setAttribute('data-ui-select', 1);
                
                $ul = selfObj.$template.find('ul').eq(0);
                $ul.css('max-height', 'none');
                selectHeight = $ul.height();
                $ul.css({ 'max-height': '' });

                if ($ul.offset().top + selectHeight > containerHeight) {
                    $ul.css('top', (containerHeight - $ul.offset().top) - Math.min(selfObj.maxOffset, selectHeight));
                }


                $label = selfObj.$template.find('.label').eq(0);
                selfObj.$template.click(function (e) {
                    e.stopPropagation();
                    e.preventDefault();
                    $(this).toggleClass('open');
                });

                $(document).click(function () {
                    selfObj.$template.removeClass('open');
                });

                selfObj.$template.find('li').click(function (e) {
                    var $el = $(this);

                    if(!selfObj.closeOnSelect) {
                        e.stopPropagation();
                        e.preventDefault();
                    }

                    selfObj.$template.find('li').removeAttr('data-selected');
                    $label.html(selfObj.label.replace('%s', $el.html()));
                    selfObj.item.find('option').prop('selected', false);
                    $el.attr('data-selected', 'true');
                    selfObj.item.find('option[value="' + $el.data('value') + '"]').prop('selected', true);

                    selfObj.updated($el, selfObj);
                });
            };

            /**
             *  {
             *      "options": {
             *          "0": {
             *              "value": 0
             *          }
             *      }
             *  }
             */
            this.parseName = function(nameArray, inputName, inputValue) {
                var regexp = new RegExp(/\[([^\]]+)\]/, 'gmi'),
                    _match = null;
                
                if(inputValue === undefined) {
                    return nameArray;
                }

                if(_match = inputName.match(regexp)) {
                    var cleanName = inputName.replace(/\[.*/,''),
                        arrLength = _match.length;

                    if(nameArray[cleanName] === undefined) {
                        nameArray[cleanName] = {};
                    }

                    cleanName = nameArray[cleanName];

                    for(var i = 0; i < arrLength; i++) {
                        var value = _match[i].match(/\[(.*)\]/)[1];
                        if(arrLength > (i+1)) {
                            if(cleanName[value] === undefined) {
                                cleanName[value] = {};
                            }
                            cleanName = cleanName[value]
                        } else {
                            cleanName[value] = inputValue;
                            cleanName = cleanName[value];
                        }
                    }
                } else {
                    nameArray[inputName] = inputValue;
                }

                return nameArray;
            };
        };

    $[pluginName] = $.fn[pluginName] = function (settings) {
        var element = typeof this === 'function' ? $('html') : this,
            newData = arguments[1] || {},
            returnElement = [];

        returnElement[0] = element.each(function (k, i) {
            var pluginClass = $.data(this, pluginName),
                args = Array.prototype.slice.call(arguments);

            if (!settings || typeof settings === 'object' || settings === 'init') {

                if (!pluginClass) {
                    if (settings === 'init')
                        settings = args[1] || {};
                    pluginClass = new PluginClass();

                    var newOptions = new Object(pluginClass.initOptions);
                    if (settings)
                        newOptions = $.extend(true, {}, newOptions, settings);
                    pluginClass = $.extend(newOptions, pluginClass);
                    /** Initialisieren. */
                    pluginClass.init(this);
                    $.data(this, pluginName, pluginClass);
                } else {
                    return;
                }
            } else if (!pluginClass) {
                return;
            } else if (pluginClass[settings]) {
                var method = settings;
                returnElement[1] = pluginClass[method](newData);
            } else {
                return;
            }
        });

        if (returnElement[1] !== undefined) return returnElement[1];
        return returnElement[0];

    };

})(jQuery);


(function ($) {
    var addApplyment = function(selector) {
        $(selector).each(function() {
            $(this).select({
                closeOnSelect: false,
                updated: function($li, selectObj) {
                    var widgetData = selectObj.item.data('widget'),
                        formValues = [],
                        serializedFormData = $li.closest('form').serializeArray();

                    widgetData.form = null;
                    if($li.data('value') === '') {
                        return;
                    }
                    if(serializedFormData.length) {
                        for(var key in serializedFormData) {
                            if(serializedFormData[key].name.indexOf('a2e_') === 0) {
                                continue;
                            }
                            
                            formValues = selectObj.parseName(formValues, serializedFormData[key].name,serializedFormData[key].value);
                        }
                    }

                    widgetData.form = formValues[widgetData.name];
                    
                    $.ajax({
                        method: 'POST',
                        dataType: 'json',
                        data: widgetData,
                        url: '/sioweb/a2e/api/'+$li.data('value'),
                        success: function() {

                        }
                    });
                }
            });
        });
    };
    window.addEvent('ajax_change', function() {
        addApplyment('.apply2environment:not([data-ui-select="1]');
    });

    $(function () {
        addApplyment('.apply2environment');
    });
})(jQuery);