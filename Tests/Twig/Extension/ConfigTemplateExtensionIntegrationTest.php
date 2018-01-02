<?php

namespace Craue\ConfigBundle\Tests\Twig\Extension;

use Craue\ConfigBundle\Entity\Setting;
use Craue\ConfigBundle\Tests\IntegrationTestCase;

/**
 * @group integration
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-present Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class ConfigTemplateExtensionIntegrationTest extends IntegrationTestCase {

	/**
	 * @dataProvider dataSettingFunction
	 */
	public function testSettingFunction($platform, $config, $requiredExtension, $name, $value) {
		$this->initClient($requiredExtension, array('environment' => $platform, 'config' => $config));
		$this->persistSetting(Setting::create($name, $value));

		$this->assertSame($value, $this->getTwig()->render('@IntegrationTest/Settings/setting.html.twig', array(
			'name' => $name,
		)));
	}

	public function dataSettingFunction() {
		return self::duplicateTestDataForEachPlatform(array(
			array('name', 'value'),
		));
	}

}
