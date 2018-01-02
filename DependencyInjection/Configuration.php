<?php

namespace Craue\ConfigBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration for the bundle.
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-present Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class Configuration implements ConfigurationInterface {

	/**
	 * {@inheritDoc}
	 */
	public function getConfigTreeBuilder() {
		$supportedDrivers = array('doctrine_orm');

		$treeBuilder = new TreeBuilder();
		$treeBuilder->root('craue_config')
			->children()
				// TODO replace by `->enumNode('db_driver')->values($supportedDrivers)->defaultValue($supportedDrivers[0])->end()` when at least two values are defined or as soon as Symfony >= 3.1 is required
				->scalarNode('db_driver')
					->defaultValue($supportedDrivers[0])
					->validate()
						->ifNotInArray($supportedDrivers)
						->thenInvalid('The driver "%s" is not supported. Please choose one of ' . json_encode($supportedDrivers))
					->end()
				->end()
				->scalarNode('entity_name')
					->defaultValue('Craue\ConfigBundle\Entity\Setting')
				->end()
			->end()
		;

		return $treeBuilder;
	}

}
