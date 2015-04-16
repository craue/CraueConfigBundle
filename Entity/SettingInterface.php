<?php

namespace Craue\ConfigBundle\Entity;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2017 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
interface SettingInterface {

	function setName($name);
	function getName();

	function setValue($value);
	function getValue();

	function setSection($section);
	function getSection();

}
