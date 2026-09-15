<?php declare(strict_types=1);

namespace Craue\ConfigBundle\Tests\Util;

use Craue\ConfigBundle\Entity\Setting;
use Craue\ConfigBundle\Repository\SettingRepository;
use Craue\ConfigBundle\Util\Config;
use Craue\ConfigBundle\Util\SettingsUtil;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub\ReturnValueMap;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

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

		$cache = new ArrayAdapter();
		$config->setCache($cache);

		$this->assertEquals($setting->getValue(), $config->get($setting->getName()));

		$this->assertTrue($cache->getItem($setting->getName())->isHit());
		$this->assertSame($setting->getValue(), $cache->getItem($setting->getName())->get());
	}

	public function testGet_cacheHit() : void {
		$config = new Config();
		$setting = Setting::create('name', 'value');

		$cache = new ArrayAdapter();
		$config->setCache($cache);

		$cacheItem = $cache->getItem($setting->getName());
		$cacheItem->set($setting->getValue());
		$cache->save($cacheItem);

		$this->assertEquals($setting->getValue(), $config->get($setting->getName()));
	}

	public function testSet() : void {
		$config = new Config();
		$cache = $this->createCacheMock();
		$config->setCache($cache);

		$setting = $this->createMock(Setting::class);
		$newValue = 'new-value';

		$config->setEntityManager($this->createEntityManagerMock($this->createEntityRepositoryMock(['findOneBy' => $setting])));
		$config->setEntityName(Setting::class);

		// cache is not invoked because SettingUpdateListener is not active in this unit test
		$cache->expects($this->never())
			->method($this->anything())
		;

		$setting->expects($this->once())
			->method('getName')
			->will($this->returnValue('name'))
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

		// cache is not invoked because SettingUpdateListener is not active in this unit test
		$cache->expects($this->never())
			->method($this->anything())
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
		$cache = new ArrayAdapter();
		$config->setCache($cache);

		$setting1 = Setting::create('name1', 'value1');
		$setting2 = Setting::create('name2', 'value2');

		$settingsKeyValuePairs = SettingsUtil::getAsNamesAndValues([$setting1, $setting2]);

		$config->setEntityManager($this->createEntityManagerMock($this->createEntityRepositoryMock(['findAll' => [$setting1, $setting2]])));
		$config->setEntityName(Setting::class);

		$this->assertEquals($settingsKeyValuePairs, $config->all());

		$this->assertTrue($cache->getItem('name1')->isHit());
		$this->assertSame('value1', $cache->getItem('name1')->get());
		$this->assertTrue($cache->getItem('name2')->isHit());
		$this->assertSame('value2', $cache->getItem('name2')->get());
	}

	/**
	 * @dataProvider dataGetBySection
	 */
	public function testGetBySection($section, array $foundSettings, $expectedKeyValuePairs) : void {
		$config = new Config();
		$cache = new ArrayAdapter();
		$config->setCache($cache);

		$config->setEntityManager($this->createEntityManagerMock($this->createEntityRepositoryMock(['findBy' => $this->returnValueMap([
			[['section' => $section], null, null, null, $foundSettings],
		])])));
		$config->setEntityName(Setting::class);

		$this->assertEquals($expectedKeyValuePairs, $config->getBySection($section));

		foreach ($expectedKeyValuePairs as $name => $value) {
			$this->assertSame($value, $cache->getItem($name)->get());
		}
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

	protected function createCacheMock() : MockObject&CacheItemPoolInterface {
		return $this->createMock(CacheItemPoolInterface::class);
	}

}
