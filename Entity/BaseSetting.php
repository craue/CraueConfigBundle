<?php declare(strict_types=1);

namespace Craue\ConfigBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2026 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
abstract class BaseSetting implements SettingInterface {

	/**
	 * @Assert\NotBlank
	 */
	protected string $name;

	protected ?string $value;

	protected ?string $section;

	public function setName(string $name) : void {
		$this->name = $name;
	}

	public function getName() : string {
		return $this->name;
	}

	public function setValue(?string $value) : void {
		$this->value = $value;
	}

	public function getValue() : ?string {
		return $this->value;
	}

	public function setSection(?string $section) : void {
		$this->section = $section;
	}

	public function getSection() : ?string {
		return $this->section;
	}

	/**
	 * Creates a {@code SettingInterface}.
	 */
	public static function create(string $name, ?string $value = null, ?string $section = null) : static {
		$setting = new static();
		$setting->setName($name);
		$setting->setValue($value);
		$setting->setSection($section);

		return $setting;
	}

}
