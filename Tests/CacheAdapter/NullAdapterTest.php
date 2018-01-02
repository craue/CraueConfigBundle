<?php

namespace Craue\ConfigBundle\Tests\CacheAdapter;

use Craue\ConfigBundle\CacheAdapter\CacheAdapterInterface;
use Craue\ConfigBundle\CacheAdapter\NullAdapter;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-present Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class NullAdapterTest extends TestCase {

	/**
	 * @return CacheAdapterInterface
	 */
	protected function getAdapter() {
		return new NullAdapter();
	}

	public function testClear() {
		$this->assertTrue($this->getAdapter()->clear());
	}

	public function testHas() {
		$this->assertFalse($this->getAdapter()->has('key'));
	}

	public function testGet() {
		$this->assertNull($this->getAdapter()->get('key'));
	}

	public function testSet() {
		$this->assertFalse($this->getAdapter()->set('key', 'value'));
	}

	public function testSetMultiple() {
		$this->assertFalse($this->getAdapter()->setMultiple(array()));
	}

}
