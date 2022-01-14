<?php

namespace Craue\ConfigBundle\CacheAdapter;

use Doctrine\Common\Cache\CacheProvider;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2022 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class DoctrineCacheBundleAdapter implements CacheAdapterInterface {

	/**
	 * @var CacheProvider
	 */
	private $cache;

	public function __construct(CacheProvider $cache) {
		$this->cache = $cache;
	}

	public function clear() {
		return $this->cache->deleteAll();
	}

	public function has($key) {
		return $this->cache->contains($key);
	}

	public function get($key) {
		return $this->cache->fetch($key);
	}

	public function set($key, $value) {
		return $this->cache->save($key, $value);
	}

	public function setMultiple(array $keysAndValues) {
		return $this->cache->saveMultiple($keysAndValues);
	}

}
