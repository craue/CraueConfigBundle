<?php

namespace Craue\ConfigBundle\Tests\Util;

use Craue\ConfigBundle\CacheAdapter\CacheAdapterInterface;
use Craue\ConfigBundle\Entity\Setting;
use Craue\ConfigBundle\Repository\SettingRepository;
use Craue\ConfigBundle\Util\Config;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub\ReturnValueMap;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2026 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class ConfigUnitTest extends TestCase {

	public function testGet() : void {
		$config = new Config();
		$setting = Setting::create('name', 'value');

		$config->setEntityManager($this->createEntityManagerMock($this->createEntityRepositoryMock(['findOneBy' => $this->returnValueMap([
			[['name' => $setting->getName()], null, $setting],
		])])));
		$config->setEntityName(Setting::class);

		$this->assertEquals($setting->getValue(), $config->get($setting->getName()));
	}

	public function testGet_nonexistentSetting() : void {
		$config = new Config();
		$config->setEntityManager($this->createEntityManagerMock($this->createEntityRepositoryMock()));
		$config->setEntityName(Setting::class);

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('Setting "oh-no" couldn\'t be found.');
		$config->get('oh-no');
	}

	public function testGet_cacheMiss() : void {
		$config = new Config();
		$setting = Setting::create('name', 'value');

		$config->setEntityManager($this->createEntityManagerMock($this->createEntityRepositoryMock(['findOneBy' => $this->returnValueMap([
			[['name' => $setting->getName()], null, $setting],
		])])));
		$config->setEntityName(Setting::class);

		$cache = $this->createCacheMock();
		$config->setCache($cache);

		$cache->expects($this->once())
			->method('has')
			->will($this->returnValue(false))
		;
		$cache->expects($this->never())
			->method('get')
		;
		$cache->expects($this->once())
			->method('set')
			->with('name', $setting->getValue())
		;

		$this->assertEquals($setting->getValue(), $config->get($setting->getName()));
	}

	public function testGet_cacheHit() : void {
		$config = new Config();
		$cache = $this->createCacheMock();
		$config->setCache($cache);

		$cache->expects($this->once())
			->method('has')
			->will($this->returnValueMap([
				['name', true],
			]))
		;
		$cache->expects($this->once())
			->method('get')
			->will($this->returnValueMap([
				['name', 'value'],
			]))
		;
		$cache->expects($this->never())
			->method('set')
		;

		$this->assertEquals('value', $config->get('name'));
	}

	public function testSet() : void {
		$config = new Config();
		$cache = $this->createCacheMock();
		$config->setCache($cache);

		$setting = $this->createMock(Setting::class);
		$newValue = 'new-value';

		$config->setEntityManager($this->createEntityManagerMock($this->createEntityRepositoryMock(['findOneBy' => $setting])));
		$config->setEntityName(Setting::class);

		$cache->expects($this->once())
			->method('set')
			->with($setting->getName(), $newValue)
		;

		$setting->expects($this->once())
			->method('setValue')
			->with($newValue)
		;

		$config->set($setting->getName(), $newValue);
	}

	public function testSet_nonexistentSetting() : void {
		$config = new Config();
		$config->setEntityManager($this->createEntityManagerMock($this->createEntityRepositoryMock()));
		$config->setEntityName(Setting::class);

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('Setting "oh-no" couldn\'t be found.');
		$config->set('oh-no', 'new-value');
	}

	public function testSetMultiple() : void {
		$config = new Config();
		$cache = $this->createCacheMock();
		$config->setCache($cache);

		$setting = $this->getMockBuilder(Setting::class)->setMethods(['setValue'])->getMock();
		$setting->setName('name');
		$newValue = 'new-value';

		$config->setEntityManager($this->createEntityManagerMock($this->createEntityRepositoryMock(['findByNames' => [$setting->getName() => $setting]])));
		$config->setEntityName(Setting::class);

		$settingsKeyValuePairs = [
			$setting->getName() => $newValue,
		];

		$cache->expects($this->once())
			->method('setMultiple')
			->with($settingsKeyValuePairs)
		;

		$setting->expects($this->once())
			->method('setValue')
			->with($newValue)
		;

		$config->setMultiple($settingsKeyValuePairs);
	}

	public function testSetMultiple_noChanges() : void {
		$config = new Config();
		$setting = $this->createMock(Setting::class);

		$setting->expects($this->never())
			->method('setValue')
		;

		$config->setMultiple([]);
	}

	public function testSetMultiple_nonexistentSetting() : void {
		$config = new Config();
		$setting = Setting::create('name1');

		$config->setEntityManager($this->createEntityManagerMock($this->createEntityRepositoryMock(['findByNames' => [$setting->getName() => $setting]])));
		$config->setEntityName(Setting::class);

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('Setting "oh-no" couldn\'t be found.');
		$config->setMultiple([
			$setting->getName() => 'new-value1',
			'oh-no' => 'new-value2',
		]);
	}

	public function testAll_noSettings() : void {
		$config = new Config();
		$config->setEntityManager($this->createEntityManagerMock($this->createEntityRepositoryMock(['findAll' => []])));
		$config->setEntityName(Setting::class);

		$this->assertEquals([], $config->all());
	}

	/**
	 * Ensure that the cache gets filled while fetching all settings from the DB.
	 */
	public function testAll_cacheUpdate() : void {
		$config = new Config();
		$cache = $this->createCacheMock();
		$config->setCache($cache);

		$setting1 = Setting::create('name1', 'value1');
		$setting2 = Setting::create('name2', 'value2');

		$settingsKeyValuePairs = [
			$setting1->getName() => $setting1->getValue(),
			$setting2->getName() => $setting2->getValue(),
		];

		$config->setEntityManager($this->createEntityManagerMock($this->createEntityRepositoryMock(['findAll' => [$setting1, $setting2]])));
		$config->setEntityName(Setting::class);

		$cache->expects($this->once())
			->method('setMultiple')
			->with($settingsKeyValuePairs)
		;

		$this->assertEquals($settingsKeyValuePairs, $config->all());
	}

	/**
	 * @dataProvider dataGetBySection
	 */
	public function testGetBySection($section, array $foundSettings, $expectedKeyValuePairs) : void {
		$config = new Config();
		$cache = $this->createCacheMock();
		$config->setCache($cache);

		$config->setEntityManager($this->createEntityManagerMock($this->createEntityRepositoryMock(['findBy' => $this->returnValueMap([
			[['section' => $section], null, null, null, $foundSettings],
		])])));
		$config->setEntityName(Setting::class);

		$cache->expects($this->once())
			->method('setMultiple')
			->with($expectedKeyValuePairs)
		;

		$this->assertEquals($expectedKeyValuePairs, $config->getBySection($section));
	}

	public static function dataGetBySection() : iterable {
		return [
			['section',			[Setting::create('name', 'value', 'section')],	['name' => 'value']],
			[null,				[Setting::create('name', 'value')],				['name' => 'value']],
			['other-section',	[],												[]],
		];
	}

	/**
	 * Ensure that the configured repository is returned.
	 */
	public function testGetRepo_configuredRepository() : void {
		$config = new Config();
		$method = new \ReflectionMethod($config, 'getRepo');

		$repo = $this->getMockBuilder(EntityRepository::class)
			->disableOriginalConstructor()
			->getMock()
		;

		$config->setEntityManager($this->createEntityManagerMock($repo));
		$config->setEntityName(Setting::class);

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessageMatches(sprintf('/^Entity repository of type "%s" expected, but got ".+"\.$/', preg_quote(SettingRepository::class)));
		$method->invoke($config);
	}

	/**
	 * Ensure that the repository is fetched only once from the EntityManager, but again if it's changed at runtime.
	 */
	public function testGetRepo_changedEntityManager() : void {
		$config = new Config();
		$method = new \ReflectionMethod($config, 'getRepo');

		// 1st call to `getRepo` using a mocked EntityManager
		$config->setEntityManager($this->createEntityManagerMock($this->createEntityRepositoryMock()));
		$config->setEntityName(Setting::class);

		// invoke twice to ensure the cached instance is used
		$method->invoke($config);
		$method->invoke($config);

		// 2nd call to `getRepo` using a different mocked EntityManager
		$config->setEntityManager($this->createEntityManagerMock($this->createEntityRepositoryMock()));

		// invoke twice to ensure the cached instance is used
		$method->invoke($config);
		$method->invoke($config);
	}

	/**
	 * Ensure that the repository is fetched only once with a given entity name, but again if it's changed at runtime.
	 */
	public function testGetRepo_changedEntityName() : void {
		$config = new Config();
		$method = new \ReflectionMethod($config, 'getRepo');

		$em = $this->createEntityManagerMock();

		$em->expects($this->exactly(2))
			->method('getRepository')
			->will($this->returnValue($this->createEntityRepositoryMock()))
		;

		$config->setEntityManager($em);

		// 1st call to `getRepo` using the default entity name
		$config->setEntityName(Setting::class);

		// invoke twice to ensure the cached instance is used
		$method->invoke($config);
		$method->invoke($config);

		// 2nd call to `getRepo` using a different entity name
		$config->setEntityName(Setting::class . 'DoesNotExist');

		// invoke twice to ensure the cached instance is used
		$method->invoke($config);
		$method->invoke($config);
	}

	/**
	 * Ensure that the cache is not cleared when setting a new EntityManager or when setting the same EntityManager again.
	 */
	public function testSetEntityManager_newOrSame() : void {
		$config = new Config();
		$cache = $this->createCacheMock();
		$config->setCache($cache);

		$cache->expects($this->never())
			->method('clear')
		;

		$em = $this->createEntityManagerMock();

		// 1st call to `setEntityManager` using a new EntityManager
		$config->setEntityManager($em);

		// 2nd call to `setEntityManager` using the same EntityManager
		$config->setEntityManager($em);
	}

	/**
	 * Ensure that the cache is cleared when setting a different EntityManager.
	 */
	public function testSetEntityManager_different() : void {
		$config = new Config();
		$cache = $this->createCacheMock();
		$config->setCache($cache);

		$cache->expects($this->once())
			->method('clear')
		;

		$em1 = $this->createEntityManagerMock();
		$em2 = $this->createEntityManagerMock();

		// 1st call to `setEntityManager` using a new EntityManager
		$config->setEntityManager($em1);

		// 2nd call to `setEntityManager` using a different EntityManager
		$config->setEntityManager($em2);
	}

	/**
	 * @param array $methodsWithReturnValues Method call expectations (method name => return value).
	 */
	protected function createEntityRepositoryMock(array $methodsWithReturnValues = []) : MockObject&SettingRepository {
		$repo = $this->getMockBuilder(SettingRepository::class)
			->disableOriginalConstructor()
			->getMock()
		;

		foreach ($methodsWithReturnValues as $method => $returnValue) {
			if (!$returnValue instanceof ReturnValueMap) {
				$returnValue = $this->returnValue($returnValue);
			}

			$repo->expects($this->once())
				->method($method)
				->will($returnValue)
			;
		}

		return $repo;
	}

	protected function createEntityManagerMock(?EntityRepository $repo = null) : MockObject&EntityManager {
		$em = $this->getMockBuilder(EntityManager::class)
			->disableOriginalConstructor()
			->getMock()
		;

		if ($repo !== null) {
			$em->expects($this->once())
				->method('getRepository')
				->will($this->returnValue($repo))
			;
		}

		return $em;
	}

	protected function createCacheMock() : MockObject&CacheAdapterInterface {
		return $this->createMock(CacheAdapterInterface::class);
	}

}
