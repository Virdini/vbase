<?php

namespace Drupal\vbase\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'vbase_youtube_title' field type.
 *
 * @FieldType(
 *   id = "vbase_youtube_title",
 *   module = "vbase",
 *   label = @Translation("YouTube with title"),
 *   category = @Translation("Virdini"),
 *   default_widget = "vbase_youtube",
 *   default_formatter = "vbase_youtube"
 * )
 */
class YouTubeWithTitleItem extends YouTubeItem {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = parent::schema($field_definition);

    $schema['columns']['title'] = [
      'type' => 'varchar',
      'length' => 255,
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = parent::propertyDefinitions($field_definition);

    $properties['title'] = DataDefinition::create('string')
      ->setLabel(t('Title'));

    return $properties;
  }

}
