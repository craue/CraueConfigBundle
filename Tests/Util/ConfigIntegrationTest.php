<?php

namespace Craue\ConfigBundle\Tests\Util;

use Craue\ConfigBundle\Entity\Setting;
use Craue\ConfigBundle\Tests\IntegrationTestBundle\Entity\CustomSetting;
use Craue\ConfigBundle\Tests\IntegrationTestBundle\Util\CustomConfig;
use Craue\ConfigBundle\Tests\IntegrationTestCase;

/**
 * @group integration
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2019 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class ConfigIntegrationTest extends IntegrationTestCase {

	/**
	 * Ensure that the configured cache is actually used.
	 *
	 * @dataProvider dataCacheUsage
	 */
	public function testCacheUsage($platform, $config, $requiredExtension, $environment) {
		$client = $this->initClient($requiredExtension, ['environment' => $environment . '_' . $platform, 'config' => $config]);
		$container = $client->getContainer();

		$this->persistSetting(Setting::create('name', 'value'));

		$container->get('craue_config')->all();

		$this->assertTrue($container->get('craue_config_cache_adapter')->has('name'));
	}

	public function dataCacheUsage() {
		return array_merge(
			self::duplicateTestDataForEachPlatform([
				['cache_DoctrineCacheBundle_file_system'],
			], 'config_cache_DoctrineCacheBundle_file_system.yml'),
			self::duplicateTestDataForEachPlatform([
				['cache_SymfonyCacheComponent_filesystem'],
			], 'config_cache_SymfonyCacheComponent_filesystem.yml')
		);
	}

	/**
	 * Ensure that a custom config class can actually be used with a custom model class.
	 *
	 * @dataProvider dataCustomEntity
	 */
	public function testCustomEntity($platform, $config, $requiredExtension, $environment) {
		$client = $this->initClient($requiredExtension, ['environment' => $environment . '_' . $platform, 'config' => $config]);
		$customSetting = $this->persistSetting(CustomSetting::create('name1', 'value1', 'section1', 'comment1'));

		$customConfig = $client->getContainer()->get('craue_config');
		$this->assertInstanceOf(CustomConfig::class, $customConfig);

		$fetchedSetting = $customConfig->getRawSetting('name1');
		$this->assertSame($customSetting, $fetchedSetting);
		$this->assertEquals('value1', $customConfig->get('name1'));
	}

	public function dataCustomEntity() {
		return self::duplicateTestDataForEachPlatform([
			['customEntity'],
		], 'config_customEntity.yml');
	}

	/**
	 * Ensure that the database enforces a unique name for settings.
	 *
	 * @dataProvider getPlatformConfigs
	 *
	 * @expectedException \Doctrine\DBAL\Exception\UniqueConstraintViolationException
	 * @expectedExceptionMessage An exception occurred while executing 'INSERT INTO craue_config_setting
	 * @expectedExceptionMessage Integrity constraint violation: 1062 Duplicate entry 'name1' for key 'PRIMARY'
	 */
	public function testDefaultEntityNameUnique($platform, $config, $requiredExtension) {
		$this->initClient($requiredExtension, ['environment' => $platform, 'config' => $config]);

		$this->assertSame(['name'], $this->getEntityManager()->getClassMetadata(Setting::class)->getIdentifier());

		$this->persistSetting(Setting::create('name1'));
		$this->persistSetting(Setting::create('name1'));
	}

	/**
	 * Ensure that the database enforces a unique name for settings with a custom entity.
	 *
	 * @dataProvider dataCustomEntity
	 *
	 * @expectedException \Doctrine\DBAL\Exception\UniqueConstraintViolationException
	 * @expectedExceptionMessage An exception occurred while executing 'INSERT INTO craue_config_setting_custom
	 * @expectedExceptionMessage Integrity constraint violation: 1062 Duplicate entry 'name1' for key 'PRIMARY'
	 */
	public function testCustomEntityNameUnique($platform, $config, $requiredExtension, $environment) {
		$this->initClient($requiredExtension, ['environment' => $environment . '_' . $platform, 'config' => $config]);

		$this->assertSame(['name'], $this->getEntityManager()->getClassMetadata(CustomSetting::class)->getIdentifier());

		$this->persistSetting(CustomSetting::create('name1'));
		$this->persistSetting(CustomSetting::create('name1'));
	}

}
