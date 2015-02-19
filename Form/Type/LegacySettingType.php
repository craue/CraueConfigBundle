<?php

namespace Craue\ConfigBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2015 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class LegacySettingType extends SettingType {

	/**
	 * {@inheritDoc}
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver) {
		parent::configureOptions($resolver);
	}

}
