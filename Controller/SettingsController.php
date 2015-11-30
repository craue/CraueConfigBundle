<?php

namespace Craue\ConfigBundle\Controller;

use Craue\ConfigBundle\Entity\Setting;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2015 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class SettingsController extends Controller {

	public function modifyAction(Request $request) {
		$em = $this->getDoctrine()->getManager();
		$repo = $em->getRepository('Craue\ConfigBundle\Entity\Setting');
		$allStoredSettings = $repo->findAll();

		$formData = array(
			'settings' => $allStoredSettings,
		);

		$useFqcn = method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix');
		$form = $this->createForm($useFqcn ? 'Craue\ConfigBundle\Form\ModifySettingsForm' : 'craue_config_modifySettings', $formData);

		if ($request->getMethod() === 'POST') {
			if (method_exists($form, 'handleRequest')) {
				$form->handleRequest($request);
			} else {
				$form->bind($request); // for symfony/form < 2.3
			}

			if ($form->isValid()) {
				foreach ($formData['settings'] as $formSetting) {
					$storedSetting = $this->getSettingByName($allStoredSettings, $formSetting->getName());
					if ($storedSetting !== null) {
						$storedSetting->setValue($formSetting->getValue());
						$em->persist($storedSetting);
					}
				}

				$em->flush();

				$this->get('session')->getFlashBag()->set('notice',
						$this->get('translator')->trans('settings_changed', array(), 'CraueConfigBundle'));
				return $this->redirect($this->generateUrl($this->container->getParameter('craue_config.redirectRouteAfterModify')));
			}
		}

		return $this->render('CraueConfigBundle:Settings:modify.html.twig', array(
			'form' => $form->createView(),
			'sections' => $this->getSections($allStoredSettings),
			'symfonyPriorTo2dot3' => !method_exists($form, 'handleRequest'),
		));
	}

	/**
	 * @param Setting[] $settings
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
	 * @param Setting[] $settings
	 * @param string $name
	 * @return Setting|null
	 */
	protected function getSettingByName(array $settings, $name) {
		foreach ($settings as $setting) {
			if ($setting->getName() === $name) {
				return $setting;
			}
		}
	}

}
