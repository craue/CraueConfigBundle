<?php

namespace Craue\ConfigBundle\Tests\Controller;

use Craue\ConfigBundle\Entity\Setting;
use Craue\ConfigBundle\Tests\IntegrationTestBundle\Entity\CustomSetting;
use Craue\ConfigBundle\Tests\IntegrationTestCase;

/**
 * @group integration
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-present Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class SettingsControllerIntegrationTest extends IntegrationTestCase {

	/**
	 * @dataProvider getPlatformConfigs
	 */
	public function testModifyAction_noSettings($platform, $config, $requiredExtension) {
		$client = $this->initClient($requiredExtension, array('environment' => $platform, 'config' => $config));

		$client->request('GET', $this->url($client, 'craue_config_settings_modify'));
		$content = $client->getResponse()->getContent();
		$this->assertSame(200, $client->getResponse()->getStatusCode(), $content);
		$this->assertContains('<div class="craue_config_settings_modify">', $content);
		$this->assertContains('There are no settings defined yet.', $content);
	}

	/**
	 * @dataProvider getPlatformConfigs
	 */
	public function testModifyAction_noChanges($platform, $config, $requiredExtension) {
		$client = $this->initClient($requiredExtension, array('environment' => $platform, 'config' => $config));
		$this->persistSetting(Setting::create('name', 'value'));

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
	 *
	 * @dataProvider getPlatformConfigs
	 */
	public function testModifyAction_changeValue($platform, $config, $requiredExtension) {
		$client = $this->initClient($requiredExtension, array('environment' => $platform, 'config' => $config));
		$this->persistSetting(Setting::create('name', 'value', 'section'));

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
	public function testModifyAction_changeValue_cacheUsage($platform, $config, $requiredExtension, $environment) {
		$client = $this->initClient($requiredExtension, array('environment' => $environment . '_' . $platform, 'config' => $config));
		$this->persistSetting(Setting::create('name1', 'value1'));
		$this->persistSetting(Setting::create('name2', 'value2'));

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
		$testData = self::duplicateTestDataForEachPlatform(array(
			array('cache_DoctrineCacheBundle_file_system'),
		), 'config_cache_DoctrineCacheBundle_file_system.yml');

		// TODO remove check as soon as Symfony >= 3.1 is required
		if (class_exists('\Symfony\Component\Cache\Adapter\ArrayAdapter')) {
			$testData = array_merge($testData,
				self::duplicateTestDataForEachPlatform(array(
					array('cache_SymfonyCacheComponent_filesystem'),
				), 'config_cache_SymfonyCacheComponent_filesystem.yml'));
		}

		return $testData;
	}

	/**
	 * Ensure that an invalid form submission is handled properly.
	 *
	 * @dataProvider getPlatformConfigs
	 */
	public function testModifyAction_formInvalid($platform, $config, $requiredExtension) {
		$client = $this->initClient($requiredExtension, array('environment' => $platform, 'config' => $config));
		$this->persistSetting(Setting::create('name', 'value'));

		$crawler = $client->request('GET', $this->url($client, 'craue_config_settings_modify'));
		$form = $crawler->selectButton('apply')->form();
		$form->remove('craue_config_modifySettings[settings][0][value]');
		$client->followRedirects();
		$client->submit($form);
		$content = $client->getResponse()->getContent();
		$this->assertNotContains('<div class="notice">The settings were changed.</div>', $content);
	}

	/**
	 * Ensure that dynamic values (sections, names) are properly translated (exactly once).
	 *
	 * @dataProvider getPlatformConfigs
	 */
	public function testModifyAction_properTranslations($platform, $config, $requiredExtension) {
		$client = $this->initClient($requiredExtension, array('environment' => $platform, 'config' => $config));
		$this->persistSetting(Setting::create('setting-number-one', 'value', 'section-number-one'));

		$client->enableProfiler();
		$client->request('GET', $this->url($client, 'craue_config_settings_modify'));
		$content = $client->getResponse()->getContent();
		$this->assertContains('<legend>section no. 1</legend>', $content);
		$this->assertContains('<label for="craue_config_modifySettings_settings_0_value">setting no. 1</label>', $content);

		$profile = $client->getProfile();
		$this->assertSame(0, $profile->getCollector('translation')->getCountMissings());
	}

	/**
	 * @dataProvider getPlatformConfigs
	 */
	public function testModifyAction_sectionOrder_defaultOrder($platform, $config, $requiredExtension) {
		$client = $this->initClient($requiredExtension, array('environment' => $platform, 'config' => $config));
		$this->persistSetting(Setting::create('name1', 'value1', 'section1'));
		$this->persistSetting(Setting::create('name2', 'value2'));
		$this->persistSetting(Setting::create('name3', 'value3', 'section2'));

		$client->request('GET', $this->url($client, 'craue_config_settings_modify'));
		$content = $client->getResponse()->getContent();
		$this->assertContains('<legend>section1</legend>', $content);
		$this->assertContains('<legend>section2</legend>', $content);
		$strPosField1 = strpos($content, '<label for="craue_config_modifySettings_settings_0_value">name1</label>');
		$strPosField2 = strpos($content, '<label for="craue_config_modifySettings_settings_1_value">name2</label>');
		$strPosField3 = strpos($content, '<label for="craue_config_modifySettings_settings_2_value">name3</label>');
		$this->assertTrue($strPosField2 < $strPosField1 && $strPosField1 < $strPosField3, 'The sections are rendered in wrong order.');
	}

	/**
	 * @dataProvider dataModifyAction_sectionOrder_customOrder
	 */
	public function testModifyAction_sectionOrder_customOrder($platform, $config, $requiredExtension) {
		$client = $this->initClient($requiredExtension, array('environment' => 'customSectionOrder_' . $platform, 'config' => $config));
		$this->persistSetting(Setting::create('name1', 'value1', 'section1'));
		$this->persistSetting(Setting::create('name2', 'value2'));
		$this->persistSetting(Setting::create('name3', 'value3', 'section2'));

		$client->request('GET', $this->url($client, 'craue_config_settings_modify'));
		$content = $client->getResponse()->getContent();
		$this->assertContains('<legend>section1</legend>', $content);
		$this->assertContains('<legend>section2</legend>', $content);
		$strPosField1 = strpos($content, '<label for="craue_config_modifySettings_settings_0_value">name1</label>');
		$strPosField2 = strpos($content, '<label for="craue_config_modifySettings_settings_1_value">name2</label>');
		$strPosField3 = strpos($content, '<label for="craue_config_modifySettings_settings_2_value">name3</label>');
		$this->assertTrue($strPosField2 < $strPosField3 && $strPosField3 < $strPosField1, 'The sections are rendered in wrong order.');
	}

	public function dataModifyAction_sectionOrder_customOrder() {
		return self::duplicateTestDataForEachPlatform(array(
			array(),
		), 'config_customSectionOrder.yml');
	}

	/**
	 * @dataProvider dataModifyAction_redirectRouteAfterModify
	 */
	public function testModifyAction_redirectRouteAfterModify($platform, $config, $requiredExtension) {
		$client = $this->initClient($requiredExtension, array('environment' => 'redirectRouteAfterModify_' . $platform, 'config' => $config));
		$this->persistSetting(Setting::create('name', 'value'));

		$this->assertSame('admin_settings_start', $client->getContainer()->getParameter('craue_config.redirectRouteAfterModify'));

		$crawler = $client->request('GET', $this->url($client, 'craue_config_settings_modify'));
		$form = $crawler->selectButton('apply')->form();
		$client->submit($form);
		$this->assertRedirect($client, $this->url($client, 'admin_settings_start'));
	}

	public function dataModifyAction_redirectRouteAfterModify() {
		return self::duplicateTestDataForEachPlatform(array(
			array(),
		), 'config_redirectRouteAfterModify.yml');
	}

	/**
	 * Ensure that a custom model class can actually be used.
	 *
	 * @dataProvider dataModifyAction_customEntity
	 */
	public function testModifyAction_customEntity($platform, $config, $requiredExtension, $environment) {
		$client = $this->initClient($requiredExtension, array('environment' => $environment . '_' . $platform, 'config' => $config));
		$this->persistSetting(CustomSetting::create('name', 'value', 'section', 'comment'));
		$newValue = str_repeat('X', 200) . "\n" . str_repeat('Y', 99);

		$crawler = $client->request('GET', $this->url($client, 'craue_config_settings_modify'));
		$content = $client->getResponse()->getContent();
		$this->assertContains('<textarea id="craue_config_modifySettings_settings_0_value" name="craue_config_modifySettings[settings][0][value]">value</textarea>', $content);

		$form = $crawler->selectButton('apply')->form();
		$client->followRedirects();
		$client->submit($form, array(
			'craue_config_modifySettings[settings][0][value]' => $newValue,
		));

		$settings = $this->getSettingsRepo()->findAll();
		$this->assertCount(1, $settings);

		$setting = $settings[0];
		$this->assertSame('name', $setting->getName());
		$this->assertSame(strlen($newValue), strlen($setting->getValue()));
		$this->assertSame($newValue, $setting->getValue());
		$this->assertSame('section', $setting->getSection());
		$this->assertSame('comment', $setting->getComment());
	}

	public function dataModifyAction_customEntity() {
		return self::duplicateTestDataForEachPlatform(array(
			array('customEntity'),
		), 'config_customEntity.yml');
	}

}
