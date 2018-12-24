<?php

namespace Craue\ConfigBundle\Tests\IntegrationTestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2018 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class DebugController extends AbstractController {

	public function getAction($name) {
		return new JsonResponse(array(
			$name => $this->container->get('craue_config')->get($name),
		));
	}

}
