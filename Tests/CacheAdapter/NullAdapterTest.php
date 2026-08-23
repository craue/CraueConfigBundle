<?php

namespace Craue\ConfigBundle\Tests\CacheAdapter;

use Craue\ConfigBundle\CacheAdapter\NullAdapter;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2026 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class NullAdapterTest extends TestCase {

	protected function getAdapter() : NullAdapter {
		return new NullAdapter();
	}

	public function testClear() : void {
		$this->assertTrue($this->getAdapter()->clear());
	}

	public function testHas() : void {
		$this->assertFalse($this->getAdapter()->has('key'));
	}

	public function testGet() : void {
		$this->assertNull($this->getAdapter()->get('key'));
	}

	public function testSet() : void {
		$this->assertFalse($this->getAdapter()->set('key', 'value'));
	}

	public function testSetMultiple() : void {
		$this->assertFalse($this->getAdapter()->setMultiple([]));
	}

}
