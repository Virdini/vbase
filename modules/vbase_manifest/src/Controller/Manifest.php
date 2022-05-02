<?php

namespace Drupal\vbase_manifest\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Cache\CacheableJsonResponse;

/**
 * Provides a 'Manifest'
 */
class Manifest extends ControllerBase {

  public function build() {
    $lang = $language = $this->languageManager()->getCurrentLanguage();
    $output = [
     	'dir' => $lang->getDirection(),
      'lang'=> $lang->getId(),
      'start_url' => './?utm_source=manifest&utm_medium=manifest&utm_campaign=manifest',
      'icons' => [],
    ];
    $config = $this->config('vbase_manifest.settings');
    foreach ($config->get() as $key => $value) {
      if (in_array($key, ['_core', 'langcode']) || empty($value) || is_array($value)) {
        continue;
      }
      $output[$key] = trim($value);
    }
    if (isset($output['short_name']) || isset($output['name'])) {
      foreach (\Drupal::service('vbase_manifest')->getManifestIcons() as $icon) {
        $icon['src'] = \Drupal::service('file_url_generator')->generateString($icon['uri']);
        unset($icon['uri']);
        $output['icons'][] = $icon;
      }
    }
    $response = new CacheableJsonResponse($output, 200, ['Content-Type' => 'application/manifest+json']);
    return $response->addCacheableDependency($config);
  }

}
