<?php

namespace Craue\ConfigBundle\Util;

use Craue\ConfigBundle\CacheAdapter\CacheAdapterInterface;
use Craue\ConfigBundle\CacheAdapter\NullAdapter;
use Craue\ConfigBundle\Entity\SettingInterface;
use Craue\ConfigBundle\Repository\SettingRepository;
use Doctrine\ORM\EntityManager;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-present Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class Config {

	/**
	 * @var CacheAdapterInterface
	 */
	protected $cache;

	/**
	 * @var EntityManager
	 */
	protected $em;

	/**
	 * @var SettingRepository
	 */
	protected $repo;

	/**
	 * @var string
	 */
	protected $entityName;

	public function __construct(CacheAdapterInterface $cache = null) {
		$this->setCache($cache !== null ? $cache : new NullAdapter());
	}

	public function setCache(CacheAdapterInterface $cache) {
		$this->cache = $cache;
	}

	public function setEntityManager(EntityManager $em) {
		if ($this->em !== $em) {
			if ($this->em !== null) {
				$this->cache->clear();
			}

			$this->em = $em;
			$this->repo = null;
		}
	}

	public function setEntityName($entityName) {
		$this->entityName = $entityName;
		$this->repo = null;
	}

	/**
	 * @param string $name Name of the setting.
	 * @return string|null Value of the setting.
	 * @throws \RuntimeException If the setting is not defined.
	 */
	public function get($name) {
		if ($this->cache->has($name)) {
			return $this->cache->get($name);
		}

		$setting = $this->getRepo()->findOneBy(array(
			'name' => $name,
		));

		if ($setting === null) {
			throw $this->createNotFoundException($name);
		}

		$this->cache->set($name, $setting->getValue());

		return $setting->getValue();
	}

	/**
	 * @param string $name Name of the setting to update.
	 * @param string|null $value New value for the setting.
	 * @throws \RuntimeException If the setting is not defined.
	 */
	public function set($name, $value) {
		$setting = $this->getRepo()->findOneBy(array(
			'name' => $name,
		));

		if ($setting === null) {
			throw $this->createNotFoundException($name);
		}

		$setting->setValue($value);
		$this->em->flush($setting);

		$this->cache->set($name, $value);
	}

	/**
	 * @param array $newSettings List of settings (as name => value) to update.
	 * @throws \RuntimeException If at least one of the settings is not defined.
	 */
	public function setMultiple(array $newSettings) {
		if (empty($newSettings)) {
			return;
		}

		$settings = $this->getRepo()->findByNames(array_keys($newSettings));

		foreach ($newSettings as $name => $value) {
			if (!isset($settings[$name])) {
				throw $this->createNotFoundException($name);
			}

			$settings[$name]->setValue($value);
		}

		$this->em->flush();

		$this->cache->setMultiple($newSettings);
	}

	/**
	 * @return array with name => value
	 */
	public function all() {
		$settings = $this->getAsNamesAndValues($this->getRepo()->findAll());

		$this->cache->setMultiple($settings);

		return $settings;
	}

	/**
	 * @param string|null $section Name of the section to fetch settings for.
	 * @return array with name => value
	 */
	public function getBySection($section) {
		$settings = $this->getAsNamesAndValues($this->getRepo()->findBy(array('section' => $section)));

		$this->cache->setMultiple($settings);

		return $settings;
	}

	/**
	 * @param SettingInterface[] $entities
	 * @return array with name => value
	 */
	protected function getAsNamesAndValues(array $settings) {
		$result = array();

		foreach ($settings as $setting) {
			$result[$setting->getName()] = $setting->getValue();
		}

		return $result;
	}

	/**
	 * @return SettingRepository
	 */
	protected function getRepo() {
		if ($this->repo === null) {
			$this->repo = $this->em->getRepository($this->entityName);
		}

		return $this->repo;
	}

	/**
	 * @param string $name Name of the setting.
	 * @return \RuntimeException
	 */
	protected function createNotFoundException($name) {
		return new \RuntimeException(sprintf('Setting "%s" couldn\'t be found.', $name));
	}

}
