<?php

namespace Craue\ConfigBundle\Tests\Twig\Extension;

use Craue\ConfigBundle\Twig\Extension\ConfigTemplateExtension;
use Craue\ConfigBundle\Util\Config;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2022 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class ConfigTemplateExtensionTest extends TestCase {

	public function testGetSetting() {
		$ext = new ConfigTemplateExtension();
		$config = $this->createMock(Config::class);

		$config->expects($this->once())
			->method('get')
			->with('name')
		;

		$ext->setConfig($config);

		$ext->getSetting('name');
	}

	public function testSortSections() {
		$ext = new ConfigTemplateExtension();
		$ext->setSectionOrder(['section1', 'section2']);

		$this->assertEquals([null, 'section1', 'section2'], $ext->sortSections(['section2', null, 'section1']));
	}

	/**
	 * Ensure that setting a section order is optional.
	 */
	public function testSortSections_sectionOrderNotSet() {
		$ext = new ConfigTemplateExtension();

		$this->assertEquals([null, 'section2', 'section1'], $ext->sortSections(['section2', null, 'section1']));
	}

}
