<?php declare(strict_types=1);

namespace Craue\ConfigBundle\Tests\DependencyInjection;

use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
use Craue\ConfigBundle\DependencyInjection\Configuration;
use Craue\ConfigBundle\Entity\BaseSetting;
use Craue\ConfigBundle\Entity\Setting;
use Craue\ConfigBundle\Entity\SettingInterface;
use Craue\ConfigBundle\Tests\IntegrationTestBundle\Entity\CustomSetting;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

/**
 * @group unit
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2026 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class ConfigurationTest extends TestCase {

	private Configuration $configuration;
	private Processor $processor;

	protected function setUp() : void {
		$this->configuration = new Configuration();
		$this->processor = new Processor();
	}

	public function testDefaults() : void {
		$config = $this->processor->processConfiguration($this->configuration, []);

		$this->assertSame('doctrine_orm', $config['db_driver']);
		$this->assertSame(Setting::class, $config['entity_name']);
	}

	public static function dataValidValue() : iterable {
		yield ['db_driver',		'doctrine_orm'];
		yield ['entity_name',	CustomSetting::class];
	}

	/**
	 * @dataProvider dataValidValue
	 */
	public function testValidValue(string $key, mixed $value) : void {
		$config = $this->processor->processConfiguration($this->configuration, [
			'craue_config' => [
				$key => $value,
			],
		]);

		$this->assertSame($value, $config[$key]);
	}

	public static function dataInvalidValue() : iterable {
		// TODO remove old error message variants as soon as Symfony >= 7.3 is required
		yield ['db_driver',		null,		InstalledVersions::satisfies(new VersionParser(), 'symfony/config', '<7.3')
												? 'The value null is not allowed for path "craue_config.db_driver". Permissible values: "doctrine_orm"'
												: 'The value of type "null" is not allowed for path "craue_config.db_driver". Permissible values: "doctrine_orm".'];
		// TODO add trailing dot to the error messages as soon as Symfony >= 7.3 is required
		yield ['db_driver',		'',			'The value "" is not allowed for path "craue_config.db_driver". Permissible values: "doctrine_orm"'];
		yield ['db_driver',		'other',		'The value "other" is not allowed for path "craue_config.db_driver". Permissible values: "doctrine_orm"'];

		yield ['entity_name',	null,							'The path "craue_config.entity_name" cannot contain an empty value, but got null.'];
		yield ['entity_name',	'',								'The path "craue_config.entity_name" cannot contain an empty value, but got "".'];
		yield ['entity_name',	0,								'Invalid configuration for path "craue_config.entity_name": The value 0 is not a string.'];
		yield ['entity_name',	Setting::class . 'DoesNotExist',	'Invalid configuration for path "craue_config.entity_name": The class "' . str_replace('\\', '\\\\', Setting::class . 'DoesNotExist') . '" doesn\'t exist.'];
		yield ['entity_name',	SettingInterface::class,			'Invalid configuration for path "craue_config.entity_name": The class "' . str_replace('\\', '\\\\', SettingInterface::class) . '" doesn\'t exist.'];
		yield ['entity_name',	BaseSetting::class,				'Invalid configuration for path "craue_config.entity_name": The class "' . str_replace('\\', '\\\\', BaseSetting::class) . '" must be a non-abstract implementation of Craue\ConfigBundle\Entity\SettingInterface.'];
	}

	/**
	 * @dataProvider dataInvalidValue
	 */
	public function testInvalidValue(string $key, mixed $value, string $expectedMessage) : void {
		$this->expectException(InvalidConfigurationException::class);
		$this->expectExceptionMessage($expectedMessage);

		$this->processor->processConfiguration($this->configuration, [
			'craue_config' => [
				$key => $value,
			],
		]);
	}

}
