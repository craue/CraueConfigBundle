<?php

namespace Craue\ConfigBundle\CacheAdapter;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2022 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class NullAdapter implements CacheAdapterInterface {

	public function clear() {
		return true;
	}

	public function has($key) {
		return false;
	}

	public function get($key) {
		return null;
	}

	public function set($key, $value) {
		return false;
	}

	public function setMultiple(array $keysAndValues) {
		return false;
	}

}
