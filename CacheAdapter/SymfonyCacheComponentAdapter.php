<?php

namespace Craue\ConfigBundle\CacheAdapter;

use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\Psr16Adapter;
use Symfony\Component\Cache\Adapter\SimpleCacheAdapter;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2019 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class SymfonyCacheComponentAdapter implements CacheAdapterInterface {

	/**
	 * @var CacheItemPoolInterface
	 */
	private $cache;

	public function __construct($cache) {
		if ($cache instanceof CacheItemPoolInterface) {
			$this->cache = $cache;
			return;
		}

		if ($cache instanceof CacheInterface) {
			// TODO remove as soon as Symfony >= 4.3 is required
			if (!class_exists('Symfony\Component\Cache\Adapter\Psr16Adapter')) {
				$this->cache = new SimpleCacheAdapter($cache);
				return;
			}

			$this->cache = new Psr16Adapter($cache);
			return;
		}

		throw new \InvalidArgumentException(sprintf('Expected argument of type "%s" or "%s", but "%s" given.',
				CacheItemPoolInterface::class,
				CacheInterface::class,
				is_object($cache) ? get_class($cache) : gettype($cache)));
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
