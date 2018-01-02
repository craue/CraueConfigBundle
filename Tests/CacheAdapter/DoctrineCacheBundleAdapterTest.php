<?php

namespace Craue\ConfigBundle\Tests\CacheAdapter;

use Craue\ConfigBundle\CacheAdapter\DoctrineCacheBundleAdapter;
use Doctrine\Common\Cache\ArrayCache;

/**
 * @group unit
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-present Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class DoctrineCacheBundleAdapterTest extends BaseCacheAdapterTest {

	protected function getAdapter() {
		return new DoctrineCacheBundleAdapter(new ArrayCache());
	}

	/**
	 * TODO remove as soon as doctrine/cache >= 1.6 is required
	 */
	public function testSetMultiple_fails() {
		if (method_exists('\Doctrine\Common\Cache\ArrayCache', 'saveMultiple')) {
			$this->markTestSkipped('DoctrineCacheBundle already supports `saveMultiple`.');
		}

		$providerMock = $this->getMockBuilder('\Doctrine\Common\Cache\ArrayCache')->getMock();

		$providerMock->expects($this->once())
			->method('save')
			->will($this->returnValue(false))
		;

		$adapter = new DoctrineCacheBundleAdapter($providerMock);

		$this->assertFalse($adapter->setMultiple(array('key' => 'value')));
	}

}
