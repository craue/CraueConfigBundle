<?php

namespace Craue\ConfigBundle\Tests\IntegrationTestBundle\Form\Extension;

use Craue\ConfigBundle\Entity\SettingInterface;
use Craue\ConfigBundle\Form\Type\SettingType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2023 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class SettingTypeExtension extends AbstractTypeExtension {

	/**
	 * {@inheritDoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) : void {
		/* @var $formData SettingInterface */
		$formData = $options['data'];

		$config = $builder->get('value')->getForm()->getConfig();

		$builder->add($config->getName(), get_class($config->getType()->getInnerType()), array_merge($config->getOptions(), [
			'disabled' => $formData->isDisabled(),
		]));
	}

	public static function getExtendedTypes() : iterable {
		return [SettingType::class];
	}

}
