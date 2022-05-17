<?php

namespace Drupal\vbase\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'vbase_youtube' formatter.
 *
 * @FieldFormatter(
 *   id = "vbase_youtube",
 *   label = @Translation("YouTube"),
 *   field_types = {
 *     "vbase_youtube",
 *     "vbase_youtube_title"
 *   }
 * )
 */
class YouTubeFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $elements[$delta] = [
        '#theme' => 'vbase_youtube',
        '#id' => $item->value,
        '#title' => $item->title,
        '#width' => $item->width,
        '#height' => $item->height,
        '#responsive' => $item->resp,
      ];
    }

    return $elements;
  }

}
