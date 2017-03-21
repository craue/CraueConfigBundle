<?php

namespace Craue\ConfigBundle\Tests;

use Craue\ConfigBundle\Entity\Setting;
use Craue\ConfigBundle\Twig\Extension\ConfigTemplateExtension;
use Craue\ConfigBundle\Util\Config;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2017 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
abstract class IntegrationTestCase extends WebTestCase {

	/**
	 * @var bool[]
	 */
	private static $databaseInitialized = array();

	/**
	 * {@inheritDoc}
	 */
	protected static function createKernel(array $options = array()) {
		$environment = isset($options['environment']) ? $options['environment'] : 'test';
		$configFile = isset($options['config']) ? $options['config'] : 'config.yml';

		return new AppKernel($environment, $configFile);
	}

	/**
	 * Initializes a client and prepares the database.
	 * @param array $options options for creating the client
	 * @return Client
	 */
	protected function initClient(array $options = array()) {
		$client = static::createClient($options);
		$environment = static::$kernel->getEnvironment();

		// Avoid completely rebuilding the database for each test. Create it only once per environment. After that, cleaning it is enough.
		if (!array_key_exists($environment, self::$databaseInitialized) || !self::$databaseInitialized[$environment]) {
			$this->rebuildDatabase();
			self::$databaseInitialized[$environment] = true;
		} else {
			$this->removeAllSettings();
		}

		return $client;
	}

	protected function rebuildDatabase() {
		$em = $this->getEntityManager();
		$metadata = $em->getMetadataFactory()->getAllMetadata();
		$schemaTool = new SchemaTool($em);

		$schemaTool->dropSchema($metadata);
		$schemaTool->createSchema($metadata);
	}

	/**
	 * Persists a {@code Setting}.
	 * @param string $name
	 * @param string|null $value
	 * @param string|null $section
	 * @return Setting
	 */
	protected function persistSetting($name, $value = null, $section = null) {
		$setting = new Setting();
		$setting->setName($name);
		$setting->setValue($value);
		$setting->setSection($section);

		$em = $this->getEntityManager();
		$em->persist($setting);
		$em->flush();

		return $setting;
	}

	/**
	 * Removes all {@code Setting}s.
	 */
	protected function removeAllSettings() {
		$em = $this->getEntityManager();

		foreach ($this->getSettingsRepo()->findAll() as $entity) {
			$em->remove($entity);
		}

		$em->flush();
	}

	/**
	 * @return Config
	 */
	protected function getConfig() {
		return $this->getService('craue_config');
	}

	/**
	 * @return ConfigTemplateExtension
	 */
	protected function getConfigTemplateExtension() {
		return $this->getService('twig.extension.craue_config_template');
	}

	/**
	 * @return EntityManager
	 */
	protected function getEntityManager() {
		return $this->getService('doctrine')->getManager();
	}

	/**
	 * @return EntityRepository
	 */
	protected function getSettingsRepo() {
		return $this->getEntityManager()->getRepository('Craue\ConfigBundle\Entity\Setting');
	}

	/**
	 * @return \Twig_Environment
	 */
	protected function getTwig() {
		return $this->getService('twig');
	}

	/**
	 * @param string $id The service identifier.
	 * @return object The associated service.
	 */
	protected function getService($id) {
		return static::$kernel->getContainer()->get($id);
	}

	/**
	 * @param Client $client
	 * @param string $route
	 * @param array $parameters
	 * @return string URL
	 */
	protected function url(Client $client, $route, array $parameters = array()) {
		return $client->getContainer()->get('router')->generate($route, $parameters);
	}

	/**
	 * @param Client $client
	 * @param string $expectedTargetUrl
	 */
	protected function assertRedirect(Client $client, $expectedTargetUrl) {
		// don't just check with $client->getResponse()->isRedirect() to know the actual URL on failure
		$this->assertEquals(302, $client->getResponse()->getStatusCode());
		$this->assertContains($expectedTargetUrl, $client->getResponse()->headers->get('Location'));
	}

}
