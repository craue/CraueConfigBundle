<?php

namespace Craue\ConfigBundle\Tests\CacheAdapter;

use Craue\ConfigBundle\CacheAdapter\DoctrineCacheBundleAdapter;
use Doctrine\Common\Cache\ArrayCache;

/**
 * @group unit
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2022 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 *
 * TODO remove as soon as Symfony >= 5.0 is required
 */
class DoctrineCacheBundleAdapterTest extends BaseCacheAdapterTest {

	protected function setUp() : void {
		if (!class_exists(\Doctrine\Bundle\DoctrineCacheBundle\DoctrineCacheBundle::class)) {
			$this->markTestSkipped('DoctrineCacheBundle is not available.');
		}
	}

	protected function getAdapter() {
		return new DoctrineCacheBundleAdapter(new ArrayCache());
	}

	/**
	 * TODO remove as soon as doctrine/cache >= 1.6 is required
	 */
	public function testSetMultiple_fails() {
		if (method_exists(ArrayCache::class, 'saveMultiple')) {
			$this->markTestSkipped('DoctrineCacheBundle already supports `saveMultiple`.');
		}

		$providerMock = $this->createMock(ArrayCache::class);

		$providerMock->expects($this->once())
			->method('save')
			->will($this->returnValue(false))
		;

		$adapter = new DoctrineCacheBundleAdapter($providerMock);

		$this->assertFalse($adapter->setMultiple(['key' => 'value']));
	}

}
