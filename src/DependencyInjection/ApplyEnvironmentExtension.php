<?php

namespace Sioweb\ApplyEnvironment\DependencyInjection;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ApplyEnvironmentExtension extends Extension
{
	/**
	 * {@inheritdoc}
	 */
	public function getAlias()
	{
		return 'sioweb_apply_environment';
    }
    
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $baseConfig = Yaml::parse(file_get_contents(__DIR__.'/../Resources/config/environments.yml'), Yaml::PARSE_CONSTANT);
        $configs = array_filter(array_merge([$baseConfig['apply_environments']], $configs));

        $rootDir = $container->getParameter('kernel.root_dir');
        if (file_exists($rootDir.'/config/environments.yml')) {
            
            $root_baseConfig = Yaml::parse(file_get_contents($rootDir.'/config/environments.yml'), Yaml::PARSE_CONSTANT);
            $configs = [array_filter(array_merge($configs[0], $root_baseConfig['apply_environments']))];
        }
        
        $mergedConfig = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );

        $loader->load('listener.yml');
        $loader->load('services.yml');

		$container->setParameter('apply_environments.environments', $mergedConfig['environments']);
    }
}
