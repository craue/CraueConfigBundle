<?php declare(strict_types=1);

namespace Craue\ConfigBundle\DependencyInjection;

use Craue\ConfigBundle\Entity\Setting;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Registration of the extension via DI.
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2026 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class CraueConfigExtension extends Extension implements PrependExtensionInterface {

	/**
	 * {@inheritDoc}
	 */
	public function load(array $configs, ContainerBuilder $container) : void {
		$config = $this->processConfiguration(new Configuration(), $configs);

		$loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
		$loader->load('controller.yml');
		$loader->load('event_listener.yml');
		$loader->load('form.yml');
		$loader->load('twig.yml');
		$loader->load('util.yml');

		$definition = $container->getDefinition('craue_config_default');
		$definition->addMethodCall('setEntityManager', [
			new Reference(sprintf('doctrine.orm.%s_entity_manager', $config['entity_manager'])),
		]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function prepend(ContainerBuilder $container) : void {
		$config = $this->processConfiguration(new Configuration(), $container->getExtensionConfig($this->getAlias()));

		$container->setParameter('craue_config.db_driver.' . $config['db_driver'], true);
		$container->setParameter('craue_config.entity_manager', $config['entity_manager']);
		$container->setParameter('craue_config.entity_name', $config['entity_name']);

		$container->prependExtensionConfig('doctrine', [
			'orm' => [
				'entity_managers' => [
					$config['entity_manager'] => [
						'mappings' => [
							'CraueConfigBundle' => [
								'type' => 'xml',
								'dir' => 'Resources/config/' . ($config['entity_name'] === Setting::class ? 'doctrine-mapping-with-default-setting' : 'doctrine-mapping'),
								'prefix' => 'Craue\ConfigBundle\Entity',
							],
						],
					],
				],
			],
		]);
	}

}
