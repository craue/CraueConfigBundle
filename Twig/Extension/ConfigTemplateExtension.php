<?php

namespace Craue\ConfigBundle\Twig\Extension;

use Craue\ConfigBundle\Util\Config;
/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2014 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class ConfigTemplateExtension extends \Twig_Extension {

	/**
	 * @var string[]
	 */
	protected $sectionOrder;
	
	/**
	 * @var Config $config
	 */
	protected $config;

	/**
	 * Sets the order in which sections will be rendered.
	 * @param string[] $sectionOrder
	 */
	public function setSectionOrder(array $sectionOrder = array()) {
		$this->sectionOrder = $sectionOrder;
	}

	/**
	 * Setter for Config class
	 * @param Config $config
	 */
	public function setConfig($config) {
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
		return array(
			'craue_sortSections' => new \Twig_Filter_Method($this, 'sortSections'),
		);
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function getFunctions() {
		return array(
			'craue_setting' => new \Twig_Function_Function(array($this, 'getSetting')),
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
	 * Get the corresponding value belonging with the key, or throw RuntimeException when there is no value.
	 * 
	 * @param unknown $name
	 * @return Ambigous <string, NULL>
	 * @throws \RuntimeException
	 */
	public function getSetting($name) {
		return $this->config->get($name);
	}

}
