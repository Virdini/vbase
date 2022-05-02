<?php

namespace Drupal\vbase\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'vbase_youtube' widget.
 *
 * @FieldWidget(
 *   id = "vbase_youtube",
 *   label = @Translation("vbase YouTube"),
 *   field_types = {
 *     "vbase_youtube"
 *   }
 * )
 */
class YouTubeWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $properties = $items->getItemDefinition()->getPropertyDefinitions();
    $element['#type'] = $element['#title_display'] == 'invisible' ? 'container' : 'fieldset';
    $element['#attributes']['class'][] = 'vbase-field-widget-grid';
    if (!$items[$delta]->value) {
      $items[$delta]->width = 940;
      $items[$delta]->height = 640;
      $items[$delta]->resp = TRUE;
    }
    $element['value'] = [
      '#type' => 'textfield',
      '#title' => $properties['value']->getLabel(),
      '#default_value' => $items[$delta]->value,
      '#required' => $element['#required'],
    ];
    $element['width'] = [
      '#type' => 'number',
      '#title' => $properties['width']->getLabel(),
      '#default_value' => $items[$delta]->width,
    ];
    $element['height'] = [
      '#type' => 'number',
      '#title' => $properties['height']->getLabel(),
      '#default_value' => $items[$delta]->height,
    ];
    $element['resp'] = [
      '#type' => 'checkbox',
      '#title' => $properties['resp']->getLabel(),
      '#default_value' => $items[$delta]->resp,
    ];
    return $element;
  }

}
