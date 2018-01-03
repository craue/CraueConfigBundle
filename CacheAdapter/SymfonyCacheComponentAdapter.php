<?php

namespace Craue\ConfigBundle\CacheAdapter;

use Psr\Cache\CacheItemPoolInterface;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2018 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class SymfonyCacheComponentAdapter implements CacheAdapterInterface {

	/**
	 * @var CacheItemPoolInterface
	 */
	private $cache;

	public function __construct(CacheItemPoolInterface $cache) {
		$this->cache = $cache;
	}

	public function clear() {
		return $this->cache->clear();
	}

	public function has($key) {
		return $this->cache->hasItem($key);
	}

	public function get($key) {
		return $this->cache->getItem($key)->get();
	}

	public function set($key, $value) {
		$cacheItem = $this->cache->getItem($key);
		$cacheItem->set($value);

		return $this->cache->save($cacheItem);
	}

	public function setMultiple(array $keysAndValues) {
		foreach ($keysAndValues as $key => $value) {
			$cacheItem = $this->cache->getItem($key);
			$cacheItem->set($value);
			if (!$this->cache->saveDeferred($cacheItem)) {
				return false;
			}
		}

		return $this->cache->commit();
	}

}
