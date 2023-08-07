<?php

namespace Craue\ConfigBundle\Tests\Controller;

use Craue\ConfigBundle\Entity\Setting;
use Craue\ConfigBundle\Entity\SettingInterface;
use Craue\ConfigBundle\Tests\IntegrationTestBundle\Entity\CanBeDisabledSetting;
use Craue\ConfigBundle\Tests\IntegrationTestBundle\Entity\CustomSetting;
use Craue\ConfigBundle\Tests\IntegrationTestCase;

/**
 * @group integration
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2023 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class SettingsControllerIntegrationTest extends IntegrationTestCase {

	/**
	 * @dataProvider getPlatformConfigs
	 */
	public function testModifyAction_noSettings($platform, $config, $requiredExtension) {
		$this->initClient($requiredExtension, ['environment' => $platform, 'config' => $config]);

		static::$client->request('GET', $this->url('craue_config_settings_modify'));
		$content = static::$client->getResponse()->getContent();
		$this->assertSame(200, static::$client->getResponse()->getStatusCode(), $content);
		$this->assertStringContainsString('<div class="craue_config_settings_modify">', $content);
		$this->assertStringContainsString('There are no settings defined yet.', $content);
	}

	/**
	 * @dataProvider getPlatformConfigs
	 */
	public function testModifyAction_noChanges($platform, $config, $requiredExtension) {
		$this->initClient($requiredExtension, ['environment' => $platform, 'config' => $config]);
		$this->persistSetting(Setting::create('name', 'value'));

		$crawler = static::$client->request('GET', $this->url('craue_config_settings_modify'));
		$content = static::$client->getResponse()->getContent();
		$this->assertSame(200, static::$client->getResponse()->getStatusCode(), $content);
		$this->assertMatchesRegularExpression('/<form .*method="post" .*class="craue_config_settings_modify".*>/', $content);
		$this->assertStringContainsString('<label for="craue_config_modifySettings_settings_name_value">name</label>', $content);
		$this->assertStringContainsString('<input type="text" id="craue_config_modifySettings_settings_name_value" name="craue_config_modifySettings[settings][name][value]" value="value" />', $content);
		$this->assertStringContainsString('<button type="submit">apply</button>', $content);

		$form = $crawler->selectButton('apply')->form();
		static::$client->followRedirects();
		static::$client->submit($form);
		$content = static::$client->getResponse()->getContent();
		$this->assertStringContainsString('<div class="notice">The settings were changed.</div>', $content);

		/* @var $setting SettingInterface */
		$setting = $this->getSettingsRepo()->findOneBy([]);
		$this->assertSame('name', $setting->getName());
		$this->assertSame('value', $setting->getValue());
		$this->assertNull($setting->getSection());
	}

	/**
	 * Ensure that the value of a setting added between rendering and submitting the form won't get lost.
	 *
	 * @dataProvider getPlatformConfigs
	 */
	public function testModifyAction_noChanges_concurrentlyAddedSetting($platform, $config, $requiredExtension) : void {
		$this->initClient($requiredExtension, ['environment' => $platform, 'config' => $config]);
		$this->persistSetting(Setting::create('name1', 'value1'));
		$this->persistSetting(Setting::create('name2', 'value2'));

		$crawler = static::$client->request('GET', $this->url('craue_config_settings_modify'));
		$form = $crawler->selectButton('apply')->form();

		// add a new setting which would be placed between name1 and name2
		$this->persistSetting(Setting::create('name11', 'value11'));

		static::$client->submit($form);

		/* @var $setting SettingInterface */
		$setting = $this->getSettingsRepo()->findOneBy([
			'name' => 'name11',
		]);
		$this->assertSame('value11', $setting->getValue());
	}

	/**
	 * Ensure that only the value can be changed, but neither name nor section.
	 *
	 * @dataProvider getPlatformConfigs
	 */
	public function testModifyAction_changeValue($platform, $config, $requiredExtension) {
		$this->initClient($requiredExtension, ['environment' => $platform, 'config' => $config]);
		$this->persistSetting(Setting::create('name', 'value', 'section'));

		$crawler = static::$client->request('GET', $this->url('craue_config_settings_modify'));
		$form = $crawler->selectButton('apply')->form();
		$this->assertFalse($form->has('craue_config_modifySettings[settings][name][name]'));
		$this->assertFalse($form->has('craue_config_modifySettings[settings][name][section]'));
		static::$client->followRedirects();
		static::$client->submit($form, [
			'craue_config_modifySettings[settings][name][value]' => 'new-value',
		]);
		$content = static::$client->getResponse()->getContent();
		$this->assertStringContainsString('<div class="notice">The settings were changed.</div>', $content);

		/* @var $setting SettingInterface */
		$setting = $this->getSettingsRepo()->findOneBy([]);
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
		$this->initClient($requiredExtension, ['environment' => $environment . '_' . $platform, 'config' => $config]);
		$this->persistSetting(Setting::create('name1', 'value1'));
		$this->persistSetting(Setting::create('name2', 'value2'));

		$cache = $this->getService('craue_config_cache_adapter');
		$cache->clear();
		$this->assertFalse($cache->has('name1'));
		$this->assertFalse($cache->has('name2'));

		$crawler = static::$client->request('GET', $this->url('craue_config_settings_modify'));
		$form = $crawler->selectButton('apply')->form();
		static::$client->submit($form, [
			'craue_config_modifySettings[settings][name1][value]' => 'new-value1',
		]);

		$this->assertTrue($cache->has('name1'));
		$this->assertFalse($cache->has('name2'));
		$this->assertSame('new-value1', $cache->get('name1'));
	}

	public function dataModifyAction_changeValue_cacheUsage() {
		$testData = self::duplicateTestDataForEachPlatform([
			['cache_SymfonyCacheComponent_filesystem'],
		], 'config_cache_SymfonyCacheComponent_filesystem.yml');

		if (!empty($_ENV['REDIS_DSN'])) {
			$testData = array_merge($testData, self::duplicateTestDataForEachPlatform([
				['cache_SymfonyCacheComponent_redis'],
			], 'config_cache_SymfonyCacheComponent_redis.yml'));
		}

		// TODO remove as soon as Symfony >= 5.0 is required
		if (class_exists(\Doctrine\Bundle\DoctrineCacheBundle\DoctrineCacheBundle::class)) {
			$testData = array_merge($testData, self::duplicateTestDataForEachPlatform([
				['cache_DoctrineCacheBundle_file_system'],
			], 'config_cache_DoctrineCacheBundle_file_system.yml'));
		}

		return $testData;
	}

	/**
	 * Ensure that values are assigned to their originating setting when adding a setting between rendering and submitting the form.
	 *
	 * @dataProvider getPlatformConfigs
	 */
	public function testModifyAction_changeValue_concurrentlyAddedSetting($platform, $config, $requiredExtension) : void {
		$this->initClient($requiredExtension, ['environment' => $platform, 'config' => $config]);
		$this->persistSetting(Setting::create('name1', 'old-value1'));
		$this->persistSetting(Setting::create('name2', 'old-value2'));
		$this->persistSetting(Setting::create('name3', 'old-value3'));

		$crawler = static::$client->request('GET', $this->url('craue_config_settings_modify'));
		$form = $crawler->selectButton('apply')->form();

		// add a new setting which would be placed between name1 and name2
		$this->persistSetting(Setting::create('name11', 'old-value11'));

		static::$client->submit($form, [
			'craue_config_modifySettings[settings][name1][value]' => 'new-value1',
			'craue_config_modifySettings[settings][name2][value]' => '',
			'craue_config_modifySettings[settings][name3][value]' => 'new-value3',
		]);

		$this->assertSame('new-value1', $this->getSettingsRepo()->findOneBy(['name' => 'name1'])->getValue());
		$this->assertSame('old-value11', $this->getSettingsRepo()->findOneBy(['name' => 'name11'])->getValue());
		$this->assertNull($this->getSettingsRepo()->findOneBy(['name' => 'name2'])->getValue());
		$this->assertSame('new-value3', $this->getSettingsRepo()->findOneBy(['name' => 'name3'])->getValue());
	}

	/**
	 * Ensure that an invalid form submission is handled properly.
	 *
	 * @dataProvider getPlatformConfigs
	 */
	public function testModifyAction_formInvalid($platform, $config, $requiredExtension) {
		$this->initClient($requiredExtension, ['environment' => $platform, 'config' => $config]);
		$this->persistSetting(Setting::create('name', 'value'));

		$crawler = static::$client->request('GET', $this->url('craue_config_settings_modify'));
		$form = $crawler->selectButton('apply')->form();
		$form->remove('craue_config_modifySettings[settings][name][value]');
		static::$client->followRedirects();
		static::$client->submit($form);
		$content = static::$client->getResponse()->getContent();
		$this->assertStringNotContainsString('<div class="notice">The settings were changed.</div>', $content);
	}

	/**
	 * Ensure that dynamic values (sections, names) are properly translated (exactly once).
	 *
	 * @dataProvider getPlatformConfigs
	 */
	public function testModifyAction_properTranslations($platform, $config, $requiredExtension) {
		$this->initClient($requiredExtension, ['environment' => $platform, 'config' => $config]);
		$this->persistSetting(Setting::create('setting-number-one', 'value', 'section-number-one'));

		static::$client->enableProfiler();
		static::$client->request('GET', $this->url('craue_config_settings_modify'));
		$content = static::$client->getResponse()->getContent();
		$this->assertStringContainsString('<legend>section no. 1</legend>', $content);
		$this->assertStringContainsString('<label for="craue_config_modifySettings_settings_setting-number-one_value">setting no. 1</label>', $content);

		$profile = static::$client->getProfile();
		$this->assertSame(0, $profile->getCollector('translation')->getCountMissings());
	}

	/**
	 * @dataProvider getPlatformConfigs
	 */
	public function testModifyAction_sectionOrder_defaultOrder($platform, $config, $requiredExtension) {
		$this->initClient($requiredExtension, ['environment' => $platform, 'config' => $config]);
		$this->persistSetting(Setting::create('name1', 'value1', 'section1'));
		$this->persistSetting(Setting::create('name2', 'value2'));
		$this->persistSetting(Setting::create('name3', 'value3', 'section2'));

		static::$client->request('GET', $this->url('craue_config_settings_modify'));
		$content = static::$client->getResponse()->getContent();
		$this->assertStringContainsString('<legend>section1</legend>', $content);
		$this->assertStringContainsString('<legend>section2</legend>', $content);
		$strPosField1 = strpos($content, '<label for="craue_config_modifySettings_settings_name1_value">name1</label>');
		$strPosField2 = strpos($content, '<label for="craue_config_modifySettings_settings_name2_value">name2</label>');
		$strPosField3 = strpos($content, '<label for="craue_config_modifySettings_settings_name3_value">name3</label>');
		$this->assertTrue($strPosField2 < $strPosField1 && $strPosField1 < $strPosField3, 'The sections are rendered in wrong order.');
	}

	/**
	 * @dataProvider dataModifyAction_sectionOrder_customOrder
	 */
	public function testModifyAction_sectionOrder_customOrder($platform, $config, $requiredExtension) {
		$this->initClient($requiredExtension, ['environment' => 'customSectionOrder_' . $platform, 'config' => $config]);
		$this->persistSetting(Setting::create('name1', 'value1', 'section1'));
		$this->persistSetting(Setting::create('name2', 'value2'));
		$this->persistSetting(Setting::create('name3', 'value3', 'section2'));

		static::$client->request('GET', $this->url('craue_config_settings_modify'));
		$content = static::$client->getResponse()->getContent();
		$this->assertStringContainsString('<legend>section1</legend>', $content);
		$this->assertStringContainsString('<legend>section2</legend>', $content);
		$strPosField1 = strpos($content, '<label for="craue_config_modifySettings_settings_name1_value">name1</label>');
		$strPosField2 = strpos($content, '<label for="craue_config_modifySettings_settings_name2_value">name2</label>');
		$strPosField3 = strpos($content, '<label for="craue_config_modifySettings_settings_name3_value">name3</label>');
		$this->assertTrue($strPosField2 < $strPosField3 && $strPosField3 < $strPosField1, 'The sections are rendered in wrong order.');
	}

	public function dataModifyAction_sectionOrder_customOrder() {
		return self::duplicateTestDataForEachPlatform([
			[],
		], 'config_customSectionOrder.yml');
	}

	/**
	 * @dataProvider dataModifyAction_redirectRouteAfterModify
	 */
	public function testModifyAction_redirectRouteAfterModify($platform, $config, $requiredExtension) {
		$this->initClient($requiredExtension, ['environment' => 'redirectRouteAfterModify_' . $platform, 'config' => $config]);
		$this->persistSetting(Setting::create('name', 'value'));

		$this->assertSame('admin_settings_start', static::$client->getContainer()->getParameter('craue_config.redirectRouteAfterModify'));

		$crawler = static::$client->request('GET', $this->url('craue_config_settings_modify'));
		$form = $crawler->selectButton('apply')->form();
		static::$client->submit($form);
		$this->assertRedirect($this->url('admin_settings_start'));
	}

	public function dataModifyAction_redirectRouteAfterModify() {
		return self::duplicateTestDataForEachPlatform([
			[],
		], 'config_redirectRouteAfterModify.yml');
	}

	/**
	 * Ensure that a custom model class can actually be used.
	 *
	 * @dataProvider dataModifyAction_customEntity
	 */
	public function testModifyAction_customEntity($platform, $config, $requiredExtension, $environment) {
		$this->initClient($requiredExtension, ['environment' => $environment . '_' . $platform, 'config' => $config]);
		$this->persistSetting(CustomSetting::create('name', 'value', 'section', 'comment'));
		$newValue = str_repeat('X', 200) . "\n" . str_repeat('Y', 99);

		$crawler = static::$client->request('GET', $this->url('craue_config_settings_modify'));
		$content = static::$client->getResponse()->getContent();
		$this->assertStringContainsString('<textarea id="craue_config_modifySettings_settings_name_value" name="craue_config_modifySettings[settings][name][value]">value</textarea>', $content);

		$form = $crawler->selectButton('apply')->form();
		static::$client->submit($form, [
			'craue_config_modifySettings[settings][name][value]' => $newValue,
		]);

		/* @var $setting CustomSetting */
		$setting = $this->getSettingsRepo()->findOneBy([]);
		$this->assertSame('name', $setting->getName());
		$this->assertSame(strlen($newValue), strlen($setting->getValue()));
		$this->assertSame($newValue, $setting->getValue());
		$this->assertSame('section', $setting->getSection());
		$this->assertSame('comment', $setting->getComment());
	}

	public function dataModifyAction_customEntity() {
		return self::duplicateTestDataForEachPlatform([
			['customEntity'],
		], 'config_customEntity.yml');
	}

	/**
	 * Ensure that submitting a disabled form field will keep the setting's old value.
	 *
	 * @dataProvider dataModifyAction_customEntity_disabled
	 */
	public function testModifyAction_customEntity_disabled($platform, $config, $requiredExtension, $environment) {
		$this->initClient($requiredExtension, ['environment' => $environment . '_' . $platform, 'config' => $config]);
		$this->persistSetting(CanBeDisabledSetting::create('name', 'old-value', null, true));

		$crawler = static::$client->request('GET', $this->url('craue_config_settings_modify'));
		$form = $crawler->selectButton('apply')->form();

		$this->assertTrue($form->get('craue_config_modifySettings[settings][name][value]')->isDisabled());

		static::$client->submit($form, [
			'craue_config_modifySettings[settings][name][value]' => 'new-value', // will be ignored
		]);

		/* @var $setting CanBeDisabledSetting */
		$setting = $this->getSettingsRepo()->findOneBy([]);
		$this->assertSame('old-value', $setting->getValue());
	}

	public function dataModifyAction_customEntity_disabled() {
		return self::duplicateTestDataForEachPlatform([
			['config_customEntity_canBeDisabled'],
		], 'config_customEntity_canBeDisabled.yml');
	}

}
