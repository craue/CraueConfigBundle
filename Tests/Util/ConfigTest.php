<?php

namespace Craue\ConfigBundle\Tests\Util;

use Craue\ConfigBundle\Entity\Setting;
use Craue\ConfigBundle\Repository\SettingRepository;
use Craue\ConfigBundle\Util\Config;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

/**
 * @group unit
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2017 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class ConfigTest extends \PHPUnit_Framework_TestCase {

	public function testGet() {
		$config = new Config();
		$setting = Setting::create('name', 'value');

		$config->setEntityManager($this->createEntityManagerMock($this->createEntityRepositoryMock(array('findOneBy' => $this->returnValueMap(array(
			array(array('name' => $setting->getName()), null, $setting),
		))))));

		$this->assertEquals($setting->getValue(), $config->get($setting->getName()));
	}

	/**
	 * @expectedException \RuntimeException
	 * @expectedExceptionMessage Setting "oh-no" couldn't be found.
	 */
	public function testGet_nonexistentSetting() {
		$config = new Config();
		$config->setEntityManager($this->createEntityManagerMock($this->createEntityRepositoryMock()));

		$config->get('oh-no');
	}

	public function testSet() {
		$config = new Config();

		$setting = $this->getMockBuilder('Craue\ConfigBundle\Entity\Setting')->getMock();
		$newValue = 'new-value';

		$config->setEntityManager($this->createEntityManagerMock($this->createEntityRepositoryMock(array('findOneBy' => $setting))));

		$setting->expects($this->once())
			->method('setValue')
			->with($newValue)
		;

		$config->set($setting->getName(), $newValue);
	}

	/**
	 * @expectedException \RuntimeException
	 * @expectedExceptionMessage Setting "oh-no" couldn't be found.
	 */
	public function testSet_nonexistentSetting() {
		$config = new Config();
		$config->setEntityManager($this->createEntityManagerMock($this->createEntityRepositoryMock()));

		$config->set('oh-no', 'new-value');
	}

	public function testSetMultiple() {
		$config = new Config();

		$setting = $this->getMockBuilder('Craue\ConfigBundle\Entity\Setting')->setMethods(array('setValue'))->getMock();
		$setting->setName('name');
		$newValue = 'new-value';

		$config->setEntityManager($this->createEntityManagerMock($this->createEntityRepositoryMock(array('findByNames' => array($setting->getName() => $setting)))));

		$settingsKeyValuePairs = array(
			$setting->getName() => $newValue,
		);

		$setting->expects($this->once())
			->method('setValue')
			->with($newValue)
		;

		$config->setMultiple($settingsKeyValuePairs);
	}

	public function testSetMultiple_noChanges() {
		$config = new Config();
		$setting = $this->getMockBuilder('Craue\ConfigBundle\Entity\Setting')->getMock();

		$setting->expects($this->never())
			->method('setValue')
		;

		$config->setMultiple(array());
	}

	/**
	 * @expectedException \RuntimeException
	 * @expectedExceptionMessage Setting "oh-no" couldn't be found.
	 */
	public function testSetMultiple_nonexistentSetting() {
		$config = new Config();
		$setting = Setting::create('name1');

		$config->setEntityManager($this->createEntityManagerMock($this->createEntityRepositoryMock(array('findByNames' => array($setting->getName() => $setting)))));

		$config->setMultiple(array(
			$setting->getName() => 'new-value1',
			'oh-no' => 'new-value2',
		));
	}

	public function testAll_noSettings() {
		$config = new Config();
		$config->setEntityManager($this->createEntityManagerMock($this->createEntityRepositoryMock(array('findAll' => array()))));

		$this->assertEquals(array(), $config->all());
	}

	/**
	 * @dataProvider dataGetBySection
	 */
	public function testGetBySection($section, array $foundSettings, $expectedKeyValuePairs) {
		$config = new Config();

		$config->setEntityManager($this->createEntityManagerMock($this->createEntityRepositoryMock(array('findBy' => $this->returnValueMap(array(
			array(array('section' => $section), null, null, null, $foundSettings),
		))))));

		$this->assertEquals($expectedKeyValuePairs, $config->getBySection($section));
	}

	public function dataGetBySection() {
		return array(
			array('section',		array(Setting::create('name', 'value', 'section')),	array('name' => 'value')),
			array(null,				array(Setting::create('name', 'value')),			array('name' => 'value')),
			array('other-section',	array(),											array()),
		);
	}

	/**
	 * Ensure that the repository is fetched only once from the EntityManager, but again if it's changed at runtime.
	 */
	public function testGetRepo() {
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
	 * @param array $methodsWithReturnValues Method call expectations (method name => return value).
	 * @return PHPUnit_Framework_MockObject_MockObject|SettingRepository
	 */
	protected function createEntityRepositoryMock(array $methodsWithReturnValues = array()) {
		$repo = $this->getMockBuilder('Craue\ConfigBundle\Repository\SettingRepository')
			->disableOriginalConstructor()
			->getMock()
		;

		foreach ($methodsWithReturnValues as $method => $returnValue) {
			if (!($returnValue instanceof \PHPUnit_Framework_MockObject_Stub_Return)
					&& !($returnValue instanceof \PHPUnit_Framework_MockObject_Stub_ReturnValueMap)) {
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
	 * @param PHPUnit_Framework_MockObject_MockObject|EntityRepository|null $repo
	 * @return PHPUnit_Framework_MockObject_MockObject|EntityManager
	 */
	protected function createEntityManagerMock(EntityRepository $repo = null) {
		$em = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
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

}
