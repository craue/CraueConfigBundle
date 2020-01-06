<?php

namespace Craue\ConfigBundle\CacheAdapter;

use Doctrine\Common\Cache\CacheProvider;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2020 Christian Raue
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
		// TODO remove as soon as doctrine/cache >= 1.6 is required
		if (!method_exists($this->cache, 'saveMultiple')) {
			foreach ($keysAndValues as $key => $value) {
				if (!$this->cache->save($key, $value)) {
					return false;
				}
			}

			return true;
		}

		return $this->cache->saveMultiple($keysAndValues);
	}

}
