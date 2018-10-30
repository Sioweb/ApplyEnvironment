<?php


namespace Sioweb\ApplyEnvironment\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function getConfigTreeBuilder()
	{
		$treeBuilder = new TreeBuilder();
		$rootNode = $treeBuilder->root('apply_environments');

		$rootNode
			->children()
				->arrayNode('environments')
					->requiresAtLeastOneElement()
					->prototype('array')
						->children()
							->scalarNode('title')->end()
							->scalarNode('short')->end()
							->booleanNode('prod')->end()
							->booleanNode('hideInBackend')->end()
						->end()
					->end()
				->end()
			->end();

    	return $treeBuilder;

	}
}
