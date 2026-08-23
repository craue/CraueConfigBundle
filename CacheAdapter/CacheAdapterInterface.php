<?php

namespace Craue\ConfigBundle\CacheAdapter;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2026 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
interface CacheAdapterInterface {

	/**
	 * Deletes all cache entries.
	 * @return bool Whether the operation was successful.
	 */
	function clear() : bool;

	function has(string $key) : bool;

	function get(string $key) : mixed;

	/**
	 * @return bool Whether the entry was successfully stored in the cache.
	 */
	function set(string $key, mixed $value) : bool;

	/**
	 * @param array<string, mixed> $keysAndValues
	 * @return bool Whether the entries were successfully stored in the cache.
	 */
	function setMultiple(array $keysAndValues) : bool;

}
