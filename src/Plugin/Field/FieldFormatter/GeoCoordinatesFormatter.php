<?php

namespace Drupal\vbase\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'vbase_geocoordinates' formatter.
 *
 * @FieldFormatter(
 *   id = "vbase_geocoordinates",
 *   label = @Translation("vbase GeoCoordinates"),
 *   field_types = {
 *     "vbase_geocoordinates"
 *   }
 * )
 */
class GeoCoordinatesFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        '#markup' => $item->latitude . ', ' . $item->longitude,
      ];
      if ($item->label) {
        $elements[$delta]['#markup'] = $item->label . ': ' . $elements[$delta]['#markup'];
      }
    }
    return $elements;
  }

}
