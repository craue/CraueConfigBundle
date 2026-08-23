<?php

namespace Craue\ConfigBundle\Tests\IntegrationTestBundle\Entity;

use Craue\ConfigBundle\Entity\BaseSetting;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2026 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class CustomSetting extends BaseSetting {

	/**
	 * @var string|null
	 */
	protected $comment;

	public function setComment(?string $comment) : void {
		$this->comment = $comment;
	}

	public function getComment() : ?string {
		return $this->comment;
	}

	/**
	 * Creates a {@code CustomSetting}.
	 */
	public static function create(string $name, ?string $value = null, ?string $section = null, ?string $comment = null) : static {
		$setting = parent::create($name, $value, $section);
		$setting->setComment($comment);

		return $setting;
	}

}
