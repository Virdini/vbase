<?php

namespace Drupal\vbase\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Configure SMTP server settings for this site.
 */
class SettingsSMTP extends ConfigTypedFormBase {

  /**
   * {@inheritdoc}
   */
  protected $configName = 'vbase.settings.smtp';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'vbase_settings_smtp';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config($this->configName);
    $definition = $this->definition($this->configName);

    $form['enabled'] = [
      '#title' => $this->t($definition['mapping']['enabled']['label']),
      '#type' => 'checkbox',
      '#default_value' => $config->get('enabled'),
    ];

    $form['host'] = [
      '#title' => $this->t($definition['mapping']['host']['label']),
      '#type' => 'textfield',
      '#default_value' => $config->get('host'),
      '#placeholder' => '[encryption type]://[hostname]:[port]',
      '#description' => 'Gmail SMTP server: tls://smtp.gmail.com:587',
    ];

    $form['username'] = [
      '#title' => $this->t($definition['mapping']['username']['label']),
      '#type' => 'textfield',
      '#default_value' => $config->get('username'),
    ];

    $form['password'] = [
      '#title' => $this->t($definition['mapping']['password']['label']),
      '#type' => 'textfield',
      '#default_value' => $config->get('password'),
    ];

    $form['from'] = [
      '#title' => $this->t($definition['mapping']['from']['label']),
      '#type' => 'textfield',
      '#default_value' => $config->get('from'),
      '#placeholder' => 'Name <mail@example.com>',
    ];

    return parent::buildForm($form, $form_state);
  }

}
