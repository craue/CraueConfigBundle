<?php

namespace Craue\ConfigBundle\Tests\CacheAdapter;

use Craue\ConfigBundle\CacheAdapter\SymfonyCacheComponentAdapter;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\CacheItem;

/**
 * @group unit
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-present Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class SymfonyCacheComponentAdapterTest extends BaseCacheAdapterTest {

	public static function setUpBeforeClass() {
		// TODO remove as soon as Symfony >= 3.1 is required
		if (!class_exists('\Symfony\Component\Cache\Adapter\ArrayAdapter')) {
			self::markTestSkipped('Symfony Cache component not available.');
		}
	}

	protected function getAdapter() {
		return new SymfonyCacheComponentAdapter(new ArrayAdapter());
	}

	public function testSetMultiple_fails() {
		$providerMock = $this->getMockBuilder('\Symfony\Component\Cache\Adapter\ArrayAdapter')->getMock();

		$providerMock->expects($this->once())
			->method('getItem')
			->will($this->returnValue(new CacheItem()))
		;

		$providerMock->expects($this->once())
			->method('saveDeferred')
			->will($this->returnValue(false))
		;

		$adapter = new SymfonyCacheComponentAdapter($providerMock);

		$this->assertFalse($adapter->setMultiple(array('key' => 'value')));
	}

}
