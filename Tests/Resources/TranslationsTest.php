<?php declare(strict_types=1);

namespace Craue\ConfigBundle\Tests\Resources;

use Craue\TranslationsTests\YamlTranslationsTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2026 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
#[Group('unit')]
class TranslationsTest extends YamlTranslationsTest {

	/**
	 * @return string[]
	 */
	protected function defineTranslationFiles() : array {
		return glob(__DIR__ . '/../../Resources/translations/*.yml');
	}

}
