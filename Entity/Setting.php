<?php

namespace Craue\ConfigBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2017 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 *
 * @ORM\Entity(repositoryClass="Craue\ConfigBundle\Repository\SettingRepository")
 * @ORM\Table(name="craue_config_setting")
 */
class Setting {

	/**
	 * @var string
	 * @ORM\Id
	 * @ORM\Column(name="name", type="string", nullable=false, unique=true)
	 * @Assert\NotBlank
	 */
	protected $name;

	/**
	 * @var string|null
	 * @ORM\Column(name="value", type="string", nullable=true)
	 */
	protected $value;

	/**
	 * @var string|null
	 * @ORM\Column(name="section", type="string", nullable=true)
	 */
	protected $section;

	public function setName($name) {
		$this->name = $name;
	}

	public function getName() {
		return $this->name;
	}

	public function setValue($value) {
		$this->value = $value;
	}

	public function getValue() {
		return $this->value;
	}

	public function setSection($section) {
		$this->section = $section;
	}

	public function getSection() {
		return $this->section;
	}

	/**
	 * Creates a {@code Setting}.
	 * @param string $name
	 * @param string|null $value
	 * @param string|null $section
	 * @return Setting
	 */
	public static function create($name, $value = null, $section = null) {
		$setting = new static();
		$setting->setName($name);
		$setting->setValue($value);
		$setting->setSection($section);

		return $setting;
	}

}
