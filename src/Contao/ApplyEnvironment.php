<?php

namespace Sioweb\ApplyEnvironment\Contao;
use Contao;
use Contao\Input;
use Contao\Model;
use Contao\System;
use Contao\Environment;
use Contao\BackendTemplate;
use Symfony\Component\Yaml\Yaml;

class ApplyEnvironment extends \Backend implements \executable {

    public function isActive() {
        return false;
    }

	/**
	 * Generate the module
	 *
	 * @return string
	 */
	public function run()
	{
		/** @var BackendTemplate|object $objTemplate */
		$objTemplate = new BackendTemplate('be_apply_environment');
		$objTemplate->action = ampersand(Environment::get('request'));
		$objTemplate->headline = $GLOBALS['TL_LANG']['tl_maintenance']['apply_environment'];
        $objTemplate->isActive = $this->isActive();
        
        $container = System::getContainer();

		// Toggle the maintenance mode
		if (Input::post('FORM_SUBMIT') == 'tl_maintenance_apply_environment')
		{
            $environment = Input::post('apply_environment');
            $arrEnvironment = [];
                
            $Environments = $container->getParameter('apply_environments.environments');
            
            foreach($Environments as $type => $env) {
                if(empty($env['short'])) {
                    $env['short'] = $type;
                }

                if($env['short'] === $environment) {
                    $envPath = $container->getParameter('kernel.root_dir').'/environments/'.$env['short'];

                    if(file_exists($envPath.'/'.$type.'.yml')) {
                        $arrEnvironment = Yaml::parseFile($envPath.'/'.$type.'.yml');
                    } else {
                        $this->reload();
                        die();
                    }

                    break;
                }
            }

            $sql = [];
            
            foreach($arrEnvironment['apply_environment']['sql']['update'] as $table => $arrQuery) {
                foreach($arrQuery as $id => $fields) {

                    /** @var Model $strModelClass */
                    $strModelClass = \Model::getClassFromTable($table);

                    // Load the model
                    if (class_exists($strModelClass))
                    {
                        $objHybrid = $strModelClass::findByPk($id);

                        if ($objHybrid === null)
                        {
                            return;
                        }

                        foreach($fields as $field => $value) {
                            $objHybrid->{$field} = $value;
                        }

                        $objHybrid->save();
                    }
                }
            }
            
			$this->reload();
        }
        
        $objTemplate->activeEnvironment = $container->getParameter('kernel.environment');
        $objTemplate->environments = $container->getParameter('apply_environments.environments');

        $objTemplate->class= 'tl_info';
        $objTemplate->submit = $GLOBALS['TL_LANG']['tl_maintenance']['apply_environment_button'];
        $objTemplate->label = $GLOBALS['TL_LANG']['tl_maintenance']['apply_environment_label'];
        $objTemplate->help = $GLOBALS['TL_LANG']['tl_maintenance']['apply_environment_help'];
		
		return $objTemplate->parse();
	}
}