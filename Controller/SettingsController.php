<?php

namespace Craue\ConfigBundle\Controller;

use Craue\ConfigBundle\Entity\SettingInterface;
use Craue\ConfigBundle\Form\ModifySettingsForm;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2019 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class SettingsController extends AbstractController {

	public function modifyAction(Request $request) {
		$em = $this->getDoctrine()->getManager();
		$repo = $em->getRepository($this->container->getParameter('craue_config.entity_name'));
		$allStoredSettings = $repo->findAll();
		$cache = $this->get('craue_config_cache_adapter');

		$formData = [
			'settings' => $allStoredSettings,
		];

		$form = $this->createForm(ModifySettingsForm::class, $formData);

		if ($request->getMethod() === 'POST') {
			$form->handleRequest($request);

			if ($form->isSubmitted() && $form->isValid()) {
				foreach ($formData['settings'] as $formSetting) {
					$storedSetting = $this->getSettingByName($allStoredSettings, $formSetting->getName());
					if ($storedSetting !== null) {
						$storedSetting->setValue($formSetting->getValue());
						$cache->set($storedSetting->getName(), $storedSetting->getValue());
					}
				}

				$em->flush();

				$this->get('session')->getFlashBag()->set('notice',
						$this->get('translator')->trans('settings_changed', [], 'CraueConfigBundle'));
				return $this->redirect($this->generateUrl($this->container->getParameter('craue_config.redirectRouteAfterModify')));
			}
		}

		return $this->render('@CraueConfig/Settings/modify.html.twig', [
			'form' => $form->createView(),
			'sections' => $this->getSections($allStoredSettings),
		]);
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
