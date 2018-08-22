<?php

/**
 * Contao Open Source CMS
 */

/**
 * @file config.php
 * @author Sascha Weidner
 * @package sioweb.apply_environment
 * @copyright Sioweb, Sascha Weidner
 */


if (TL_MODE == 'BE')
{
    if(empty($GLOBALS['TL_JAVASCRIPT'])) {
        $GLOBALS['TL_JAVASCRIPT'] = [];
    }
	array_unshift($GLOBALS['TL_JAVASCRIPT'], 'bundles/siowebapplyenvironment/js/apply2environment.js');
	array_unshift($GLOBALS['TL_JAVASCRIPT'], 'bundles/siowebapplyenvironment/js/jquery.min-3.3.1.js');
	$GLOBALS['TL_CSS'][] = 'bundles/siowebapplyenvironment/css/apply2environment.css';
}

$GLOBALS['TL_HOOKS']['parseWidget'][] = ['Sioweb\ApplyEnvironment\Widgets\Widget', 'parseWidget'];

array_insert($GLOBALS['TL_MAINTENANCE'],1,array(
	'Sioweb\ApplyEnvironment\Contao\ApplyEnvironment'
));