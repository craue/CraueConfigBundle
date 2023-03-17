<?php

namespace Craue\ConfigBundle\Form\Type;

use Craue\ConfigBundle\Entity\SettingInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2023 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class SettingType extends AbstractType {

	/**
	 * @var string
	 */
	protected $entityName;

	public function __construct($entityName) {
		$this->entityName = $entityName;
	}

	/**
	 * {@inheritDoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('value', null, [
			'required' => false,
			'translation_domain' => 'CraueConfigBundle',
		]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function finishView(FormView $view, FormInterface $form, array $options) {
		/* @var $setting SettingInterface */
		$setting = $form->getData();

		$view->children['value']->vars['label'] = $setting->getName();
	}

	/**
	 * {@inheritDoc}
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults([
			'data_class' => $this->entityName,
		]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getBlockPrefix() : string {
		return 'craue_config_setting';
	}

}
