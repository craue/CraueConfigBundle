<?php

namespace Craue\ConfigBundle\Form\Type;

use Craue\ConfigBundle\Entity\Setting;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2014 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class SettingType extends AbstractType {

	/**
	 * {@inheritDoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('name', 'hidden');
		$builder->add('section', 'hidden');
		$builder->add('value', null, array(
			'required' => false,
		));
	}

	/**
	 * {@inheritDoc}
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		$resolver->setDefaults(array(
			'data_class' => get_class(new Setting()),
		));
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName() {
		return 'craue_config_setting';
	}

}
