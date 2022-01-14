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

}
