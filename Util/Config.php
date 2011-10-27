<?php

namespace Craue\ConfigBundle\Util;

use Craue\ConfigBundle\Entity\Setting;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011 Christian Raue
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
			throw new \RuntimeException(sprintf('Setting "%s" couldn\'t be found.', $name));
		}

		return $setting->getValue();
	}

	/**
	 * @return array[Setting]
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

}
