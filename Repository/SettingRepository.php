<?php

namespace Craue\ConfigBundle\Repository;

use Craue\ConfigBundle\Entity\Setting;
use Doctrine\ORM\EntityRepository;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2017 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class SettingRepository extends EntityRepository {

	/**
	 * @param string[] $names
	 * @return Setting[] Array of settings, indexed by name.
	 */
	public function findByNames(array $names) {
		return $this->getEntityManager()->createQueryBuilder()
			->select('s')
			->from('Craue\ConfigBundle\Entity\Setting', 's', 's.name')
			->where('s.name IN (:names)')
			->getQuery()
			->execute(array('names' => $names))
		;
	}

}
