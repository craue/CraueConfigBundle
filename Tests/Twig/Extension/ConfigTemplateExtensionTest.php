<?php

namespace Craue\ConfigBundle\Tests\Twig\Extension;

use Craue\ConfigBundle\Tests\IntegrationTestCase;

/**
 * @group unit
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2014 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class ConfigTemplateExtensionTest extends IntegrationTestCase {

	public function testGetSetting() {
		$this->persistSetting('name', 'value');

		$this->assertEquals('value', $this->getConfigTemplateExtension()->getSetting('name'));
	}

	/**
	 * @expectedException \RuntimeException
	 * @expectedExceptionMessage Setting "oh-no" couldn't be found.
	 */
	public function testGetSetting_nonexistentSetting() {
		$this->getConfigTemplateExtension()->getSetting('oh-no');
	}

	public function testSortSections() {
		$ext = new ConfigTemplateExtension();
		$ext->setSectionOrder(array('section1', 'section2'));
		$this->assertEquals(array(null, 'section1', 'section2'), $ext->sortSections(array('section2', null, 'section1')));
	}

}
