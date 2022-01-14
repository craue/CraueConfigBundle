<?php

namespace Craue\ConfigBundle\Tests\Resources;

use Craue\TranslationsTests\YamlTranslationsTest;

/**
 * @group unit
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2022 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class TranslationsTest extends YamlTranslationsTest {

	protected function defineTranslationFiles() {
		return glob(__DIR__ . '/../../Resources/translations/*.yml');
	}

}
