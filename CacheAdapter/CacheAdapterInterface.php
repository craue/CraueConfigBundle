<?php

namespace Craue\ConfigBundle\CacheAdapter;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2022 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
interface CacheAdapterInterface {

	/**
	 * Deletes all cache entries.
	 * @return bool Whether the operation was successful.
	 */
	function clear();

	/**
	 * @param string $key
	 * @return bool
	 */
	function has($key);

	/**
	 * @param string $key
	 * @return mixed
	 */
	function get($key);

	/**
	 * @param string $key
	 * @param mixed $value
	 * @return bool Whether the entry was successfully stored in the cache.
	 */
	function set($key, $value);

	/**
	 * @param array $keysAndValues
	 * @return bool Whether the entries were successfully stored in the cache.
	 */
	function setMultiple(array $keysAndValues);

}
