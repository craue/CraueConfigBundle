<?php

namespace Craue\ConfigBundle\Tests\IntegrationTestBundle\Util;

use Craue\ConfigBundle\Tests\IntegrationTestBundle\Entity\CustomSetting;
use Craue\ConfigBundle\Util\Config;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2018 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class CustomConfig extends Config {

	/**
	 * @param string $name Name of the setting.
	 * @return CustomSetting The setting object.
	 * @throws \RuntimeException If the setting is not defined.
	 */
	public function getRawSetting($name) {
		$setting = $this->getRepo()->findOneBy([
			'name' => $name,
		]);

		if ($setting === null) {
			throw $this->createNotFoundException($name);
		}

		return $setting;
	}

}
