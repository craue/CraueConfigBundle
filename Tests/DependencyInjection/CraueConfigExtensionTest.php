<?php declare(strict_types=1);

namespace Craue\ConfigBundle\Tests\DependencyInjection;

use Craue\ConfigBundle\DependencyInjection\CraueConfigExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @group unit
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2026 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class CraueConfigExtensionTest extends TestCase {

	public static function dataEntityManager() : iterable {
		yield 'default entity manager' => [
			[],
			'doctrine.orm.default_entity_manager',
		];
		yield 'custom entity manager' => [
			[
				[
					'entity_manager' => 'custom',
				],
			],
			'doctrine.orm.custom_entity_manager',
		];
	}

	/**
	 * @dataProvider dataEntityManager
	 */
	public function testEntityManager(array $configs, string $expectedEntityManager) : void {
		$container = new ContainerBuilder();
		$extension = new CraueConfigExtension();
		$extension->load($configs, $container);

		$methodCalls = $container->getDefinition('craue_config_default')->getMethodCalls();

		$this->assertCount(2, $methodCalls);
		$this->assertSame('setEntityManager', $methodCalls[1][0]);
		$this->assertInstanceOf(Reference::class, $methodCalls[1][1][0]);
		$this->assertSame($expectedEntityManager, (string) $methodCalls[1][1][0]);
	}

}
