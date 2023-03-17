<?php

namespace Craue\ConfigBundle\Tests\Controller;

use Craue\ConfigBundle\Entity\Setting;
use Craue\ConfigBundle\Tests\IntegrationTestCase;
use Doctrine\Bundle\DoctrineBundle\DataCollector\DoctrineDataCollector;

/**
 * @group integration
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2023 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class DebugControllerTest extends IntegrationTestCase {

	/**
	 * Ensure that cache values are persisted between requests.
	 *
	 * @dataProvider dataGetAction_severalRequests
	 */
	public function testGetAction_severalRequests($platform, $config, $requiredExtension, $environment) {
		$this->initClient($requiredExtension, ['environment' => $environment . '_' . $platform, 'config' => $config]);
		$this->persistSetting(Setting::create('name1', 'value1'));

		$cache = static::$client->getContainer()->get('craue_config_cache_adapter');
		$cache->clear();

		// 1st request
		$dbCollector = $this->doRequest();
		$this->assertGreaterThan(0, $dbCollector->getQueryCount());

		// 2nd request
		$dbCollector = $this->doRequest();
		$this->assertSame(0, $dbCollector->getQueryCount(), "No database queries were expected on the 2nd request, but got:\n" . var_export($dbCollector->getQueries(), true));
	}

	public function dataGetAction_severalRequests() {
		$testData = self::duplicateTestDataForEachPlatform([
			['cache_SymfonyCacheComponent_filesystem'],
		], 'config_cache_SymfonyCacheComponent_filesystem.yml');

		if (!empty($_ENV['REDIS_DSN'])) {
			$testData = array_merge($testData, self::duplicateTestDataForEachPlatform([
				['cache_SymfonyCacheComponent_redis'],
			], 'config_cache_SymfonyCacheComponent_redis.yml'));
		}

		// TODO remove as soon as Symfony >= 5.0 is required
		if (class_exists(\Doctrine\Bundle\DoctrineCacheBundle\DoctrineCacheBundle::class)) {
			$testData = array_merge($testData, self::duplicateTestDataForEachPlatform([
				['cache_DoctrineCacheBundle_file_system'],
			], 'config_cache_DoctrineCacheBundle_file_system.yml'));
		}

		return $testData;
	}

	/**
	 * @return DoctrineDataCollector
	 */
	private function doRequest() {
		static::$client->enableProfiler();
		static::$client->request('GET', $this->url('debug_get', ['name' => 'name1']));
		$this->assertSame('{"name1":"value1"}', static::$client->getResponse()->getContent());

		return static::$client->getProfile()->getCollector('db');
	}

}
