<?php

namespace Craue\ConfigBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpKernel\Kernel;

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
		$builder->add('settings', Kernel::VERSION_ID < 20800 ? 'collection' : 'Symfony\Component\Form\Extension\Core\Type\CollectionType', array(
			'type' => Kernel::VERSION_ID < 20800 ? 'craue_config_setting' : 'Craue\ConfigBundle\Form\Type\SettingType',
		));
	}

}
