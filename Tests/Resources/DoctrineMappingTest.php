<?php

namespace Craue\ConfigBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

/**
 * @group unit
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2019 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class DoctrineMappingTest extends TestCase {

	public function testDuplicatedMappingFilesExist() {
		$this->assertNotEmpty($this->getDuplicatedMappingFiles(), 'No mapping files found. Check the path pointing to them.');
	}

	public function testDuplicatedMappingFilesAreInSync() {
		$mappingFiles = $this->getDuplicatedMappingFiles();

		for ($i = count($mappingFiles) - 1; $i > 0; --$i) {
			$fileA = realpath($mappingFiles[$i]);
			$fileB = realpath($mappingFiles[$i - 1]);
			$this->assertFileEquals($fileA, $fileB, sprintf('Files "%s" and "%s" are out of sync!', $fileA, $fileB));
		}
	}

	protected function getDuplicatedMappingFiles() {
		return glob(__DIR__ . '/../../Resources/config/doctrine-mapping*/BaseSetting.orm.xml');
	}

}
