<?php declare(strict_types=1);

namespace Craue\ConfigBundle\Controller;

use Craue\ConfigBundle\Entity\SettingInterface;
use Craue\ConfigBundle\Form\ModifySettingsForm;
use Craue\ConfigBundle\Util\SettingsUtil;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2026 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class SettingsController extends AbstractController {

	public function modifyAction(CacheItemPoolInterface $cache, FormFactoryInterface $formFactory, Request $request,
			SessionInterface $session, Environment $twig, EntityManagerInterface $em, TranslatorInterface $translator) : Response {
		/** @var class-string<SettingInterface> $entityName */
		$entityName = $this->container->getParameter('craue_config.entity_name');
		$repo = $em->getRepository($entityName);
		$allStoredSettings = $repo->findAll();

		$formData = [
			'settings' => $allStoredSettings,
		];

		$form = $formFactory->create(ModifySettingsForm::class, $formData);

		if ($request->getMethod() === 'POST') {
			$form->handleRequest($request);

			if ($form->isSubmitted() && $form->isValid()) {
				$em->flush();

				// update the cache
				foreach (SettingsUtil::getAsNamesAndValues($allStoredSettings) as $name => $value) {
					$cacheItem = $cache->getItem($name);
					$cacheItem->set($value);
					$cache->saveDeferred($cacheItem);
				}
				$cache->commit();

				if ($session instanceof Session) {
					$session->getFlashBag()->set('notice', $translator->trans('settings_changed', [], 'CraueConfigBundle'));
				}

				return $this->redirectToRoute($this->container->getParameter('craue_config.redirectRouteAfterModify'));
			}
		}

		return new Response($twig->render('@CraueConfig/Settings/modify.html.twig', [
			'form' => $form->createView(),
			'sections' => SettingsUtil::getSections($allStoredSettings),
		]));
	}

}
