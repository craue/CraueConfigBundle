<?php

namespace Craue\ConfigBundle\Tests\CacheAdapter;

use Craue\ConfigBundle\CacheAdapter\SymfonyCacheComponentAdapter;
use Symfony\Component\Cache\Simple\ArrayCache;

/**
 * @group legacy
 * @group unit
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2019 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class SymfonyCacheComponentAdapterPsr16SimpleCacheTest extends BaseCacheAdapterTest {

	protected function getAdapter() {
		return new SymfonyCacheComponentAdapter(new ArrayCache());
	}

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
