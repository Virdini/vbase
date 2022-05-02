<?php

namespace Drupal\vbase_manifest;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

class Manifest {

  /**
   * The manifest config.
   */
  protected $config;

  /**
   * Cache backend instance to use.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * The file system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The manifest icons list.
   *
   * @var array
   */
  protected $icons;

  /**
   * Constructs a Manifest object.
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system.
   */
  public function __construct(ConfigFactoryInterface $config_factory, CacheBackendInterface $cache_backend, FileSystemInterface $file_system) {
    $this->config = $config_factory->get('vbase_manifest.settings');
    $this->cache = $cache_backend;
    $this->fileSystem = $file_system;
  }

  public function getIcons() {
    if (!isset($this->icons)) {
      $this->icons = [];
      $cache = $this->cache->get('vbase_manifest');
      $cache = FALSE;
      if (!$cache) {
        $files = $this->fileSystem->scanDirectory('public://manifest', '/.*/', ['key' => 'name']);
        if (!empty($files)) {
          $mimetype = \Drupal::service('file.mime_type.guesser');
          uksort($files, function($a, $b) {
            $a = filter_var($a, FILTER_SANITIZE_NUMBER_INT);
            $b = filter_var($b, FILTER_SANITIZE_NUMBER_INT);
            return $a > $b;
          });
          foreach ($files as $file) {
            $sizes = strtr($file->name, ['square' => '']);
            $this->icons[$file->filename] = [
              'uri' => $file->uri,
              'type' => $mimetype->guess($file->uri),
              'sizes' => $sizes ?: 'any',
            ];
          }
        }
        $this->cache->set('vbase_manifest', $this->icons, CacheBackendInterface::CACHE_PERMANENT);
      }
      else {
        $this->icons = $cache->data;
      }
    }
    return $this->icons;
  }

  public function getManifestIcons() {
    $allowed = [
      'square192x192.png',
      'square512x512.png',
    ];
    return array_intersect_key($this->getIcons(), array_flip($allowed));
  }

  public function setIconLinks(array &$attachments) {
    vbase_add_cacheable_dependency($attachments, $this->config);
    $icons = $this->getIcons();
    if (isset($icons['square180x180.png'])) {
      $attachments['#attached']['html_head_link'][] = [[
        'rel' => 'apple-touch-icon',
        'sizes' => '180x180',
        'href' => vbase_file_relative_url($icons['square180x180.png']['uri']),
      ]];
    }
    if (isset($icons['mask-icon.svg']) && $this->config->get('mask_icon_color')) {
      $attachments['#attached']['html_head_link'][] = [[
        'rel' => 'mask-icon',
        'href' => vbase_file_relative_url($icons['mask-icon.svg']['uri']),
        'color' => $this->config->get('mask_icon_color'),
      ]];
    }
    if (isset($icons['mstile-144x144.png'])) {
      $attachments['#attached']['html_head'][] = [[
        '#type' => 'html_tag',
        '#tag' => 'meta',
        '#attributes' => [
          'name' => 'msapplication-TileImage',
          'content' => file_create_url($icons['mstile-144x144.png']['uri']),
        ],
      ], 'msapplication-TileImage'];
    }
  }

}
