<?php declare(strict_types=1);

namespace Craue\ConfigBundle\Tests\Twig\Extension;

use Craue\ConfigBundle\Entity\Setting;
use Craue\ConfigBundle\Tests\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2026 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
#[Group('integration')]
class ConfigTemplateExtensionIntegrationTest extends IntegrationTestCase {

	#[DataProvider('dataSettingFunction')]
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
