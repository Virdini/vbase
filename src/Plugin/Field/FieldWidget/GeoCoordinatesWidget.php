<?php

namespace Drupal\vbase\Plugin\Field\FieldWidget;

use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'vbase_geocoordinates' widget.
 *
 * @FieldWidget(
 *   id = "vbase_geocoordinates",
 *   label = @Translation("vbase GeoCoordinates"),
 *   field_types = {
 *     "vbase_geocoordinates"
 *   }
 * )
 */
class GeoCoordinatesWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $properties = $items->getItemDefinition()->getPropertyDefinitions();
    $element['#type'] = $element['#title_display'] == 'invisible' ? 'container' : 'fieldset';
    $element['#attributes']['class'][] = 'vbase-field-widget-grid';
    $element['latitude'] = [
      '#type' => 'textfield',
      '#title' => $properties['latitude']->getLabel(),
      '#default_value' => $items[$delta]->latitude,
      '#required' => $element['#required'],
    ];
    $element['longitude'] = [
      '#type' => 'textfield',
      '#title' => $properties['longitude']->getLabel(),
      '#default_value' => $items[$delta]->longitude,
      '#required' => $element['#required'],
    ];
    $element['label'] = [
      '#type' => 'textfield',
      '#title' => $properties['label']->getLabel(),
      '#default_value' => $items[$delta]->label,
    ];
    return $element;
  }

}
