<?php

namespace Drupal\vbase_manifest\Form;

use Drupal\vbase\Form\ConfigTypedFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure site manifest settings for this site.
 */
class ManifestSettings extends ConfigTypedFormBase {

  /**
   * {@inheritdoc}
   */
  protected $configName = 'vbase_manifest.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'vbase_manifest_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config($this->configName);
    $definition = $this->definition($this->configName);

    foreach ($definition['mapping'] as $key => $info) {
      if ($info['type'] == 'label'
          || in_array($key, ['theme_color', 'background_color', 'mask_icon_color'])) {
        $form[$key] = [
          '#type' => 'textfield',
          '#title' => $this->t($info['label']),
          '#default_value' => $config->get($key),
        ];
      }
    }
    $form['display'] = [
      '#type' => 'select',
      '#title' => $this->t($definition['mapping']['display']['label']),
      '#default_value' => $config->get('display'),
      '#options' => [
        '' => '',
        'fullscreen' => 'fullscreen',
        'standalone' => 'standalone',
        'minimal-ui' => 'minimal-ui',
        'browser' => 'browser',
      ],
    ];
    $form['orientation'] = [
      '#type' => 'select',
      '#title' => $this->t($definition['mapping']['orientation']['label']),
      '#default_value' => $config->get('orientation'),
      '#options' => [
        '' => '',
        'any' => 'any',
        'natural' => 'natural',
        'landscape' => 'landscape',
        'landscape-primary' => 'landscape-primary',
        'landscape-secondary' => 'landscape-secondary',
        'portrait' => 'portrait',
        'portrait-primary' => 'portrait-primary',
        'portrait-secondary' => 'portrait-secondary',
      ],
    ];
    return parent::buildForm($form, $form_state);
  }

}
