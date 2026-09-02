<?php declare(strict_types=1);

namespace Craue\ConfigBundle\Util;

use Craue\ConfigBundle\Entity\SettingInterface;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2026 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class SettingsUtil {

	/**
	 * @param SettingInterface[] $settings
	 * @return array<string, ?string> with name => value
	 */
	public static function getAsNamesAndValues(array $settings) : array {
		$result = [];

		foreach ($settings as $setting) {
			$result[$setting->getName()] = $setting->getValue();
		}

		return $result;
	}

	/**
	 * @param SettingInterface[] $settings
	 * @return array<string|null> (may also contain a null value)
	 */
	public static function getSections(array $settings) : array {
		$sections = [];

		foreach ($settings as $setting) {
			$section = $setting->getSection();
			if (!in_array($section, $sections, true)) {
				$sections[] = $section;
			}
		}

		sort($sections);

		return $sections;
	}

}
