<?php

namespace Craue\ConfigBundle\Tests\Twig\Extension;

use Craue\ConfigBundle\Tests\IntegrationTestCase;

/**
 * @group integration
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2014 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class ConfigTemplateExtensionIntegrationTest extends IntegrationTestCase {

	/**
	 * @dataProvider dataSettingFunction
	 */
	public function testSettingFunction($name, $value) {
		$this->persistSetting($name, $value);

		$this->assertSame($value, $this->getTwig()->render('IntegrationTestBundle:Settings:setting.html.twig', array(
			'name' => $name,
		)));
	}

	public function dataSettingFunction() {
		return array(
			array('name', 'value'),
		);
	}

}
