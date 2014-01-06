<?php

namespace Craue\ConfigBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Registration of the extension via DI.
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2014 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class CraueConfigExtension extends Extension {

	/**
	 * {@inheritDoc}
	 */
	public function load(array $config, ContainerBuilder $container) {
		$loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
		$loader->load('twig.xml');
		$loader->load('util.xml');
	}

}
