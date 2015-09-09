<?php

namespace Craue\ConfigBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2015 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
abstract class AbstractModifySettingsForm extends AbstractType {

	/**
	 * {@inheritDoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$useFqcn = method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix');

		$builder->add('settings', $useFqcn ? 'Symfony\Component\Form\Extension\Core\Type\CollectionType' : 'collection', array(
			'type' => $useFqcn ? 'Craue\ConfigBundle\Form\Type\SettingType' : 'craue_config_setting',
		));
	}

}
