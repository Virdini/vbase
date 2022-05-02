<?php

namespace Drupal\vbase\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Html;

/**
 * Plugin implementation of the 'vbase_geojsonfeaturecollection' formatter.
 *
 * @link https://en.wikipedia.org/wiki/GeoJSON
 *
 * @FieldFormatter(
 *   id = "vbase_geojsonfeaturecollection",
 *   label = @Translation("vbase GeoJSONFeatureCollection"),
 *   field_types = {
 *     "vbase_geocoordinates"
 *   }
 * )
 */
class GeoJSONFeatureCollectionFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $data = [
      'type' => 'FeatureCollection',
      'features' => [],
    ];
    $entity = $items->getEntity();
    $key = Html::cleanCssIdentifier('gj-' . $entity->getEntityTypeId() . '-' . $entity->id() . '-' . $items->getName());
    foreach ($items as $delta => $item) {
      $data['features'][] = [
        'type' => 'Feature',
        'id' => $key . '-' . $delta,
        'geometry' => [
          'type' => 'Point',
          'coordinates' => [(float) $item->longitude, (float) $item->latitude],
        ],
        'properties' => [
          'label' => $item->label,
        ],
      ];
    }
    if (empty($data['features'])) {
      return [];
    }
    return [
      '#is_multiple' => FALSE,
      '#attributes' => ['id' => $key . '-map', 'class' => ['gj-field-map']],
      0 => [
        'json' => [
          '#type' => 'html_tag',
          '#tag' => 'script',
          '#attributes' => ['id' => $key . '-data', 'class' => ['gj-data'], 'type' => 'application/json'],
          '#value' => Json::encode($data),
        ],
      ],
    ];
  }

}
