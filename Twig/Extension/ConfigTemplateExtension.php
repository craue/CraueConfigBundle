<?php

namespace Craue\ConfigBundle\Twig\Extension;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2013 Christian Raue
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class ConfigTemplateExtension extends \Twig_Extension {

	/**
	 * @var string[]
	 */
	protected $sectionOrder;

	/**
	 * Sets the order in which sections will be rendered.
	 * @param string[] $sectionOrder
	 */
	public function setSectionOrder(array $sectionOrder = array()) {
		$this->sectionOrder = $sectionOrder;
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

}
