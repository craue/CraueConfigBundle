<?php

namespace Craue\ConfigBundle\Tests\CacheAdapter;

use Craue\ConfigBundle\CacheAdapter\CacheAdapterInterface;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-present Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
abstract class BaseCacheAdapterTest extends TestCase {

	/**
	 * @return CacheAdapterInterface
	 */
	abstract protected function getAdapter();

	public function testOperations() {
		$adapter = $this->getAdapter();

		$this->assertFalse($adapter->has('key'));
		$this->assertTrue($adapter->set('key', 'value'));
		$this->assertTrue($adapter->has('key'));
		$this->assertSame('value', $adapter->get('key'));

		$this->assertTrue($adapter->set('key', 'new-value'));
		$this->assertSame('new-value', $adapter->get('key'));

		$this->assertTrue($adapter->clear());
		$this->assertFalse($adapter->has('key'));

		$this->assertTrue($adapter->setMultiple(array('key1' => 'value1', 'key2' => 'value2')));
		$this->assertTrue($adapter->has('key1'));
		$this->assertTrue($adapter->has('key2'));
		$this->assertSame('value1', $adapter->get('key1'));
		$this->assertSame('value2', $adapter->get('key2'));

		$this->assertTrue($adapter->clear());
		$this->assertFalse($adapter->has('key1'));
		$this->assertFalse($adapter->has('key2'));
	}

}
