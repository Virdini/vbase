<?php

namespace Drupal\vbase\Plugin\Field\FieldWidget;

use Drupal\Core\Datetime\Plugin\Field\FieldWidget\TimestampDatetimeWidget as CoreTimestampDatetimeWidget;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'vbase datetime timestamp' widget.
 *
 * @FieldWidget(
 *   id = "vbase_datetime_timestamp",
 *   label = @Translation("vbase Datetime Timestamp"),
 *   field_types = {
 *     "timestamp"
 *   }
 * )
 */
class TimestampDatetimeWidget extends CoreTimestampDatetimeWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $default_value = isset($items[$delta]->value) && $items[$delta]->value ? DrupalDateTime::createFromTimestamp($items[$delta]->value) : '';
    $element['value'] = $element + [
      '#type' => 'datetime',
      '#default_value' => $default_value,
    ];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as $key => &$item) {
      // @todo The structure is different whether access is denied or not, to
      //   be fixed in https://www.drupal.org/node/2326533.
      if (isset($item['value']) && $item['value'] instanceof DrupalDateTime) {
        $item['value'] = $item['value']->getTimestamp();
      }
      elseif (isset($item['value']['object']) && $item['value']['object'] instanceof DrupalDateTime) {
        $item['value'] = $item['value']['object']->getTimestamp();
      }
      else {
        $item['value'] = 0;
      }
    }
    return $values;
  }

}
