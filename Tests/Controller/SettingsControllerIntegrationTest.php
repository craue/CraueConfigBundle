<?php

namespace Craue\ConfigBundle\Tests\Controller;

use Craue\ConfigBundle\Tests\IntegrationTestCase;

/**
 * @group integration
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2017 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class SettingsControllerIntegrationTest extends IntegrationTestCase {

	public function testModifyAction_noSettings() {
		$client = $this->initClient();

		$client->request('GET', $this->url($client, 'craue_config_settings_modify'));
		$content = $client->getResponse()->getContent();
		$this->assertSame(200, $client->getResponse()->getStatusCode(), $content);
		$this->assertContains('<div class="craue_config_settings_modify">', $content);
		$this->assertContains('There are no settings defined yet.', $content);
	}

	public function testModifyAction_noChanges() {
		$client = $this->initClient();
		$this->persistSetting('name', 'value');

		$crawler = $client->request('GET', $this->url($client, 'craue_config_settings_modify'));
		$content = $client->getResponse()->getContent();
		$this->assertSame(200, $client->getResponse()->getStatusCode(), $content);
		$this->assertRegExp('/<form .*method="post" .*class="craue_config_settings_modify".*>/', $content);
		$this->assertContains('<label for="craue_config_modifySettings_settings_0_value">name</label>', $content);
		$this->assertContains('<input type="text" id="craue_config_modifySettings_settings_0_value" name="craue_config_modifySettings[settings][0][value]" value="value" />', $content);
		$this->assertContains('<button type="submit">apply</button>', $content);

		$form = $crawler->selectButton('apply')->form();
		$client->followRedirects();
		$client->submit($form);
		$content = $client->getResponse()->getContent();
		$this->assertContains('<div class="notice">The settings were changed.</div>', $content);

		$settings = $this->getSettingsRepo()->findAll();
		$this->assertCount(1, $settings);

		$setting = $settings[0];
		$this->assertSame('name', $setting->getName());
		$this->assertSame('value', $setting->getValue());
		$this->assertNull($setting->getSection());
	}

	/**
	 * Ensure that only the value can be changed, but neither name nor section.
	 */
	public function testModifyAction_changeValue() {
		$client = $this->initClient();
		$this->persistSetting('name', 'value', 'section');

		$crawler = $client->request('GET', $this->url($client, 'craue_config_settings_modify'));
		$form = $crawler->selectButton('apply')->form();
		$this->assertFalse($form->has('craue_config_modifySettings[settings][0][name]'));
		$this->assertFalse($form->has('craue_config_modifySettings[settings][0][section]'));
		$client->followRedirects();
		$client->submit($form, array(
			'craue_config_modifySettings[settings][0][value]' => 'new-value',
		));
		$content = $client->getResponse()->getContent();
		$this->assertContains('<div class="notice">The settings were changed.</div>', $content);

		$settings = $this->getSettingsRepo()->findAll();
		$this->assertCount(1, $settings);

		$setting = $settings[0];
		$this->assertSame('name', $setting->getName());
		$this->assertSame('new-value', $setting->getValue());
		$this->assertSame('section', $setting->getSection());
	}

	/**
	 * Ensure that the configured cache is actually used and that changing settings loads all settings (with updated values) into the cache.
	 *
	 * @dataProvider dataModifyAction_changeValue_cacheUsage
	 */
	public function testModifyAction_changeValue_cacheUsage($environment, $config) {
		$client = $this->initClient(array('environment' => $environment, 'config' => $config));
		$this->persistSetting('name1', 'value1');
		$this->persistSetting('name2', 'value2');

		$cache = $client->getContainer()->get('craue_config_cache_adapter');
		$cache->clear();
		$this->assertFalse($cache->has('name1'));
		$this->assertFalse($cache->has('name2'));

		$crawler = $client->request('GET', $this->url($client, 'craue_config_settings_modify'));
		$form = $crawler->selectButton('apply')->form();
		$client->followRedirects();
		$client->submit($form, array(
			'craue_config_modifySettings[settings][0][value]' => 'new-value1',
		));

		$this->assertTrue($cache->has('name1'));
		$this->assertTrue($cache->has('name2'));
		$this->assertSame('new-value1', $cache->get('name1'));
		$this->assertSame('value2', $cache->get('name2'));
	}

	public function dataModifyAction_changeValue_cacheUsage() {
		$testData = array(
			array('cache_DoctrineCacheBundle_file_system', 'config_cache_DoctrineCacheBundle_file_system.yml'),
		);

		// TODO remove check as soon as Symfony >= 3.1 is required
		if (class_exists('\Symfony\Component\Cache\Adapter\ArrayAdapter')) {
			$testData[] = array('cache_SymfonyCacheComponent_filesystem', 'config_cache_SymfonyCacheComponent_filesystem.yml');
		}

		return $testData;
	}

	/**
	 * Ensure that an invalid form submission is handled properly.
	 */
	public function testModifyAction_formInvalid() {
		$client = $this->initClient();
		$this->persistSetting('name', 'value');

		$crawler = $client->request('GET', $this->url($client, 'craue_config_settings_modify'));
		$form = $crawler->selectButton('apply')->form();
		$form->remove('craue_config_modifySettings[settings][0][value]');
		if ($form->has('craue_config_modifySettings[_token]')) {
			$form->remove('craue_config_modifySettings[_token]'); // field only present in older Symfony versions, removal needed to make form submission invalid
		}
		$client->followRedirects();
		$client->submit($form);
		$content = $client->getResponse()->getContent();
		$this->assertNotContains('<div class="notice">The settings were changed.</div>', $content);
	}

	/**
	 * Ensure that dynamic values (sections, names) are properly translated (exactly once).
	 */
	public function testModifyAction_properTranslations() {
		$client = $this->initClient();
		$this->persistSetting('setting-number-one', 'value', 'section-number-one');

		$client->enableProfiler();
		$client->request('GET', $this->url($client, 'craue_config_settings_modify'));
		$content = $client->getResponse()->getContent();
		$this->assertContains('<legend>section no. 1</legend>', $content);
		$this->assertContains('<label for="craue_config_modifySettings_settings_0_value">setting no. 1</label>', $content);

		$profile = $client->getProfile();
		if ($profile->hasCollector('translation')) { // TODO remove as soon as Symfony >= 2.7 is required
			$this->assertSame(0, $profile->getCollector('translation')->getCountMissings());
		}
	}

	public function testModifyAction_sectionOrder_defaultOrder() {
		$client = $this->initClient();
		$this->persistSetting('name1', 'value1', 'section1');
		$this->persistSetting('name2', 'value2');
		$this->persistSetting('name3', 'value3', 'section2');

		$client->request('GET', $this->url($client, 'craue_config_settings_modify'));
		$content = $client->getResponse()->getContent();
		$this->assertContains('<legend>section1</legend>', $content);
		$this->assertContains('<legend>section2</legend>', $content);
		$strPosField1 = strpos($content, '<label for="craue_config_modifySettings_settings_0_value">name1</label>');
		$strPosField2 = strpos($content, '<label for="craue_config_modifySettings_settings_1_value">name2</label>');
		$strPosField3 = strpos($content, '<label for="craue_config_modifySettings_settings_2_value">name3</label>');
		$this->assertTrue($strPosField2 < $strPosField1 && $strPosField1 < $strPosField3, 'The sections are rendered in wrong order.');
	}

	public function testModifyAction_sectionOrder_customOrder() {
		$client = $this->initClient(array('environment' => 'customSectionOrder', 'config' => 'config_customSectionOrder.yml'));
		$this->persistSetting('name1', 'value1', 'section1');
		$this->persistSetting('name2', 'value2');
		$this->persistSetting('name3', 'value3', 'section2');

		$client->request('GET', $this->url($client, 'craue_config_settings_modify'));
		$content = $client->getResponse()->getContent();
		$this->assertContains('<legend>section1</legend>', $content);
		$this->assertContains('<legend>section2</legend>', $content);
		$strPosField1 = strpos($content, '<label for="craue_config_modifySettings_settings_0_value">name1</label>');
		$strPosField2 = strpos($content, '<label for="craue_config_modifySettings_settings_1_value">name2</label>');
		$strPosField3 = strpos($content, '<label for="craue_config_modifySettings_settings_2_value">name3</label>');
		$this->assertTrue($strPosField2 < $strPosField3 && $strPosField3 < $strPosField1, 'The sections are rendered in wrong order.');
	}

	public function testModifyAction_redirectRouteAfterModify() {
		$client = $this->initClient(array('environment' => 'redirectRouteAfterModify', 'config' => 'config_redirectRouteAfterModify.yml'));
		$this->persistSetting('name', 'value');

		$this->assertSame('admin_settings_start', $client->getContainer()->getParameter('craue_config.redirectRouteAfterModify'));

		$crawler = $client->request('GET', $this->url($client, 'craue_config_settings_modify'));
		$form = $crawler->selectButton('apply')->form();
		$client->submit($form);
		$this->assertRedirect($client, $this->url($client, 'admin_settings_start'));
	}

}
