<?php

namespace Craue\ConfigBundle\DependencyInjection;

use Craue\ConfigBundle\Entity\Setting;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Semantic bundle configuration.
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2022 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class Configuration implements ConfigurationInterface {

	/**
	 * {@inheritDoc}
	 */
	public function getConfigTreeBuilder() : TreeBuilder {
		$supportedDrivers = ['doctrine_orm'];

		$treeBuilder = new TreeBuilder('craue_config');

		$treeBuilder->getRootNode()
			->children()
				->enumNode('db_driver')
					->values($supportedDrivers)
					->defaultValue($supportedDrivers[0])
				->end()
				->scalarNode('entity_name')
					->defaultValue(Setting::class)
				->end()
			->end()
		;

		return $treeBuilder;
	}

}
