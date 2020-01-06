<?php

namespace Craue\ConfigBundle\Tests\CacheAdapter;

use Craue\ConfigBundle\CacheAdapter\SymfonyCacheComponentAdapter;
use Symfony\Component\Cache\Simple\ArrayCache;
use Symfony\Component\HttpKernel\Kernel;

/**
 * @group legacy
 * @group unit
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2020 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 *
 * TODO remove as soon as Symfony >= 5.0 is required
 */
class SymfonyCacheComponentAdapterPsr16SimpleCacheTest extends BaseCacheAdapterTest {

	protected function setUp() {
		if (!class_exists(ArrayCache::class)) {
			$this->markTestSkipped(sprintf('%s is not available in Symfony %s.', ArrayCache::class, Kernel::VERSION));
		}
	}

	protected function getAdapter() {
		return new SymfonyCacheComponentAdapter(new ArrayCache());
	}

	/**
	 * @expectedDeprecation Configuring a cache of type Psr\SimpleCache\CacheInterface is deprecated since CraueConfigBundle 2.2.1. Use Psr\Cache\CacheItemPoolInterface instead.
	 */
	public function testOperations() {
		parent::testOperations();
	}

	/**
	 * @expectedDeprecation Configuring a cache of type Psr\SimpleCache\CacheInterface is deprecated since CraueConfigBundle 2.2.1. Use Psr\Cache\CacheItemPoolInterface instead.
	 */
	public function testSetMultiple_fails() {
		$providerMock = $this->createMock(ArrayCache::class);

		$providerMock->expects($this->once())
			->method('setMultiple')
			->will($this->returnValue(false))
		;

		$adapter = new SymfonyCacheComponentAdapter($providerMock);

		$this->assertFalse($adapter->setMultiple(['key' => 'value']));
	}

}
