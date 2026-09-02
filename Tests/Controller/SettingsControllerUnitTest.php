<?php declare(strict_types=1);

namespace Craue\ConfigBundle\Tests\Controller;

use Craue\ConfigBundle\Controller\SettingsController;
use Craue\ConfigBundle\Entity\Setting;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2026 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class SettingsControllerUnitTest extends TestCase {

	/**
	 * @dataProvider dataGetSections
	 */
	public function testGetSections(array $settings, array $expectedResult) : void {
		$controller = new SettingsController();
		$method = new \ReflectionMethod($controller, 'getSections');

		$this->assertSame($expectedResult, $method->invoke($controller, $settings));
	}

	public static function dataGetSections() : iterable {
		$setting1 = Setting::create('name1', null, 'section1');
		$setting2 = Setting::create('name2', null, 'section2');
		$setting3 = Setting::create('name3', null);

		return [
			[[],											[]],
			[[$setting1],									['section1']],
			[[$setting1, $setting2],						['section1', 'section2']],
			[[$setting2, $setting1, $setting2],				['section1', 'section2']],
			[[$setting1, $setting2, $setting3, $setting3],	[null, 'section1', 'section2']],
		];
	}

}
