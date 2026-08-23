<?php

namespace Craue\ConfigBundle\Tests\Twig\Extension;

use Craue\ConfigBundle\Entity\Setting;
use Craue\ConfigBundle\Tests\IntegrationTestCase;

/**
 * @group integration
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2026 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class ConfigTemplateExtensionIntegrationTest extends IntegrationTestCase {

	/**
	 * @dataProvider dataSettingFunction
	 */
	public function testSettingFunction($platform, $config, $requiredExtension, $name, $value) : void {
		$this->initClient($requiredExtension, ['environment' => $platform, 'config' => $config]);
		$this->persistSetting(Setting::create($name, $value));

		$this->assertSame($value, $this->getTwig()->render('@IntegrationTest/Settings/setting.html.twig', [
			'name' => $name,
		]));
	}

	public static function dataSettingFunction() : iterable {
		return self::duplicateTestDataForEachPlatform([
			['name', 'value'],
		]);
	}

}
