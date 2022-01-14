<?php

namespace Craue\ConfigBundle\Tests\CacheAdapter;

use Craue\ConfigBundle\CacheAdapter\SymfonyCacheComponentAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;

/**
 * @group unit
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2022 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class SymfonyCacheComponentAdapterRedisCacheTest extends BaseCacheAdapterTest {

	protected function setUp() : void {
		if (empty($_ENV['REDIS_DSN'])) {
			$this->markTestSkipped('Environment variable REDIS_DSN is not set.');
		}
	}

	protected function getAdapter() {
		return new SymfonyCacheComponentAdapter(new RedisAdapter(RedisAdapter::createConnection($_ENV['REDIS_DSN']), 'craue_config'));
	}

}
