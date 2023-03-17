<?php

namespace Craue\ConfigBundle\Tests\CacheAdapter;

use Craue\ConfigBundle\CacheAdapter\SymfonyCacheComponentAdapter;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\CacheItem;

/**
 * @group unit
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2023 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class SymfonyCacheComponentAdapterPsr6AdvancedCacheTest extends BaseCacheAdapterTest {

	protected function getAdapter() {
		return new SymfonyCacheComponentAdapter(new ArrayAdapter());
	}

	public function testSetMultiple_fails() {
		$providerMock = $this->createMock(ArrayAdapter::class);

		$providerMock->expects($this->once())
			->method('getItem')
			->will($this->returnValue(new CacheItem()))
		;

		$providerMock->expects($this->once())
			->method('saveDeferred')
			->will($this->returnValue(false))
		;

		$adapter = new SymfonyCacheComponentAdapter($providerMock);

		$this->assertFalse($adapter->setMultiple(['key' => 'value']));
	}

}
