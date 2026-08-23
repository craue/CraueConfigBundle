<?php

namespace Craue\ConfigBundle\Util;

use Craue\ConfigBundle\CacheAdapter\CacheAdapterInterface;
use Craue\ConfigBundle\CacheAdapter\NullAdapter;
use Craue\ConfigBundle\Entity\SettingInterface;
use Craue\ConfigBundle\Repository\SettingRepository;
use Doctrine\ORM\EntityManager;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2026 Christian Raue
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
	 * @var SettingRepository|null
	 */
	protected $repo;

	/**
	 * @var class-string
	 */
	protected $entityName;

	public function __construct(?CacheAdapterInterface $cache = null) {
		$this->setCache($cache ?? new NullAdapter());
	}

	public function setCache(CacheAdapterInterface $cache) : void {
		$this->cache = $cache;
	}

	public function setEntityManager(EntityManager $em) : void {
		if ($this->em !== $em) {
			if ($this->em !== null) {
				$this->cache->clear();
			}

			$this->em = $em;
			$this->repo = null;
		}
	}

	/**
	 * @param class-string $entityName
	 */
	public function setEntityName(string $entityName) : void {
		$this->entityName = $entityName;
		$this->repo = null;
	}

	/**
	 * @param string $name Name of the setting.
	 * @return string|null Value of the setting.
	 * @throws \RuntimeException If the setting is not defined.
	 */
	public function get(string $name) : ?string {
		if ($this->cache->has($name)) {
			return $this->cache->get($name);
		}

		$setting = $this->getRepo()->findOneBy([
			'name' => $name,
		]);

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
	public function set(string $name, ?string $value) : void {
		$setting = $this->getRepo()->findOneBy([
			'name' => $name,
		]);

		if ($setting === null) {
			throw $this->createNotFoundException($name);
		}

		$setting->setValue($value);
		$this->em->flush();

		$this->cache->set($name, $value);
	}

	/**
	 * @param array<string, ?string> $newSettings List of settings (as name => value) to update.
	 * @throws \RuntimeException If at least one of the settings is not defined.
	 */
	public function setMultiple(array $newSettings) : void {
		if ($newSettings === []) {
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
	 * @return array<string, ?string> with name => value
	 */
	public function all() : array {
		$settings = $this->getAsNamesAndValues($this->getRepo()->findAll());

		$this->cache->setMultiple($settings);

		return $settings;
	}

	/**
	 * @param string|null $section Name of the section to fetch settings for.
	 * @return array<string, ?string> with name => value
	 */
	public function getBySection(?string $section) : array {
		$settings = $this->getAsNamesAndValues($this->getRepo()->findBy(['section' => $section]));

		$this->cache->setMultiple($settings);

		return $settings;
	}

	/**
	 * @param SettingInterface[] $settings
	 * @return array<string, ?string> with name => value
	 */
	protected function getAsNamesAndValues(array $settings) : array {
		$result = [];

		foreach ($settings as $setting) {
			$result[$setting->getName()] = $setting->getValue();
		}

		return $result;
	}

	protected function getRepo() : SettingRepository {
		if ($this->repo === null) {
			$repo = $this->em->getRepository($this->entityName);

			if (!$repo instanceof SettingRepository) {
				throw new \RuntimeException(sprintf('Entity repository of type "%s" expected, but got "%s".', SettingRepository::class, get_class($repo)));
			}

			$this->repo = $repo;
		}

		return $this->repo;
	}

	/**
	 * @param string $name Name of the setting.
	 * @return \RuntimeException
	 */
	protected function createNotFoundException(string $name) : \RuntimeException {
		return new \RuntimeException(sprintf('Setting "%s" couldn\'t be found.', $name));
	}

}
