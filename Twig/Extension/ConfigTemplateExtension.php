<?php

namespace Craue\ConfigBundle\Twig\Extension;

use Craue\ConfigBundle\Util\Config;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2016 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class ConfigTemplateExtension extends \Twig_Extension {

	/**
	 * @var string[]
	 */
	protected $sectionOrder = array();

	/**
	 * @var Config
	 */
	protected $config;

	/**
	 * @param string[] $sectionOrder The order in which sections will be rendered.
	 */
	public function setSectionOrder(array $sectionOrder = array()) {
		$this->sectionOrder = $sectionOrder;
	}

	/**
	 * @param Config $config
	 */
	public function setConfig(Config $config) {
		$this->config = $config;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName() {
		return 'craue_config_template';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getFilters() {
		if (version_compare(\Twig_Environment::VERSION, '1.12', '<')) {
			return array(
				'craue_sortSections' => new \Twig_Filter_Method($this, 'sortSections'),
			);
		}

		return array(
			new \Twig_SimpleFilter('craue_sortSections', array($this, 'sortSections')),
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getFunctions() {
		if (version_compare(\Twig_Environment::VERSION, '1.12', '<')) {
			return array(
				'craue_setting' => new \Twig_Function_Method($this, 'getSetting'),
			);
		}

		return array(
			new \Twig_SimpleFunction('craue_setting', array($this, 'getSetting')),
		);
	}

	/**
	 * @param string[] $sections
	 * @return string[]
	 */
	public function sortSections(array $sections) {
		$finalSectionOrder = array();

		// add null section first (if it exists)
		$nullIndex = array_search(null, $sections);
		if ($nullIndex !== false) {
			$finalSectionOrder[] = $sections[$nullIndex];
			unset($sections[$nullIndex]);
		}

		// add sections in given order
		foreach (array_intersect($this->sectionOrder, $sections) as $section) {
			$finalSectionOrder[] = $section;
		}

		// add remaining sections
		foreach (array_diff($sections, $this->sectionOrder) as $section) {
			$finalSectionOrder[] = $section;
		}

		return $finalSectionOrder;
	}

	/**
	 * @param string $name Name of the setting.
	 * @return string|null Value of the setting.
	 * @throws \RuntimeException If the setting is not defined.
	 */
	public function getSetting($name) {
		return $this->config->get($name);
	}

}
