<?php

namespace Craue\ConfigBundle\Tests;

use Craue\ConfigBundle\Entity\SettingInterface;
use Craue\ConfigBundle\Repository\SettingRepository;
use Craue\ConfigBundle\Util\Config;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Twig\Environment;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2020 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
abstract class IntegrationTestCase extends WebTestCase {

	/**
	 * @var AbstractBrowser|Client|null
	 * TODO remove Client type as soon as Symfony >= 4.3 is required
	 */
	protected static $client;

	const PLATFORM_MYSQL = 'mysql';
	const PLATFORM_SQLITE = 'sqlite';

	public static function getValidPlatformsWithRequiredExtensions() {
		return [
			self::PLATFORM_MYSQL => 'pdo_mysql',
			self::PLATFORM_SQLITE => 'pdo_sqlite',
		];
	}

	/**
	 * @var bool[]
	 */
	private static $databaseInitialized = [];

	/**
	 * @param string $testName The name of the test, set by PHPUnit when called directly as a {@code dataProvider}.
	 * @param string $baseConfig The base config filename.
	 * @return string[]
	 */
	public static function getPlatformConfigs($testName, $baseConfig = 'config.yml') {
		$testData = [];

		foreach (self::getValidPlatformsWithRequiredExtensions() as $platform => $extension) {
			$testData[] = [$platform, [$baseConfig, sprintf('config_flavor_%s.yml', $platform)], $extension];
		}

		return $testData;
	}

	/**
	 * @param array $allTestData
	 * @return array
	 */
	public static function duplicateTestDataForEachPlatform(array $allTestData, $baseConfig = 'config.yml') {
		$testData = [];

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
	protected static function createKernel(array $options = []) {
		$environment = $options['environment'] ?? 'test';
		$configFile = $options['config'] ?? 'config.yml';

		return new AppKernel($environment, $configFile);
	}

	/**
	 * Initializes a client and prepares the database.
	 * @param string|null $requiredExtension Required PHP extension.
	 * @param array $options Options for creating the client.
	 */
	protected function initClient($requiredExtension, array $options = []) {
		if ($requiredExtension !== null && !extension_loaded($requiredExtension)) {
			$this->markTestSkipped(sprintf('Extension "%s" is not loaded.', $requiredExtension));
		}

		static::$client = static::createClient($options);
		$environment = static::$kernel->getEnvironment();

		// Avoid completely rebuilding the database for each test. Create it only once per environment. After that, cleaning it is enough.
		if (!array_key_exists($environment, self::$databaseInitialized) || !self::$databaseInitialized[$environment]) {
			$this->rebuildDatabase();
			self::$databaseInitialized[$environment] = true;
		} else {
			$this->removeAllSettings();
		}
	}

	protected function rebuildDatabase() {
		$em = $this->getEntityManager();
		$metadata = $em->getMetadataFactory()->getAllMetadata();
		$schemaTool = new SchemaTool($em);

		$schemaTool->dropSchema($metadata);
		$schemaTool->createSchema($metadata);
	}

	/**
	 * @param SettingInterface $setting The setting to persist.
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
	 * @return Environment
	 */
	protected function getTwig() {
		return $this->getService('twig.test');
	}

	/**
	 * @param string $id The service identifier.
	 * @return object The associated service.
	 */
	protected function getService($id) {
		// TODO remove as soon as Symfony >= 4.3 is required
		if (!property_exists($this, 'container')) {
			return static::$kernel->getContainer()->get($id);
		}

		return self::$container->get($id);
	}

	/**
	 * @param string $route
	 * @param array $parameters
	 * @return string URL
	 */
	protected function url($route, array $parameters = []) {
		return $this->getService('router')->generate($route, $parameters);
	}

	/**
	 * @param string $expectedTargetUrl
	 */
	protected function assertRedirect($expectedTargetUrl) {
		// don't just check with static::$client->getResponse()->isRedirect() to know the actual URL on failure
		$this->assertEquals(302, static::$client->getResponse()->getStatusCode());
		$this->assertStringContainsString($expectedTargetUrl, static::$client->getResponse()->headers->get('Location'));
	}

}
