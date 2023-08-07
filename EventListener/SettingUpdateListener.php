<?php declare(strict_types=1);

namespace Craue\ConfigBundle\EventListener;

use Craue\ConfigBundle\CacheAdapter\CacheAdapterInterface;
use Craue\ConfigBundle\CacheAdapter\NullAdapter;
use Craue\ConfigBundle\Entity\SettingInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2026 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class SettingUpdateListener {

	private CacheAdapterInterface $cache;

	public function __construct(?CacheAdapterInterface $cache = null) {
		$this->cache = $cache ?? new NullAdapter();
	}

	// TODO add `PostUpdateEventArgs` type-hint as soon as doctrine/orm >= 2.13 is required
	/**
	 * @param LifecycleEventArgs<\Doctrine\ORM\EntityManagerInterface> $eventArgs
	 */
	public function postUpdate(LifecycleEventArgs $eventArgs) : void {
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
