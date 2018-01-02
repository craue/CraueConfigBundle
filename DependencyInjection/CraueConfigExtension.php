<?php

namespace Craue\ConfigBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * Registration of the extension via DI.
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-present Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class CraueConfigExtension extends Extension {

	/**
	 * {@inheritDoc}
	 */
	public function load(array $configs, ContainerBuilder $container) {
		$processor = new Processor();
		$config = $processor->processConfiguration(new Configuration(), $configs);

		$container->setParameter('craue_config.db_driver.' . $config['db_driver'], true);
		$container->setParameter('craue_config.entity_name', $config['entity_name']);

		$loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

		if (!method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')) {
			$loader->load('form_legacy.xml'); // for symfony/form < 2.8
		} else {
			$loader->load('form.xml');
		}

		$loader->load('twig.xml');
		$loader->load('util.xml');
	}

}
