<?php

namespace Craue\ConfigBundle\Tests\IntegrationTestBundle\Entity;

use Craue\ConfigBundle\Entity\BaseSetting;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2023 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class CanBeDisabledSetting extends BaseSetting {

	/**
	 * @var bool
	 */
	private $disabled = false;

	public function setDisabled(bool $disabled) : void {
		$this->disabled = $disabled;
	}

	public function isDisabled() : bool {
		return $this->disabled;
	}

	/**
	 * Creates a {@code CanBeDisabledSetting}.
	 * @param string $name
	 * @param string|null $value
	 * @param string|null $section
	 * @param bool $disabled
	 * @return CanBeDisabledSetting
	 */
	public static function create($name, $value = null, $section = null, $disabled = false) {
		$setting = parent::create($name, $value, $section);
		$setting->setDisabled($disabled);

		return $setting;
	}

}
