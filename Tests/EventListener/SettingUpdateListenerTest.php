<?php declare(strict_types=1);

namespace Craue\ConfigBundle\Tests\EventListener;

use Craue\ConfigBundle\Entity\SettingInterface;
use Craue\ConfigBundle\EventListener\SettingUpdateListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2026 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class SettingUpdateListenerTest extends TestCase {

	public function testPostUpdate() : void {
		$cache = new ArrayAdapter();
		$listener = new SettingUpdateListener($cache);

		$setting = $this->getMockBuilder(SettingInterface::class)->onlyMethods(['getName', 'getValue'])->getMockForAbstractClass();
		$name = 'name';
		$newValue = 'new-value';

		$setting->expects($this->once())
			->method('getName')
			->willReturn($name)
		;

		$setting->expects($this->once())
			->method('getValue')
			->willReturn($newValue)
		;

		$listener->postUpdate($this->getPostUpdateEventArgs($setting));

		$this->assertTrue($cache->getItem($name)->isHit());
		$this->assertSame($newValue, $cache->getItem($name)->get());
	}

	public function testPostUpdate_entityIsNotSetting() : void {
		$cache = $this->createMock(CacheItemPoolInterface::class);
		$listener = new SettingUpdateListener($cache);

		$cache->expects($this->never())
			->method($this->anything())
		;

		$listener->postUpdate($this->getPostUpdateEventArgs(new \stdClass()));
	}

	// TODO remove as soon as doctrine/orm >= 2.13 is required
	private function getPostUpdateEventArgs(object $object) : LifecycleEventArgs {
		$em = $this->createMock(EntityManagerInterface::class);

		if (!class_exists(PostUpdateEventArgs::class)) {
			return new LifecycleEventArgs($object, $em);
		}

		return new PostUpdateEventArgs($object, $em);
	}

}
