<?php

namespace Craue\ConfigBundle\Repository;

use Craue\ConfigBundle\Entity\SettingInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @extends EntityRepository<SettingInterface>
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2026 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class SettingRepository extends EntityRepository {

	/**
	 * @param string[] $names
	 * @return array<string, SettingInterface> Array of settings, indexed by name.
	 */
	public function findByNames(array $names) : array {
		return $this->createQueryBuilder('s', 's.name')
			->where('s.name IN (:names)')
			->getQuery()
			->execute(['names' => $names])
		;
	}

}
