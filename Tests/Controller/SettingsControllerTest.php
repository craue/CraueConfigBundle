<?php

namespace Craue\ConfigBundle\Tests;

use Craue\ConfigBundle\Tests\IntegrationTestCase;

/**
 * @group integration
 *
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2014 Christian Raue
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class SettingsControllerTest extends IntegrationTestCase {

	public function testModifyAction_noSettings() {
		$client = static::createClient();

		$client->request('GET', $this->url($client, 'craue_config_settings_modify'));
		$this->assertSame(200, $client->getResponse()->getStatusCode());
		$content = $client->getResponse()->getContent();
		$this->assertContains('<div class="craue_config_settings_modify">', $content);
		$this->assertContains('There are no settings defined yet.', $content);

// var_dump($client->getResponse()->getContent());
// die;
	}

	public function testModifyAction_noChanges() {
		$this->persistSetting('name', 'value');

		$client = static::createClient();

		$crawler = $client->request('GET', $this->url($client, 'craue_config_settings_modify'));
		$this->assertSame(200, $client->getResponse()->getStatusCode());
		$content = $client->getResponse()->getContent();
		$this->assertContains('<form method="post" class="craue_config_settings_modify" >', $content);
		$this->assertContains('<input type="hidden" id="craue_config_modifySettings_settings_0_name" name="craue_config_modifySettings[settings][0][name]" value="name" />', $content);
		$this->assertContains('<input type="hidden" id="craue_config_modifySettings_settings_0_section" name="craue_config_modifySettings[settings][0][section]" />', $content);
		$this->assertContains('<label for="craue_config_modifySettings_settings_0_value">name</label>', $content);
		$this->assertContains('<input type="text" id="craue_config_modifySettings_settings_0_value" name="craue_config_modifySettings[settings][0][value]" value="value" />', $content);
		$this->assertContains('<button type="submit">apply</button>', $content);

		$form = $crawler->selectButton('apply')->form();
		$client->followRedirects();
		$client->submit($form);
		$content = $client->getResponse()->getContent();
		$this->assertContains('<div class="notice">The settings were changed.</div>', $content);
		$this->assertContains('<input type="text" id="craue_config_modifySettings_settings_0_value" name="craue_config_modifySettings[settings][0][value]" value="value" />', $content);

// var_dump($client->getResponse()->getContent());
// die;
	}

	public function testModifyAction_changeValue() {
		$this->persistSetting('name', 'value');

		$client = static::createClient();

		$crawler = $client->request('GET', $this->url($client, 'craue_config_settings_modify'));
		$form = $crawler->selectButton('apply')->form();
		$client->followRedirects();
		$client->submit($form, array(
			'craue_config_modifySettings[settings][0][value]' => 'new-value',
		));
		$content = $client->getResponse()->getContent();
		$this->assertContains('<input type="text" id="craue_config_modifySettings_settings_0_value" name="craue_config_modifySettings[settings][0][value]" value="new-value" />', $content);

// var_dump($client->getResponse()->getContent());
// die;
	}

	public function testModifyAction_sectionOrder_defaultOrder() {
		$this->persistSetting('name1', 'value1', 'section1');
		$this->persistSetting('name2', 'value2');
		$this->persistSetting('name3', 'value3', 'section2');

		$client = static::createClient();

		$client->request('GET', $this->url($client, 'craue_config_settings_modify'));
		$content = $client->getResponse()->getContent();
		$this->assertContains('<legend>section1</legend>', $content);
		$this->assertContains('<legend>section2</legend>', $content);
		$strPosField1 = strpos($content, '<label for="craue_config_modifySettings_settings_0_value">name1</label>');
		$strPosField2 = strpos($content, '<label for="craue_config_modifySettings_settings_1_value">name2</label>');
		$strPosField3 = strpos($content, '<label for="craue_config_modifySettings_settings_2_value">name3</label>');
		$this->assertTrue($strPosField2 < $strPosField1 && $strPosField1 < $strPosField3, 'The sections are rendered in wrong order.');

// var_dump($client->getResponse()->getContent());
// die;
	}

	public function testModifyAction_sectionOrder_customOrder() {
		$this->persistSetting('name1', 'value1', 'section1');
		$this->persistSetting('name2', 'value2');
		$this->persistSetting('name3', 'value3', 'section2');

		$client = static::createClient(array('environment' => 'customSectionOrder', 'config' => 'config_customSectionOrder.yml'));

		$client->request('GET', $this->url($client, 'craue_config_settings_modify'));
		$content = $client->getResponse()->getContent();
		$this->assertContains('<legend>section1</legend>', $content);
		$this->assertContains('<legend>section2</legend>', $content);
		$strPosField1 = strpos($content, '<label for="craue_config_modifySettings_settings_0_value">name1</label>');
		$strPosField2 = strpos($content, '<label for="craue_config_modifySettings_settings_1_value">name2</label>');
		$strPosField3 = strpos($content, '<label for="craue_config_modifySettings_settings_2_value">name3</label>');
		$this->assertTrue($strPosField2 < $strPosField3 && $strPosField3 < $strPosField1, 'The sections are rendered in wrong order.');

// var_dump($client->getResponse()->getContent());
// die;
	}

	public function testModifyAction_redirectRouteAfterModify() {
		$this->persistSetting('name', 'value');

		$client = static::createClient(array('environment' => 'redirectRouteAfterModify', 'config' => 'config_redirectRouteAfterModify.yml'));
		$this->assertSame('admin_settings_start', $client->getContainer()->getParameter('craue_config.redirectRouteAfterModify'));

		$crawler = $client->request('GET', $this->url($client, 'craue_config_settings_modify'));
		$form = $crawler->selectButton('apply')->form();
		$client->submit($form);
		$this->assertRedirect($client, $this->url($client, 'admin_settings_start'));

// var_dump($client->getResponse()->getContent());
// die;
	}

}
