<?php

namespace Craue\ConfigBundle\Util;

use Craue\ConfigBundle\Entity\Setting;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2012 Christian Raue
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Config {

	/**
	 * @var EntityManager
	 */
	protected $em;

	/**
	 * @var EntityRepository
	 */
	protected $repo;

	public function setEntityManager(EntityManager $em) {
		$this->em = $em;
	}

	/**
	 * @param string $name Name of the setting.
	 * @return string|null Value of the setting.
	 * @throws \RuntimeException If setting is not defined.
	 */
	public function get($name) {
		$setting = $this->getRepo()->findOneBy(array(
			'name' => $name,
		));

		if ($setting === null) {
			throw $this->createNotFoundException($name);
		}

		return $setting->getValue();
	}

	/**
	 * @param string $name Name of the setting.
	 * @param string|null $value Value of the setting.
	 * @throws \RuntimeException If setting is not defined.
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
	}

	/**
	 * @param array $newSettings List of settings (as name => value) to update.
	 */
	public function setMultiple(array $newSettings)
	{
		if (empty($newSettings)) {
			return;
		}

		$settings = $this->em->createQueryBuilder()
			->select('s')
			->from('CraueConfigBundle:Setting', 's', 's.name')
			->where('s.name IN (:names)')
			->getQuery()
			->execute(array('names' => array_keys($newSettings)))
		;

		foreach ($newSettings as $name => $value) {
			if (!isset($settings[$name])) {
				throw $this->createNotFoundException($name);
			}

			$settings[$name]->setValue($value);
		}

		$this->em->flush();
	}

	/**
	 * @return string[] with key => value
	 */
	public function all() {
		$settings = array();

		foreach ($this->getRepo()->findAll() as $setting) {
			$settings[$setting->getName()] = $setting->getValue();
		}

		return $settings;
	}

	/**
	 * @return EntityRepository
	 */
	protected function getRepo() {
		if ($this->repo === null) {
			$this->repo = $this->em->getRepository(get_class(new Setting()));
		}

		return $this->repo;
	}

	/**
	 * @param string $name Name of the setting.
	 * @return \RuntimeException 
	 */
	protected function createNotFoundException($name)
	{
		return new \RuntimeException(sprintf('Setting "%s" couldn\'t be found.', $name));
	}
}
