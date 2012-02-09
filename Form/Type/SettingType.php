<?php

namespace Craue\ConfigBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2012 Christian Raue
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class SettingType extends AbstractType {

	/**
	 * {@inheritDoc}
	 */
	public function buildForm(FormBuilder $builder, array $options) {
		$builder->add('name', 'hidden');
		$builder->add('section', 'hidden');
		$builder->add('value', null, array(
			'required' => false,
		));
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName() {
		return 'craue_config_setting';
	}

}
