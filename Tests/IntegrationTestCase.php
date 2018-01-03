<?php

namespace Craue\ConfigBundle\Tests;

use Craue\ConfigBundle\Entity\SettingInterface;
use Craue\ConfigBundle\Repository\SettingRepository;
use Craue\ConfigBundle\Util\Config;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2018 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
abstract class IntegrationTestCase extends WebTestCase {

	const PLATFORM_MYSQL = 'mysql';
	const PLATFORM_SQLITE = 'sqlite';

	public static function getValidPlatformsWithRequiredExtensions() {
		return array(
			self::PLATFORM_MYSQL => 'pdo_mysql',
			self::PLATFORM_SQLITE => 'pdo_sqlite',
		);
	}

	/**
	 * @var bool[]
	 */
	private static $databaseInitialized = array();

	/**
	 * @param string $testName The name of the test, set by PHPUnit when called directly as a {@code dataProvider}.
	 * @param string $baseConfig The base config filename.
	 * @return string[]
	 */
	public static function getPlatformConfigs($testName, $baseConfig = 'config.yml') {
		$testData = array();

		foreach (self::getValidPlatformsWithRequiredExtensions() as $platform => $extension) {
			$testData[] = array($platform, array($baseConfig, sprintf('config_flavor_%s.yml', $platform)), $extension);
		}

		return $testData;
	}

	/**
	 * @param array $allTestData
	 * @return array
	 */
	public static function duplicateTestDataForEachPlatform(array $allTestData, $baseConfig = 'config.yml') {
		$testData = array();

		foreach ($allTestData as $oneTestData) {
			foreach (self::getPlatformConfigs('', $baseConfig) as $envConf) {
				$testData[] = array_merge($envConf, $oneTestData);
			}
		}

		return $testData;
	}

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
	 * @param string|null $requiredExtension Required PHP extension.
	 * @param array $options Options for creating the client.
	 * @return Client
	 */
	protected function initClient($requiredExtension, array $options = array()) {
		if ($requiredExtension !== null && !in_array($requiredExtension, get_loaded_extensions(), true)) {
			// !extension_loaded($requiredExtension) doesn't seem to work with HHVM as it returns false even though the extension is loaded
			$this->markTestSkipped(sprintf('Extension "%s" is not loaded.', $requiredExtension));
		}

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
	 * @param $setting SettingInterface The setting to persist.
	 * @return SettingInterface The persisted setting.
	 */
	protected function persistSetting(SettingInterface $setting) {
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
	 * @return EntityManager
	 */
	protected function getEntityManager() {
		return $this->getService('doctrine')->getManager();
	}

	/**
	 * @return SettingRepository
	 */
	protected function getSettingsRepo() {
		return $this->getEntityManager()->getRepository(static::$kernel->getContainer()->getParameter('craue_config.entity_name'));
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
