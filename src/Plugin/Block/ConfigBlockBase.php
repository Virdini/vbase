<?php

namespace Drupal\vbase\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Cache\Cache;

abstract class ConfigBlockBase extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The config name.
   *
   * @var string
   */
  protected $configName;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a \Drupal\vbase\Plugin\Block\ConfigBaseBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory')
    );
  }

  /**
   * Gets the configuration names associated with this block for caching.
   *
   * @return array
   *   An array of configuration object names.
   */
  protected function getCacheableConfigNames() {
    return [$this->configName];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    $contexts = parent::getCacheContexts();
    foreach ($this->configFactory->loadMultiple($this->getCacheableConfigNames()) as $config) {
      $contexts = Cache::mergeContexts($contexts, $config->getCacheContexts());
    }
    return $contexts;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $tags  = parent::getCacheTags();
    foreach ($this->configFactory->loadMultiple($this->getCacheableConfigNames()) as $config) {
      $tags = Cache::mergeTags($tags, $config->getCacheTags());
    }
    return $tags;
  }

}
