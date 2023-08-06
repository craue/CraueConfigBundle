<?php

namespace Craue\ConfigBundle\Controller;

use Craue\ConfigBundle\CacheAdapter\CacheAdapterInterface;
use Craue\ConfigBundle\Entity\SettingInterface;
use Craue\ConfigBundle\Form\ModifySettingsForm;
use Doctrine\ORM\EntityManagerInterface;
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
 * @copyright 2011-2023 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class SettingsController extends AbstractController {

	public function modifyAction(CacheAdapterInterface $cache, FormFactoryInterface $formFactory, Request $request,
			SessionInterface $session, Environment $twig, EntityManagerInterface $em, TranslatorInterface $translator) {
		$repo = $em->getRepository($this->container->getParameter('craue_config.entity_name'));
		$allStoredSettings = $repo->findAll();

		$formData = [
			'settings' => $allStoredSettings,
		];

		$form = $formFactory->create(ModifySettingsForm::class, $formData);

		if ($request->getMethod() === 'POST') {
			$form->handleRequest($request);

			if ($form->isSubmitted() && $form->isValid()) {
				// update the cache
				foreach ($formData['settings'] as $formSetting) {
					$storedSetting = $this->getSettingByName($allStoredSettings, $formSetting->getName());
					if ($storedSetting !== null) {
						$cache->set($storedSetting->getName(), $storedSetting->getValue());
					}
				}

				$em->flush();

				if ($session instanceof Session) {
					$session->getFlashBag()->set('notice', $translator->trans('settings_changed', [], 'CraueConfigBundle'));
				}

				return $this->redirectToRoute($this->container->getParameter('craue_config.redirectRouteAfterModify'));
			}
		}

		return new Response($twig->render('@CraueConfig/Settings/modify.html.twig', [
			'form' => $form->createView(),
			'sections' => $this->getSections($allStoredSettings),
		]));
	}

	/**
	 * @param SettingInterface[] $settings
	 * @return string[] (may also contain a null value)
	 */
	protected function getSections(array $settings) {
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

	/**
	 * @param SettingInterface[] $settings
	 * @param string $name
	 * @return SettingInterface|null
	 */
	protected function getSettingByName(array $settings, $name) {
		foreach ($settings as $setting) {
			if ($setting->getName() === $name) {
				return $setting;
			}
		}

		return null;
	}

}
