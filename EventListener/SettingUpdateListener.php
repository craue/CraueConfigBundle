<?php

namespace Craue\ConfigBundle\EventListener;

use Craue\ConfigBundle\CacheAdapter\CacheAdapterInterface;
use Craue\ConfigBundle\CacheAdapter\NullAdapter;
use Craue\ConfigBundle\Entity\SettingInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2023 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class SettingUpdateListener {

	/**
	 * @var CacheAdapterInterface
	 */
	private $cache;

	public function __construct(?CacheAdapterInterface $cache = null) {
		$this->cache = $cache ?? new NullAdapter();
	}

	// TODO add `PostUpdateEventArgs` type-hint as soon as doctrine/orm >= 2.13 is required
	public function postUpdate($eventArgs) : void {
		assert($eventArgs instanceof LifecycleEventArgs);

		$entity = $eventArgs->getObject();

		if (!$entity instanceof SettingInterface) {
			return;
		}

		$this->updateCache($entity);
	}

	private function updateCache(SettingInterface $setting) : void {
		$this->cache->set($setting->getName(), $setting->getValue());
	}

}
