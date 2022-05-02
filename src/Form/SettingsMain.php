<?php

namespace Drupal\vbase\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Configure site name, slogan and email address.
 */
class SettingsMain extends ConfigTypedFormBase {

  /**
   * {@inheritdoc}
   */
  protected $configName = 'system.site';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'vbase_settings_main';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config($this->configName);
    $definition = $this->definition($this->configName);

    $site_mail = $config->get('mail');
    if (empty($site_mail)) {
      $site_mail = ini_get('sendmail_from');
    }

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t($definition['mapping']['name']['label']),
      '#default_value' => $config->get('name'),
      '#required' => TRUE,
    ];

    $form['slogan'] = [
      '#type' => 'textfield',
      '#title' => $this->t($definition['mapping']['slogan']['label']),
      '#default_value' => $config->get('slogan'),
      '#description' => $this->t("How this is used depends on your site's theme."),
    ];

    $form['mail'] = [
      '#type' => 'email',
      '#title' => $this->t($definition['mapping']['mail']['label']),
      '#default_value' => $site_mail,
      '#description' => $this->t("The <em>From</em> address in automated emails sent during registration and new password requests, and other notifications. (Use an address ending in your site's domain to help prevent this email being flagged as spam.)"),
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

}
