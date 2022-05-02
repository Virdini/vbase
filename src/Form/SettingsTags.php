<?php

namespace Drupal\vbase\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Configure system tags and links for this site.
 */
class SettingsTags extends ConfigTypedFormBase {

  /**
   * {@inheritdoc}
   */
  protected $configName = 'vbase.settings.tags';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'vbase_settings_tags';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config($this->configName);
    $definition = $this->definition($this->configName);

    foreach ($definition['mapping'] as $key => $info) {
      if ($info['type'] == 'boolean') {
        $form[$key] = [
          '#type' => 'checkbox',
          '#title' => $this->t($info['label']),
          '#default_value' => $config->get($key),
        ];
      }
    }

    return parent::buildForm($form, $form_state);
  }

}
