<?php

namespace Craue\ConfigBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2015 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
abstract class AbstractSettingType extends AbstractType {

	/**
	 * {@inheritDoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('name', Kernel::VERSION_ID < 20800 ? 'hidden' : 'Symfony\Component\Form\Extension\Core\Type\HiddenType');
		$builder->add('section', Kernel::VERSION_ID < 20800 ? 'hidden' : 'Symfony\Component\Form\Extension\Core\Type\HiddenType');
		$builder->add('value', Kernel::VERSION_ID < 20800 ? 'text' : 'Symfony\Component\Form\Extension\Core\Type\TextType', array(
			'required' => false,
		));
	}

	/**
	 * {@inheritDoc}
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'Craue\ConfigBundle\Entity\Setting',
		));
	}

}
