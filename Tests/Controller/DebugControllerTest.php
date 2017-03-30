<?php

namespace Craue\ConfigBundle\Tests\Controller;

use Craue\ConfigBundle\Tests\IntegrationTestCase;
use Doctrine\Bundle\DoctrineBundle\DataCollector\DoctrineDataCollector;
use Symfony\Bundle\FrameworkBundle\Client;

/**
 * @group integration
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2017 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class DebugControllerTest extends IntegrationTestCase {

	/**
	 * Ensure that cache values are persisted between requests.
	 */
	public function testGetAction_severalRequests() {
		$client = $this->initClient(array('environment' => 'cache_DoctrineCacheBundle_file_system', 'config' => 'config_cache_DoctrineCacheBundle_file_system.yml'));
		$this->persistSetting('name1', 'value1');

		$cache = $client->getContainer()->get('craue_config_cache_adapter');
		$cache->clear();

		// 1st request
		$dbCollector = $this->doRequest($client);
		$this->assertGreaterThan(0, $dbCollector->getQueryCount());

		// 2nd request
		$dbCollector = $this->doRequest($client);
		$this->assertSame(0, $dbCollector->getQueryCount(), "No database queries were expected on the 2nd request, but got:\n" . var_export($dbCollector->getQueries(), true));
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
