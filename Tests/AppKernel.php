<?php

namespace Craue\ConfigBundle\Tests;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel {

	private $configFiles;

	public function __construct($environment, $configFiles) {
		parent::__construct($environment, true);

		if (!is_array($configFiles)) {
			$configFiles = (array) $configFiles;
		}

		$this->configFiles = array();

		foreach ($configFiles as $configFile) {
			$fs = new Filesystem();
			if (!$fs->isAbsolutePath($configFile)) {
				$configFile = __DIR__ . '/config/' . $configFile;
			}

			if (!file_exists($configFile)) {
				throw new \RuntimeException(sprintf('The config file "%s" does not exist.', $configFile));
			}

			$this->configFiles[] = $configFile;
		}
	}

	public function registerBundles() {
		return array(
			new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
			new \Doctrine\Bundle\DoctrineCacheBundle\DoctrineCacheBundle(),
			new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
			new \Symfony\Bundle\TwigBundle\TwigBundle(),
			new \Craue\ConfigBundle\CraueConfigBundle(),
			new \Craue\ConfigBundle\Tests\IntegrationTestBundle\IntegrationTestBundle(),
		);
	}

	public function registerContainerConfiguration(LoaderInterface $loader) {
		if (!is_array($this->configFiles)) {
			$this->configFiles = (array) $this->configFiles;
		}

		foreach ($this->configFiles as $configFile) {
			$loader->load($configFile);
		}

		if (class_exists('Symfony\Component\Asset\Package')) {
			// enable assets to avoid fatal error "Call to a member function needsEnvironment() on a non-object in vendor/twig/twig/lib/Twig/Node/Expression/Function.php on line 25" with Symfony 3.0
			$loader->load(function(ContainerBuilder $container) {
				$container->loadFromExtension('framework', array(
					'assets' => null,
				));
			});
		}
	}

	public function getCacheDir() {
		if (array_key_exists('CACHE_DIR', $_ENV)) {
			return $_ENV['CACHE_DIR'] . DIRECTORY_SEPARATOR . $this->environment;
		}

		return parent::getCacheDir();
	}

	public function getLogDir() {
		if (array_key_exists('LOG_DIR', $_ENV)) {
			return $_ENV['LOG_DIR'] . DIRECTORY_SEPARATOR . $this->environment;
		}

		return parent::getLogDir();
	}

	public function serialize() {
		return serialize(array($this->environment, $this->configFiles));
	}

	public function unserialize($data) {
		list($environment, $configFiles) = unserialize($data);
		$this->__construct($environment, $configFiles);
	}

}
