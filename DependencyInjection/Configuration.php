<?php declare(strict_types=1);

namespace Craue\ConfigBundle\DependencyInjection;

use Craue\ConfigBundle\Entity\Setting;
use Craue\ConfigBundle\Entity\SettingInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Semantic bundle configuration.
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2026 Christian Raue
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
				// TODO change `scalarNode` to `stringNode` (and remove the custom string validation) as soon as Symfony >= 7.2 is required
				->scalarNode('entity_name')
					->defaultValue(Setting::class)
					->cannotBeEmpty()
					->validate()
						->ifTrue(static fn(mixed $value) : bool => !is_string($value))
						->thenInvalid('The value %s is not a string.')
					->end()
					->validate()
						->ifTrue(static fn(string $class) : bool => !class_exists($class))
						->thenInvalid('The class %s doesn\'t exist.')
					->end()
					->validate()
						->ifTrue(static function(string $class) : bool {
							/** @var class-string $class */
							$reflection = new \ReflectionClass($class);
							return $reflection->isAbstract() || !$reflection->implementsInterface(SettingInterface::class);
						})
						->thenInvalid('The class %s must be a non-abstract implementation of ' . SettingInterface::class . '.')
					->end()
				->end()
			->end()
		;

		return $treeBuilder;
	}

}
