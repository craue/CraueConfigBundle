<?php

namespace Craue\ConfigBundle\Form;

use Craue\ConfigBundle\Entity\SettingInterface;
use Craue\ConfigBundle\Form\Type\SettingType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2023 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class ModifySettingsForm extends AbstractType {

	/**
	 * {@inheritDoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) : void {
		$settingsForm = $builder->create('settings', FormType::class);

		foreach ($options['data']['settings'] as $setting) {
			/* @var $setting SettingInterface */
			$settingsForm->add($setting->getName(), SettingType::class, [
				'data' => $setting,
			]);
		}

		$builder->add($settingsForm);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getBlockPrefix() : string {
		return 'craue_config_modifySettings';
	}

}
