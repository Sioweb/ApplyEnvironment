<?php

/**
 * Contao Open Source CMS
 */

/**
 * @file Widget.php
 * @class Widget
 * @author Sascha Weidner
 * @package sioweb.apply_environment
 * @copyright Sioweb, Sascha Weidner
 */


namespace Sioweb\ApplyEnvironment\Widgets;
use Contao\System;
use Contao\FrontendTemplate;

class Widget {
    public function parseWidget($strBuffer, $objWidget) {
        
        $container = System::getContainer();
        $objSelect = new FrontendTemplate('apply2environment_select');
        $objSelect->environments = $container->getParameter('apply_environments.environments');


        $objSelect->name = $objWidget->name;

        $objSelect->widget = json_encode([
            'id' => $objWidget->dataContainer->activeRecord->id,
            'table' => $objWidget->dataContainer->table,
            'name' => $objWidget->name,
            'valueOnLoad' => $objWidget->value,
            'form' => []
        ]);

        $strEnvironments = $objSelect->parse();

        if(strpos($strBuffer, '<legend>')) {
            return preg_replace('|(<\/legend>)|is', $strEnvironments.'$1', $strBuffer);
        }
        
        return preg_replace(
            '|(<\/label>)|is',
            $strEnvironments.'$1',
            $strBuffer
        );
    }
}


