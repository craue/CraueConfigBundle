<?php

namespace Craue\ConfigBundle\DependencyInjection;

use Craue\ConfigBundle\Entity\Setting;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration for the bundle.
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2018 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class Configuration implements ConfigurationInterface {

	/**
	 * {@inheritDoc}
	 */
	public function getConfigTreeBuilder() {
		$supportedDrivers = array('doctrine_orm');

		$treeBuilder = new TreeBuilder('craue_config');

		if (!method_exists($treeBuilder, 'getRootNode')) {
			// TODO remove as soon as Symfony >= 4.2 is required
			$rootNode = $treeBuilder->root('craue_config');
		} else {
			$rootNode = $treeBuilder->getRootNode();
		}

		$rootNode
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
