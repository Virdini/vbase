<?php

namespace Drupal\vbase\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Base class for implementing system configuration forms with typed config manager.
 */
abstract class ConfigTypedFormBase extends ConfigFormBase {

  /**
   * The config name.
   *
   * @var string
   */
  protected $configName;

  /**
   * The typed config manager.
   *
   * @var \Drupal\Core\Config\TypedConfigManagerInterface
   */
  protected $configTyped;

  /**
   * Constructs a \Drupal\vbase\Form\ConfigTypedFormBase object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Config\TypedConfigManagerInterface $config_typed
   *   The typed config manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, TypedConfigManagerInterface $config_typed) {
    parent::__construct($config_factory);
    $this->configTyped = $config_typed;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('config.typed')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [$this->configName];
  }

  /**
   * {@inheritdoc}
   */
  protected function definition($name) {
    return $this->configTyped->getDefinition($name);;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config($this->configName);
    $definition = $this->definition($this->configName);

    foreach ($form_state->getValues() as $key => $value) {
      if (isset($definition['mapping'][$key])) {
        $config->set($key, $value);
      }
    }

    $config->save();

    parent::submitForm($form, $form_state);
  }

}
