<?php

/**
 * Prepares variables for vbase_youtube templates.
 *
 * Default template: vbase-youtube.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - id: Youtube video id.
 */
function template_preprocess_vbase_youtube(array &$variables) {
  $variables['#attached']['library'][] = 'vbase/youtube';

  // Set default width
  if (!$variables['width']) {
    $variables['width'] = 4 * 4 * 40;
  }

  // Set default height
  if (!$variables['height']) {
    $variables['height'] = 3 * 4 * 40;
  }

  $variables['_width'] = $variables['width'];
  $variables['_height'] = $variables['height'];
  $variables['ratio'] = round(100 * $variables['_height'] / $variables['_width'], 2);

  // Change width & height if responsive
  if ($variables['responsive']) {
    $variables['width'] = '100%';
    $variables['height'] = '100%';
    $variables['attributes']['style'][] = 'padding-top:'. $variables['ratio'] .'%';
  }
}
