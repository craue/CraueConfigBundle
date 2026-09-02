<?php declare(strict_types=1);

namespace Craue\ConfigBundle\Tests\Util;

use Craue\ConfigBundle\Entity\Setting;
use Craue\ConfigBundle\Util\SettingsUtil;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2026 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class SettingsUtilUnitTest extends TestCase {

	public static function dataGetAsNamesAndValues() : iterable {
		yield 'empty' => [
			[],
			[],
		];
		yield 'one setting' => [
			[
				Setting::create('name', 'value'),
			],
			[
				'name' => 'value',
			],
		];
		yield 'two settings' => [
			[
				Setting::create('name1', 'value1'),
				Setting::create('name2', 'value2'),
			],
			[
				'name1' => 'value1',
				'name2' => 'value2',
			],
		];
		yield 'duplicate names' => [
			[
				Setting::create('name', 'value1'),
				Setting::create('name', 'value2'),
			],
			[
				'name' => 'value2',
			],
		];
	}

	/**
	 * @dataProvider dataGetAsNamesAndValues
	 */
	public function testGetAsNamesAndValues(array $settings, array $expectedResult) : void {
		$this->assertSame($expectedResult, SettingsUtil::getAsNamesAndValues($settings));
	}

	public static function dataGetSections() : iterable {
		$setting1 = Setting::create('name1', null, 'section1');
		$setting2 = Setting::create('name2', null, 'section2');
		$setting3 = Setting::create('name3', null);

		yield 'empty' => [
			[],
			[],
		];
		yield 'one setting' => [
			[
				$setting1,
			],
			[
				'section1',
			],
		];
		yield 'two settings' => [
			[
				$setting2,
				$setting1,
			],
			[
				'section1',
				'section2',
			],
		];
		yield 'duplicate setting' => [
			[
				$setting2,
				$setting1,
				$setting2,
			],
			[
				'section1',
				'section2',
			],
		];
		yield 'null section first' => [
			[
				$setting1,
				$setting2,
				$setting3,
				$setting3,
			],
			[
				null,
				'section1',
				'section2',
			],
		];
	}

	/**
	 * @dataProvider dataGetSections
	 */
	public function testGetSections(array $settings, array $expectedResult) : void {
		$this->assertSame($expectedResult, SettingsUtil::getSections($settings));
	}

}
