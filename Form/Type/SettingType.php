<?php

namespace Craue\ConfigBundle\Form\Type;

use Craue\ConfigBundle\Entity\SettingInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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
	public function buildForm(FormBuilderInterface $builder, array $options) : void {
		$builder->add('value', null, [
			'required' => false,
			'translation_domain' => 'CraueConfigBundle',
		]);

		$builder->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) : void {
			$form = $event->getForm();
			$submittedData = $event->getData();

			// replace non-submitted values by defaults - this avoids nulling values of settings missing from the request
			// idea from https://stackoverflow.com/questions/11687760/form-avoid-setting-null-to-non-submitted-field/16522446#16522446
			foreach ($form->all() as $name => $child) {
				if (!isset($submittedData[$name])) {
					$submittedData[$name] = $child->getData();
				}
			}

			$event->setData($submittedData);
		});
	}

	/**
	 * {@inheritdoc}
	 */
	public function finishView(FormView $view, FormInterface $form, array $options) : void {
		/* @var $setting SettingInterface */
		$setting = $form->getData();

		$view->children['value']->vars['label'] = $setting->getName();
	}

	/**
	 * {@inheritDoc}
	 */
	public function configureOptions(OptionsResolver $resolver) : void {
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
