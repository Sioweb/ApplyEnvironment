<?php

namespace Sioweb\ApplyEnvironment\ContaoManager;

use Sioweb\ApplyEnvironment\SiowebApplyEnvironmentBundle;
use Contao\CoreBundle\ContaoCoreBundle;
use Symfony\Component\HttpKernel\KernelInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Routing\RoutingPluginInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Contao\ManagerPlugin\Config\ContainerBuilder;
use Contao\ManagerPlugin\Config\ExtensionPluginInterface;
use Contao\ManagerPlugin\Config\ContainerBuilder as PluginContainerBuilder;
use Contao\ManagerBundle\ContaoManagerBundle;

/**
 * Plugin for the Contao Manager.
 *
 * @author Sascha Weidner <https://www.sioweb.de>
 */
class Plugin implements BundlePluginInterface, RoutingPluginInterface, ExtensionPluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(SiowebApplyEnvironmentBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class, ContaoManagerBundle::class]),
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function getRouteCollection(LoaderResolverInterface $resolver, KernelInterface $kernel)
    {
        return $resolver
            ->resolve(__DIR__.'/../Resources/config/routing.yml')
            ->load(__DIR__.'/../Resources/config/routing.yml')
        ;
    }
    
    /**
     * Allows a plugin to override extension configuration.
     *
     * @param string           $extensionName
     * @param array            $extensionConfigs
     * @param ContainerBuilder $container
     *
     * @return array
     */
    public function getExtensionConfig($extensionName, array $extensionConfigs, PluginContainerBuilder $container)
    {
        
        if ($extensionName !== 'security') {
            return $extensionConfigs;
        }


        foreach ($extensionConfigs as &$extensionConfig) {
            if (isset($extensionConfig['firewalls'])) {

                // Add e.g. your own security authentication provider
                $extensionConfig['providers']['sioweb.apply_environment.user_provider'] = [
                    'id' => 'sioweb.apply_environment.user_provider'
                ];

                $offset = (int) array_search('frontend', array_keys($extensionConfig['firewalls']));

                $extensionConfig['firewalls'] = array_merge(
                    array_slice($extensionConfig['firewalls'], 0, $offset, true),
                    [
                        'apply_environment' => [
                            'pattern' => '/sioweb/a2e/api/*',
                            'anonymous' => true,
                            'provider' => 'sioweb.apply_environment.user_provider',
                            'guard' => [
                                'authenticators' => [
                                    'sioweb.security.cm_authenticator'
                                ],
                            ],
                        ],
                        // 'apply_environment' => $extensionConfig['firewalls']['contao_frontend'],
                    ],
                    array_slice($extensionConfig['firewalls'], $offset+1, null, true)
                );

                break;
            }
        }

        // print_r($extensionConfigs);
        // die();
        return $extensionConfigs;
    }

}

