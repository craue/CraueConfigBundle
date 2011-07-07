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

	public function setEntityManager(EntityManager $em) {
		$this->em = $em;
	}

	public function get($name) {
		$repo = $this->em->getRepository(get_class(new Setting()));
		$setting = $repo->findOneBy(array(
			'name' => $name,
		));

		if ($setting === null) {
			throw new \RuntimeException(sprintf('Setting "%s" couldn\'t be found.', $name));
		}

		return $setting->getValue();
	}

}
