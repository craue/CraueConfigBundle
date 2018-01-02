<?php

namespace Craue\ConfigBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-present Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class CraueConfigBundle extends Bundle {

	/**
	 * {@inheritDoc}
	 */
	public function build(ContainerBuilder $container) {
		parent::build($container);
		$this->addRegisterMappingsPass($container);
	}

	/**
	 * @param ContainerBuilder $container
	 */
	private function addRegisterMappingsPass(ContainerBuilder $container) {
		$mappings = array(
			realpath(__DIR__ . '/Resources/config/doctrine-mapping') => 'Craue\ConfigBundle\Entity',
		);

		$container->addCompilerPass(DoctrineOrmMappingsPass::createXmlMappingDriver($mappings, array(), 'craue_config.db_driver.doctrine_orm'));
	}

}
