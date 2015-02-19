<?php

namespace Craue\ConfigBundle\Form;

use Craue\ConfigBundle\Form\Type\LegacySettingType;
use Craue\ConfigBundle\Form\Type\SettingType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpKernel\Kernel;

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
			'type' => Kernel::VERSION_ID < 20700 ? new LegacySettingType() : new SettingType(),
		));
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName() {
		return 'craue_config_modifySettings';
	}

}
