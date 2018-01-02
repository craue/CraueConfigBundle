<?php

namespace Craue\ConfigBundle\Tests\Controller;

use Craue\ConfigBundle\Entity\Setting;
use Craue\ConfigBundle\Tests\IntegrationTestCase;
use Doctrine\Bundle\DoctrineBundle\DataCollector\DoctrineDataCollector;
use Symfony\Bundle\FrameworkBundle\Client;

/**
 * @group integration
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-present Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class DebugControllerTest extends IntegrationTestCase {

	/**
	 * Ensure that cache values are persisted between requests.
	 *
	 * @dataProvider dataGetAction_severalRequests
	 */
	public function testGetAction_severalRequests($platform, $config, $requiredExtension, $environment) {
		$client = $this->initClient($requiredExtension, array('environment' => $environment . '_' . $platform, 'config' => $config));
		$this->persistSetting(Setting::create('name1', 'value1'));

		$cache = $client->getContainer()->get('craue_config_cache_adapter');
		$cache->clear();

		// 1st request
		$dbCollector = $this->doRequest($client);
		$this->assertGreaterThan(0, $dbCollector->getQueryCount());

		// 2nd request
		$dbCollector = $this->doRequest($client);
		$this->assertSame(0, $dbCollector->getQueryCount(), "No database queries were expected on the 2nd request, but got:\n" . var_export($dbCollector->getQueries(), true));
	}

	public function dataGetAction_severalRequests() {
		$testData = self::duplicateTestDataForEachPlatform(array(
			array('cache_DoctrineCacheBundle_file_system'),
		), 'config_cache_DoctrineCacheBundle_file_system.yml');

		// TODO remove check as soon as Symfony >= 3.1 is required
		if (class_exists('\Symfony\Component\Cache\Adapter\ArrayAdapter')) {
			$testData = array_merge($testData,
				self::duplicateTestDataForEachPlatform(array(
					array('cache_SymfonyCacheComponent_filesystem'),
				), 'config_cache_SymfonyCacheComponent_filesystem.yml'));
		}

		return $testData;
	}

	/**
	 * @param Client $client
	 * @return DoctrineDataCollector
	 */
	private function doRequest(Client $client) {
		$client->enableProfiler();
		$client->request('GET', $this->url($client, 'debug_get', array('name' => 'name1')));
		$this->assertSame('{"name1":"value1"}', $client->getResponse()->getContent());

		return $client->getProfile()->getCollector('db');
	}

}
