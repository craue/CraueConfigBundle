<?php

namespace Craue\ConfigBundle\Tests\Util;

use Craue\ConfigBundle\Tests\IntegrationTestCase;

/**
 * @group integration
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2017 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class ConfigIntegrationTest extends IntegrationTestCase {

	/**
	 * Ensure that the configured cache is actually used.
	 *
	 * @dataProvider dataCacheUsage
	 */
	public function testCacheUsage($platform, $config, $requiredExtension, $environment) {
		$client = $this->initClient($requiredExtension, array('environment' => $environment . '_' . $platform, 'config' => $config));
		$container = $client->getContainer();

		$this->persistSetting('name', 'value');

		$container->get('craue_config')->all();

		$this->assertTrue($container->get('craue_config_cache_adapter')->has('name'));
	}

	public function dataCacheUsage() {
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

}
