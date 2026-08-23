<?php

namespace Craue\ConfigBundle\Entity;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2026 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
interface SettingInterface {

	function setName(string $name) : void;
	function getName() : string;

	function setValue(?string $value) : void;
	function getValue() : ?string;

	function setSection(?string $section) : void;
	function getSection() : ?string;

}
