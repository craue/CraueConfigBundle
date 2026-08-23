<?php

namespace Craue\ConfigBundle\Tests\IntegrationTestBundle\Entity;

use Craue\ConfigBundle\Entity\BaseSetting;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2026 Christian Raue
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
	 */
	public static function create(string $name, ?string $value = null, ?string $section = null, bool $disabled = false) : static {
		$setting = parent::create($name, $value, $section);
		$setting->setDisabled($disabled);

		return $setting;
	}

}
