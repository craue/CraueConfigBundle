<?php declare(strict_types=1);

namespace Craue\ConfigBundle\CacheAdapter;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2026 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class NullAdapter implements CacheAdapterInterface {

	public function clear() : bool {
		return true;
	}

	public function has(string $key) : bool {
		return false;
	}

	public function get(string $key) : mixed {
		return null;
	}

	public function set(string $key, mixed $value) : bool {
		return false;
	}

	public function setMultiple(array $keysAndValues) : bool {
		return false;
	}

}
