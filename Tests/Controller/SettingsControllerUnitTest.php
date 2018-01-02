<?php

namespace Craue\ConfigBundle\Tests\Controller;

use Craue\ConfigBundle\Controller\SettingsController;
use Craue\ConfigBundle\Entity\Setting;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-present Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class SettingsControllerUnitTest extends TestCase {

	/**
	 * @dataProvider dataGetSections
	 */
	public function testGetSections(array $settings, array $expectedResult) {
		$controller = new SettingsController();
		$method = new \ReflectionMethod($controller, 'getSections');
		$method->setAccessible(true);

		$this->assertSame($expectedResult, $method->invoke($controller, $settings));
	}

	public function dataGetSections() {
		$setting1 = Setting::create('name1', null, 'section1');
		$setting2 = Setting::create('name2', null, 'section2');
		$setting3 = Setting::create('name3', null);

		return array(
			array(array(),												array()),
			array(array($setting1),										array('section1')),
			array(array($setting1, $setting2),							array('section1', 'section2')),
			array(array($setting2, $setting1, $setting2),				array('section1', 'section2')),
			array(array($setting1, $setting2, $setting3, $setting3),	array(null, 'section1', 'section2')),
		);
	}

	/**
	 * @dataProvider dataGetSettingByName
	 */
	public function testGetSettingByName(array $settings, $name, $expectedResult) {
		$controller = new SettingsController();
		$method = new \ReflectionMethod($controller, 'getSettingByName');
		$method->setAccessible(true);

		$this->assertSame($expectedResult, $method->invoke($controller, $settings, $name));
	}

	public function dataGetSettingByName() {
		$setting1 = Setting::create('name1');
		$setting2 = Setting::create('name2');

		return array(
			array(array($setting1),				'name1',	$setting1),
			array(array($setting1, $setting2),	'name2',	$setting2),
			array(array($setting1, $setting2),	'name3',	null),
		);
	}

}
