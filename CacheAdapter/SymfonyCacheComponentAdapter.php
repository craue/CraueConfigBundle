<?php

namespace Craue\ConfigBundle\CacheAdapter;

use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Adapter\Psr16Adapter;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2026 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class SymfonyCacheComponentAdapter implements CacheAdapterInterface {

	/**
	 * @var CacheItemPoolInterface
	 */
	private $cache;

	public function __construct(CacheInterface|CacheItemPoolInterface $cache) {
		if ($cache instanceof CacheInterface) {
			@trigger_error(sprintf('Configuring a cache of type %s is deprecated since CraueConfigBundle 2.2.1. Use %s instead.', CacheInterface::class, CacheItemPoolInterface::class), E_USER_DEPRECATED);

			$this->cache = new Psr16Adapter($cache);
			return;
		}

		$this->cache = $cache;
	}

	public function clear() : bool {
		return $this->cache->clear();
	}

	public function has(string $key) : bool {
		return $this->cache->hasItem($key);
	}

	public function get(string $key) : mixed {
		return $this->cache->getItem($key)->get();
	}

	public function set(string $key, mixed $value) : bool {
		$cacheItem = $this->cache->getItem($key);
		$cacheItem->set($value);

		return $this->cache->save($cacheItem);
	}

	public function setMultiple(array $keysAndValues) : bool {
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
