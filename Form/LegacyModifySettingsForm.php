<?php

namespace Craue\ConfigBundle\Form;

/**
 * for Symfony < 2.8
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2015 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class LegacyModifySettingsForm extends AbstractModifySettingsForm {

	/**
	 * {@inheritDoc}
	 */
	public function getName() {
		return 'craue_config_modifySettings';
	}

}
