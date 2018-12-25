<?php

namespace Craue\ConfigBundle\Tests\CacheAdapter;

use Craue\ConfigBundle\CacheAdapter\SymfonyCacheComponentAdapter;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2018 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class SymfonyCacheComponentAdapterInvalidCacheTest extends TestCase {

	/**
	 * @expectedException \InvalidArgumentException
	 * @expectedExceptionMessage Expected argument of type "Psr\Cache\CacheItemPoolInterface" or "Psr\SimpleCache\CacheInterface", but "NULL" given.
	 */
	public function testInvalidCache_null() {
		new SymfonyCacheComponentAdapter(null);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 * @expectedExceptionMessage Expected argument of type "Psr\Cache\CacheItemPoolInterface" or "Psr\SimpleCache\CacheInterface", but "stdClass" given.
	 */
	public function testInvalidCache_stdClass() {
		new SymfonyCacheComponentAdapter(new \stdClass());
	}

}
