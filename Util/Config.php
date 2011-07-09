<?php

namespace Craue\ConfigBundle\Util;

use Craue\ConfigBundle\Entity\Setting;
use Doctrine\ORM\EntityManager;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011 Christian Raue
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Config {

	protected $em;

	protected $repo;

	public function setEntityManager(EntityManager $em) {
		$this->em = $em;
	}

	public function get($name) {
		$setting = $this->getRepo()->findOneBy(array(
			'name' => $name,
		));

		if ($setting === null) {
			throw new \RuntimeException(sprintf('Setting "%s" couldn\'t be found.', $name));
		}

		return $setting->getValue();
	}

	public function all() {
		$settings = array();
		foreach ($this->getRepo()->findAll() as $setting) {
			$settings[$setting->getName()] = $setting->getValue();
		}
		return $settings;
	}

	protected function getRepo() {
		if ($this->repo === null) {
			$this->repo = $this->em->getRepository(get_class(new Setting()));
		}
		return $this->repo;
	}

}
