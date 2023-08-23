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
 * @copyright 2011-2023 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class ConfigUnitTest extends TestCase {

	public function testGet() {
		$config = new Config();
		$setting = Setting::create('name', 'value');

		$config->setEntityManager($this->createEntityManagerMock($this->createEntityRepositoryMock(['findOneBy' => $this->returnValueMap([
			[['name' => $setting->getName()], null, $setting],
		])])));

		$this->assertEquals($setting->getValue(), $config->get($setting->getName()));
	}

	public function testGet_nonexistentSetting() {
		$config = new Config();
		$config->setEntityManager($this->createEntityManagerMock($this->createEntityRepositoryMock()));

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('Setting "oh-no" couldn\'t be found.');
		$config->get('oh-no');
	}

	public function testGet_cacheMiss() {
		$config = new Config();
		$setting = Setting::create('name', 'value');

		$config->setEntityManager($this->createEntityManagerMock($this->createEntityRepositoryMock(['findOneBy' => $this->returnValueMap([
			[['name' => $setting->getName()], null, $setting],
		])])));

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

	public function testGet_cacheHit() {
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

	public function testSet() {
		$config = new Config();
		$cache = $this->createCacheMock();
		$config->setCache($cache);

		$setting = $this->createMock(Setting::class);
		$newValue = 'new-value';

		$config->setEntityManager($this->createEntityManagerMock($this->createEntityRepositoryMock(['findOneBy' => $setting])));

		$cache->expects($this->never())
			->method($this->anything())
		;

		$setting->expects($this->once())
			->method('setValue')
			->with($newValue)
		;

		$config->set($setting->getName(), $newValue);
	}

	public function testSet_nonexistentSetting() {
		$config = new Config();
		$config->setEntityManager($this->createEntityManagerMock($this->createEntityRepositoryMock()));

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('Setting "oh-no" couldn\'t be found.');
		$config->set('oh-no', 'new-value');
	}

	public function testSetMultiple() {
		$config = new Config();
		$cache = $this->createCacheMock();
		$config->setCache($cache);

		$setting = $this->getMockBuilder(Setting::class)->setMethods(['setValue'])->getMock();
		$setting->setName('name');
		$newValue = 'new-value';

		$config->setEntityManager($this->createEntityManagerMock($this->createEntityRepositoryMock(['findByNames' => [$setting->getName() => $setting]])));

		$settingsKeyValuePairs = [
			$setting->getName() => $newValue,
		];

		$cache->expects($this->never())
			->method($this->anything())
		;

		$setting->expects($this->once())
			->method('setValue')
			->with($newValue)
		;

		$config->setMultiple($settingsKeyValuePairs);
	}

	public function testSetMultiple_noChanges() {
		$config = new Config();
		$setting = $this->createMock(Setting::class);

		$setting->expects($this->never())
			->method('setValue')
		;

		$config->setMultiple([]);
	}

	public function testSetMultiple_nonexistentSetting() {
		$config = new Config();
		$setting = Setting::create('name1');

		$config->setEntityManager($this->createEntityManagerMock($this->createEntityRepositoryMock(['findByNames' => [$setting->getName() => $setting]])));

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('Setting "oh-no" couldn\'t be found.');
		$config->setMultiple([
			$setting->getName() => 'new-value1',
			'oh-no' => 'new-value2',
		]);
	}

	public function testAll_noSettings() {
		$config = new Config();
		$config->setEntityManager($this->createEntityManagerMock($this->createEntityRepositoryMock(['findAll' => []])));

		$this->assertEquals([], $config->all());
	}

	/**
	 * Ensure that the cache gets filled while fetching all settings from the DB.
	 */
	public function testAll_cacheUpdate() {
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

		$cache->expects($this->once())
			->method('setMultiple')
			->with($settingsKeyValuePairs)
		;

		$this->assertEquals($settingsKeyValuePairs, $config->all());
	}

	/**
	 * @dataProvider dataGetBySection
	 */
	public function testGetBySection($section, array $foundSettings, $expectedKeyValuePairs) {
		$config = new Config();
		$cache = $this->createCacheMock();
		$config->setCache($cache);

		$config->setEntityManager($this->createEntityManagerMock($this->createEntityRepositoryMock(['findBy' => $this->returnValueMap([
			[['section' => $section], null, null, null, $foundSettings],
		])])));

		$cache->expects($this->once())
			->method('setMultiple')
			->with($expectedKeyValuePairs)
		;

		$this->assertEquals($expectedKeyValuePairs, $config->getBySection($section));
	}

	public function dataGetBySection() {
		return [
			['section',			[Setting::create('name', 'value', 'section')],	['name' => 'value']],
			[null,				[Setting::create('name', 'value')],				['name' => 'value']],
			['other-section',	[],												[]],
		];
	}

	/**
	 * Ensure that the configured repository is returned.
	 */
	public function testGetRepo_configuredRepository() {
		$config = new Config();
		$method = new \ReflectionMethod($config, 'getRepo');
		$method->setAccessible(true);

		$repo = $this->getMockBuilder(EntityRepository::class)
			->disableOriginalConstructor()
			->getMock()
		;

		$config->setEntityManager($this->createEntityManagerMock($repo));

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessageMatches(sprintf('/^Entity repository of type "%s" expected, but got ".+"\.$/', preg_quote(SettingRepository::class)));
		$method->invoke($config);
	}

	/**
	 * Ensure that the repository is fetched only once from the EntityManager, but again if it's changed at runtime.
	 */
	public function testGetRepo_changedEntityManager() {
		$config = new Config();
		$method = new \ReflectionMethod($config, 'getRepo');
		$method->setAccessible(true);

		// 1st call to `getRepo` using a mocked EntityManager
		$config->setEntityManager($this->createEntityManagerMock($this->createEntityRepositoryMock()));

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
	public function testGetRepo_changedEntityName() {
		$config = new Config();
		$method = new \ReflectionMethod($config, 'getRepo');
		$method->setAccessible(true);

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
	public function testSetEntityManager_newOrSame() {
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
	public function testSetEntityManager_different() {
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
	 * @return MockObject|SettingRepository
	 */
	protected function createEntityRepositoryMock(array $methodsWithReturnValues = []) {
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

	/**
	 * @param EntityRepository|null $repo
	 * @return MockObject|EntityManager
	 */
	protected function createEntityManagerMock(EntityRepository $repo = null) {
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

	/**
	 * @return MockObject|CacheAdapterInterface
	 */
	protected function createCacheMock() {
		return $this->createMock(CacheAdapterInterface::class);
	}

}
