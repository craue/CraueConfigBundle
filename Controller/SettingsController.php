<?php

namespace Craue\ConfigBundle\Controller;

use Craue\ConfigBundle\Entity\SettingInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-present Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class SettingsController extends Controller {

	public function modifyAction(Request $request) {
		$em = $this->getDoctrine()->getManager();
		$repo = $em->getRepository($this->container->getParameter('craue_config.entity_name'));
		$allStoredSettings = $repo->findAll();
		$cache = $this->get('craue_config_cache_adapter');

		$formData = array(
			'settings' => $allStoredSettings,
		);

		$useFqcn = method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix');
		$form = $this->createForm($useFqcn ? 'Craue\ConfigBundle\Form\ModifySettingsForm' : 'craue_config_modifySettings', $formData);

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
						$this->get('translator')->trans('settings_changed', array(), 'CraueConfigBundle'));
				return $this->redirect($this->generateUrl($this->container->getParameter('craue_config.redirectRouteAfterModify')));
			}
		}

		return $this->render('@CraueConfig/Settings/modify.html.twig', array(
			'form' => $form->createView(),
			'sections' => $this->getSections($allStoredSettings),
		));
	}

	/**
	 * @param SettingInterface[] $settings
	 * @return string[] (may also contain a null value)
	 */
	protected function getSections(array $settings) {
		$sections = array();

		foreach ($settings as $setting) {
			$section = $setting->getSection();
			if (!in_array($section, $sections)) {
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
