<?php

namespace Craue\ConfigBundle\DependencyInjection;

use Craue\ConfigBundle\Entity\Setting;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * Registration of the extension via DI.
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2022 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class CraueConfigExtension extends Extension implements PrependExtensionInterface {

	/**
	 * {@inheritDoc}
	 */
	public function load(array $configs, ContainerBuilder $container) {
		$loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
		$loader->load('controller.xml');
		$loader->load('form.xml');
		$loader->load('twig.xml');
		$loader->load('util.xml');
	}

	/**
	 * {@inheritDoc}
	 */
	public function prepend(ContainerBuilder $container) {
		$config = $this->processConfiguration(new Configuration(), $container->getExtensionConfig($this->getAlias()));

		$container->setParameter('craue_config.db_driver.' . $config['db_driver'], true);
		$container->setParameter('craue_config.entity_name', $config['entity_name']);

		$container->prependExtensionConfig('doctrine', [
			'orm' => [
				'mappings' => [
					'CraueConfigBundle' => [
						'type' => 'xml',
						'dir' => 'Resources/config/' . ($config['entity_name'] === Setting::class ? 'doctrine-mapping-with-default-setting' : 'doctrine-mapping'),
						'prefix' => 'Craue\ConfigBundle\Entity',
					],
				],
			],
		]);
	}

}
