<?php

namespace Craue\ConfigBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2015 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class ModifySettingsForm extends AbstractType {

	/**
	 * {@inheritDoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('settings', 'collection', array(
			'type' => 'craue_config_setting',
		));
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName() {
		return 'craue_config_modifySettings';
	}

}
