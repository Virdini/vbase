<?php

namespace Drupal\vbase_browsers\Form;

use Drupal\vbase\Form\ConfigTypedFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure site browsers support settings for this site.
 */
class BrowsersSupportSettings extends ConfigTypedFormBase {

  /**
   * {@inheritdoc}
   */
  protected $configName = 'vbase_browsers.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'vbase_browsers_support_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config($this->configName);
    $definition = $this->definition($this->configName);

    $form['ie'] = [
      '#type' => 'select',
      '#title' => $this->t($definition['mapping']['ie']['label']),
      '#default_value' => $config->get('ie'),
      '#options' => [
        'unsupported' => $this->t('(Unsupported)'),
        '11' => '11',
        '10' => '10+',
        '9' => '9+',
        '8' => '8+',
        '7' => '7+',
      ],
    ];

    $form['ie_hide_badge'] = [
      '#type' => 'checkbox',
      '#title' => $this->t($definition['mapping']['ie_hide_badge']['label']),
      '#default_value' => $config->get('ie_hide_badge'),
    ];

    return parent::buildForm($form, $form_state);
  }

}
