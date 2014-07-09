<?php

namespace Craue\ConfigBundle\Tests\Util;

use Craue\ConfigBundle\Entity\Setting;
use Craue\ConfigBundle\Tests\IntegrationTestCase;
use Craue\ConfigBundle\Util\Config;

/**
 * @group unit
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2014 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class ConfigTest extends IntegrationTestCase {

	public function testGet() {
		$this->persistSetting('name', 'value');

		$this->assertEquals('value', $this->getConfig()->get('name'));
	}

	/**
	 * @expectedException \RuntimeException
	 * @expectedExceptionMessage Setting "oh-no" couldn't be found.
	 */
	public function testGet_nonexistentSetting() {
		$this->getConfig()->get('oh-no');
	}

	public function testSet() {
		$this->persistSetting('name', 'old-value');

		$this->getConfig()->set('name', 'new-value');

		$setting = $this->getSettingsRepo()->findOneBy(array('name' => 'name'));
		$this->assertEquals('new-value', $setting->getValue());
	}

	/**
	 * @expectedException \RuntimeException
	 * @expectedExceptionMessage Setting "oh-no" couldn't be found.
	 */
	public function testSet_nonexistentSetting() {
		$this->getConfig()->set('oh-no', 'new-value');
	}

	public function testSetMultiple() {
		$this->persistSetting('name1', 'old-value1');
		$this->persistSetting('name2', 'old-value2');
		$this->persistSetting('name3', 'old-value3');

		$this->getConfig()->setMultiple(array(
			'name1' => 'new-value1',
			'name2' => 'new-value2',
		));

		$this->assertEquals(array(
			'name1' => 'new-value1',
			'name2' => 'new-value2',
			'name3' => 'old-value3',
		), $this->getConfig()->all());
	}

	public function testSetMultiple_noChanges() {
		$this->persistSetting('name1', 'old-value1');
		$this->persistSetting('name2', 'old-value2');

		$this->getConfig()->setMultiple(array());

		$this->assertEquals(array(
			'name1' => 'old-value1',
			'name2' => 'old-value2',
		), $this->getConfig()->all());
	}

	/**
	 * @expectedException \RuntimeException
	 * @expectedExceptionMessage Setting "oh-no" couldn't be found.
	 */
	public function testSetMultiple_nonexistentSetting() {
		$this->persistSetting('name1', 'old-value1');

		$this->getConfig()->setMultiple(array(
			'name1' => 'new-value1',
			'oh-no' => 'new-value2',
		));
	}

	public function testAll_noSettings() {
		$this->assertEquals(array(), $this->getConfig()->all());
	}

	/**
	 * Ensure that the repository is fetched only once from the EntityManager, but again if it's changed at runtime.
	 */
	public function testGetRepo() {
		$config = new Config();
		$method = new \ReflectionMethod($config, 'getRepo');
		$method->setAccessible(true);

		// 1st call to `getRepo` using a mocked EntityManager
		$config->setEntityManager($this->getMockedEntityManager());

		// invoke twice to ensure the cached instance is used
		$method->invoke($config);
		$method->invoke($config);

		// 2nd call to `getRepo` using a different mocked EntityManager
		$config->setEntityManager($this->getMockedEntityManager());

		// invoke twice to ensure the cached instance is used
		$method->invoke($config);
		$method->invoke($config);
	}

	/**
	 * @return PHPUnit_Framework_MockObject_MockObject|\Doctrine\ORM\EntityManager
	 */
	protected function getMockedEntityManager() {
		$repo = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
			->disableOriginalConstructor()
			->getMock()
		;
		$repo->expects($this->any())
			->method('find')
			->will($this->returnValue(new Setting()))
		;

		$em = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
			->disableOriginalConstructor()
			->getMock()
		;
		$em->expects($this->once())
			->method('getRepository')
			->will($this->returnValue($repo))
		;

		return $em;
	}

}
