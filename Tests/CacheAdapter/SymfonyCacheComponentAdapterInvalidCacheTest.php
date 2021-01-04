<?php

namespace Craue\ConfigBundle\Tests\CacheAdapter;

use Craue\ConfigBundle\CacheAdapter\SymfonyCacheComponentAdapter;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2021 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class SymfonyCacheComponentAdapterInvalidCacheTest extends TestCase {

	public function testInvalidCache_null() {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Expected argument of type "Psr\Cache\CacheItemPoolInterface" or "Psr\SimpleCache\CacheInterface", but "NULL" given.');

		new SymfonyCacheComponentAdapter(null);
	}

	public function testInvalidCache_stdClass() {
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Expected argument of type "Psr\Cache\CacheItemPoolInterface" or "Psr\SimpleCache\CacheInterface", but "stdClass" given.');

		new SymfonyCacheComponentAdapter(new \stdClass());
	}

}
