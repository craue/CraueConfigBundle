<?php

namespace Craue\ConfigBundle\Tests\Util;

use Craue\ConfigBundle\Entity\Setting;
use Craue\ConfigBundle\Tests\IntegrationTestBundle\Entity\CustomSetting;
use Craue\ConfigBundle\Tests\IntegrationTestCase;

/**
 * @group integration
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-present Christian Raue
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

		$this->persistSetting(Setting::create('name', 'value'));

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

	/**
	 * Ensure that a custom config class can actually be used with a custom model class.
	 *
	 * @dataProvider dataCustomEntity
	 */
	public function testCustomEntity($platform, $config, $requiredExtension, $environment) {
		$client = $this->initClient($requiredExtension, array('environment' => $environment . '_' . $platform, 'config' => $config));
		$customSetting = $this->persistSetting(CustomSetting::create('name1', 'value1', 'section1', 'comment1'));

		$customConfig = $client->getContainer()->get('craue_config');
		$this->assertInstanceOf('Craue\ConfigBundle\Tests\IntegrationTestBundle\Util\CustomConfig', $customConfig);

		$fetchedSetting = $customConfig->getRawSetting('name1');
		$this->assertSame($customSetting, $fetchedSetting);
		$this->assertEquals('value1', $customConfig->get('name1'));
	}

	public function dataCustomEntity() {
		return self::duplicateTestDataForEachPlatform(array(
			array('customEntity'),
		), 'config_customEntity.yml');
	}

	/**
	 * Ensure that the database enforces a unique name for settings.
	 *
	 * @dataProvider getPlatformConfigs
	 *
	 * @expectedException \Doctrine\DBAL\DBALException
	 * @expectedExceptionMessage An exception occurred while executing 'INSERT INTO craue_config_setting
	 * @expectedExceptionMessage Integrity constraint violation: 1062 Duplicate entry 'name1' for key 'PRIMARY'
	 *
	 * TODO expect \Doctrine\DBAL\Exception\UniqueConstraintViolationException as soon as Doctrine/DBAL >= 2.5 is required
	 */
	public function testDefaultEntityNameUnique($platform, $config, $requiredExtension) {
		$this->initClient($requiredExtension, array('environment' => $platform, 'config' => $config));

		$this->assertSame(array('name'), $this->getEntityManager()->getClassMetadata('Craue\ConfigBundle\Entity\Setting')->getIdentifier());

		$this->persistSetting(Setting::create('name1'));
		$this->persistSetting(Setting::create('name1'));
	}

	/**
	 * Ensure that the database enforces a unique name for settings with a custom entity.
	 *
	 * @dataProvider dataCustomEntity
	 *
	 * @expectedException \Doctrine\DBAL\DBALException
	 * @expectedExceptionMessage An exception occurred while executing 'INSERT INTO craue_config_setting_custom
	 * @expectedExceptionMessage Integrity constraint violation: 1062 Duplicate entry 'name1' for key 'PRIMARY'
	 *
	 * TODO expect \Doctrine\DBAL\Exception\UniqueConstraintViolationException as soon as Doctrine/DBAL >= 2.5 is required
	 */
	public function testCustomEntityNameUnique($platform, $config, $requiredExtension, $environment) {
		$this->initClient($requiredExtension, array('environment' => $environment . '_' . $platform, 'config' => $config));

		$this->assertSame(array('name'), $this->getEntityManager()->getClassMetadata('Craue\ConfigBundle\Tests\IntegrationTestBundle\Entity\CustomSetting')->getIdentifier());

		$this->persistSetting(CustomSetting::create('name1'));
		$this->persistSetting(CustomSetting::create('name1'));
	}

}
