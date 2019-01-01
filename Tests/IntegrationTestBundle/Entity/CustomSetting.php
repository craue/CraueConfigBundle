<?php

namespace Craue\ConfigBundle\Tests\IntegrationTestBundle\Entity;

use Craue\ConfigBundle\Entity\BaseSetting;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2019 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class CustomSetting extends BaseSetting {

	/**
	 * @var string|null
	 */
	protected $comment;

	public function setComment($comment) {
		$this->comment = $comment;
	}

	public function getComment() {
		return $this->comment;
	}

	/**
	 * Creates a {@code CustomSetting}.
	 * @param string $name
	 * @param string|null $value
	 * @param string|null $section
	 * @param string|null $comment
	 * @return CustomSetting
	 */
	public static function create($name, $value = null, $section = null, $comment = null) {
		$setting = parent::create($name, $value, $section);
		$setting->setComment($comment);

		return $setting;
	}

}
