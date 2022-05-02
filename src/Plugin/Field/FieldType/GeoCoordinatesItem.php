<?php

namespace Drupal\vbase\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'vbase_geocoordinates' field type.
 *
 * @FieldType(
 *   id = "vbase_geocoordinates",
 *   module = "vbase",
 *   label = @Translation("vbase GeoCoordinates"),
 *   category = @Translation("Virdini"),
 *   default_widget = "vbase_geocoordinates",
 *   default_formatter = "vbase_geocoordinates",
 *   column_groups = {
 *     "coordinates" = {
 *       "label" = @Translation("Coordinates"),
 *       "columns" = {
 *         "latitude", "longitude"
 *       },
 *       "require_all_groups_for_translation" = TRUE
 *     },
 *     "label" = {
 *       "label" = @Translation("Label"),
 *       "translatable" = TRUE
 *     },
 *   },
 * )
 */
class GeoCoordinatesItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'latitude' => [
          'type' => 'float',
          'size' => 'big',
          'not null' => TRUE,
        ],
        'longitude' => [
          'type' => 'float',
          'size' => 'big',
          'not null' => TRUE,
        ],
        'label' => [
          'type' => 'varchar',
          'length' => 255,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['latitude'] = DataDefinition::create('float')
      ->setLabel(t('Latitude'))
      ->setRequired(TRUE);
    $properties['longitude'] = DataDefinition::create('float')
      ->setLabel(t('Longitude'))
      ->setRequired(TRUE);
    $properties['label'] = DataDefinition::create('string')
      ->setLabel(t('Label'));
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function mainPropertyName() {
    // Item has no main property.
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $latitude = $this->get('latitude')->getValue();
    $longitude = $this->get('longitude')->getValue();
    return $latitude === NULL || $latitude === '' || $longitude === NULL || $longitude === '';
  }

}
