<?php

namespace Sioweb\ApplyEnvironment\Controller;

use Contao\File;
use Contao\Input;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;

class ApplyToEnvironmentController extends Controller {

    public function indexAction() {
        return new JsonResponse([
            'success' => 0,
            'error' => 'Invalid route!',
        ]);
    }

    public function applyAction($environment) {
        
        if(empty($_POST)) {
            return new JsonResponse([
                'success' => 0,
                'error' => 'Bad request!',
            ]);
        }
        // Array
        // (
        //     [title] => Localhost
        // )
        // Array
        // (
        //     [id] => 1
        //     [table] => tl_page
        //     [name] => title
        //     [valueOnLoad] => de
        //     [form] => de
        // )
        
        $Environments = $this->container->getParameter('apply_environments.environments');
        
        foreach($Environments as $type => $env) {
            if(empty($env['short'])) {
                $env['short'] = $type;
            }

            if($env['short'] === $environment) {
                $envPath = $this->container->getParameter('kernel.project_dir').'/environments/'.$env['short'];
                if(!is_dir($envPath)) {
                    mkdir($envPath, 0700, true);
                }

                if(file_exists($envPath.'/'.$type.'.yml')) {
                    $arrEnvironment = Yaml::parseFile($envPath.'/'.$type.'.yml');
                }

                if(empty($arrEnvironment['apply_environment'])) {
                    $arrEnvironment = [
                        'apply_environment' => [
                            'sql' => [
                                'insert' => [],
                                'update' => [],
                                'delete' => []
                            ],
                            'globals' => []
                        ]
                    ];
                }

                if(empty($arrEnvironment['apply_environment']['sql']['update'][Input::post('table')][Input::post('id')])) {
                    $arrEnvironment['apply_environment']['sql']['update'][Input::post('table')][Input::post('id')] = [];
                }

                $Value = Input::post('form');
                $arrEnvironment['apply_environment']['sql']['update'][Input::post('table')][Input::post('id')][Input::post('name')] = $Value;
                
                $file = new File('app/environments/'.$env['short'].'/'.$type.'.yml');
                $file->write(Yaml::dump($arrEnvironment,20));
                $file->close();
            }
        }

        return new JsonResponse([
            'success' => true,
            'env' => $environment,
            'data' => $arrEnvironment['apply_environment']
        ]);
    }

}