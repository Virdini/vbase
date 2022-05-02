<?php

namespace Drupal\vbase\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Render\Element\Checkboxes;

/**
 * Configure the protection of viewing the canonical pages of the entity from users.
 */
class SettingsContentProtection extends ConfigTypedFormBase {

  /**
   * {@inheritdoc}
   */
  protected $configName = 'vbase.settings.cp';

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a \Drupal\vbase\Form\ContentProtectionSettings object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Config\TypedConfigManagerInterface $config_typed
   *   The typed config manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, TypedConfigManagerInterface $config_typed, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($config_factory, $config_typed);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('config.typed'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'vbase_settings_cp';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config($this->configName);
    $definition = $this->definition($this->configName);

    $form['node'] = [
      '#type' => 'checkboxes',
      '#multiple' => TRUE,
      '#title' => $this->t($definition['mapping']['node']['label']),
      '#options' => $this->getEntityOptions('node_type'),
      '#default_value' => $config->get('node'),
    ];

    $form['taxonomy'] = [
      '#type' => 'checkboxes',
      '#multiple' => TRUE,
      '#title' => $this->t($definition['mapping']['taxonomy']['label']),
      '#options' => $this->getEntityOptions('taxonomy_vocabulary'),
      '#default_value' => $config->get('taxonomy'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Determines which checkboxes were checked when a form is submitted.
    foreach (['node', 'taxonomy'] as $key) {
      $form_state->setValue($key, Checkboxes::getCheckedCheckboxes($form_state->getValue($key)));
    }
  }

  /**
   * Gets an array of entities suitable for using as select list options.
   *
   * @param string $type_id
   *   The entity type id.
   *
   * @return array
   *   Array of entities, key is set to entity id and value is set to entity label.
   */
  protected function getEntityOptions(string $type_id) {
    $storage = $this->entityTypeManager->getStorage($type_id);

    $options = [];
    foreach ($storage->loadMultiple() as $entity) {
      $options[$entity->id()] = $entity->label();
    }

    return $options;
  }

}
