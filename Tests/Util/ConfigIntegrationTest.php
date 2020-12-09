<?php

namespace Craue\ConfigBundle\Tests\Util;

use Craue\ConfigBundle\Entity\Setting;
use Craue\ConfigBundle\Tests\IntegrationTestBundle\Entity\CustomSetting;
use Craue\ConfigBundle\Tests\IntegrationTestBundle\Util\CustomConfig;
use Craue\ConfigBundle\Tests\IntegrationTestCase;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * @group integration
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2020 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class ConfigIntegrationTest extends IntegrationTestCase {

	/**
	 * Ensure that the code works with a real (i.e. not mocked) entity manager.
	 *
	 * @dataProvider getPlatformConfigs
	 */
	public function testWithRealEntityManager($platform, $config, $requiredExtension) {
		$this->initClient($requiredExtension, ['environment' => $platform, 'config' => $config]);

		$this->persistSetting(Setting::create('name1'));
		$this->persistSetting(Setting::create('name2'));

		$c = $this->getService('craue_config');

		$c->set('name1', 'value1');
		$this->assertSame('value1', $c->get('name1'));

		$newValues = ['name1' => 'new-value1', 'name2' => 'new-value2'];
		$c->setMultiple($newValues);
		$this->assertEquals($newValues, $c->all());
	}

	/**
	 * Ensure that the configured cache is actually used.
	 *
	 * @dataProvider dataCacheUsage
	 */
	public function testCacheUsage($platform, $config, $requiredExtension, $environment) {
		$this->initClient($requiredExtension, ['environment' => $environment . '_' . $platform, 'config' => $config]);

		$this->persistSetting(Setting::create('name', 'value'));

		$this->getService('craue_config')->all();

		$this->assertTrue($this->getService('craue_config_cache_adapter')->has('name'));
	}

	public function dataCacheUsage() {
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
	 * Ensure that a custom config class can actually be used with a custom model class.
	 *
	 * @dataProvider dataCustomEntity
	 */
	public function testCustomEntity($platform, $config, $requiredExtension, $environment) {
		$this->initClient($requiredExtension, ['environment' => $environment . '_' . $platform, 'config' => $config]);
		$customSetting = $this->persistSetting(CustomSetting::create('name1', 'value1', 'section1', 'comment1'));

		$customConfig = $this->getService('craue_config');
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
	 */
	public function testDefaultEntityNameUnique($platform, $config, $requiredExtension) {
		$this->initClient($requiredExtension, ['environment' => $platform, 'config' => $config]);

		$this->assertSame(['name'], $this->getEntityManager()->getClassMetadata(Setting::class)->getIdentifier());

		$this->persistSetting(Setting::create('name1'));

		$this->expectException(UniqueConstraintViolationException::class);
		switch ($platform) {
			case self::PLATFORM_MYSQL:
				$expectedErrorMessage = "Integrity constraint violation: 1062 Duplicate entry 'name1' for key 'PRIMARY'";
				break;
			case self::PLATFORM_SQLITE:
				$expectedErrorMessage = "UNIQUE constraint failed: craue_config_setting.name";
				break;
		}
		$this->expectExceptionMessageMatches(sprintf('/^%s.+%s/s', preg_quote("An exception occurred while executing 'INSERT INTO craue_config_setting"), preg_quote($expectedErrorMessage)));
		$this->persistSetting(Setting::create('name1'));
	}

	/**
	 * Ensure that the database enforces a unique name for settings with a custom entity.
	 *
	 * @dataProvider dataCustomEntity
	 */
	public function testCustomEntityNameUnique($platform, $config, $requiredExtension, $environment) {
		$this->initClient($requiredExtension, ['environment' => $environment . '_' . $platform, 'config' => $config]);

		$this->assertSame(['name'], $this->getEntityManager()->getClassMetadata(CustomSetting::class)->getIdentifier());

		$this->persistSetting(CustomSetting::create('name1'));

		$this->expectException(UniqueConstraintViolationException::class);
		switch ($platform) {
			case self::PLATFORM_MYSQL:
				$expectedErrorMessage = "Integrity constraint violation: 1062 Duplicate entry 'name1' for key 'PRIMARY'";
				break;
			case self::PLATFORM_SQLITE:
				$expectedErrorMessage = "UNIQUE constraint failed: craue_config_setting_custom.name";
				break;
		}
		$this->expectExceptionMessageMatches(sprintf('/^%s.+%s/s', preg_quote("An exception occurred while executing 'INSERT INTO craue_config_setting_custom"), preg_quote($expectedErrorMessage)));
		$this->persistSetting(CustomSetting::create('name1'));
	}

	/**
	 * Ensure that the database table is only created for the custom entity, but not for the bundle's original one.
	 *
	 * @dataProvider dataCustomEntity
	 */
	public function testCustomEntityTableCreation($platform, $config, $requiredExtension, $environment) {
		$this->initClient($requiredExtension, array('environment' => $environment . '_' . $platform, 'config' => $config));

		$em = $this->getEntityManager();
		$schemaTool = new SchemaTool($em);
		$schema = $schemaTool->getSchemaFromMetadata($em->getMetadataFactory()->getAllMetadata());

		$this->assertTrue($schema->hasTable('craue_config_setting_custom'));
		$this->assertFalse($schema->hasTable('craue_config_setting'));
	}

}
