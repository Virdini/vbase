<?php

use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\field\FieldStorageConfigInterface;

/**
 * Implements hook_token_info().
 */
function vbase_token_info() {
  $info = [
    'tokens' => [
      'term' => [
        'virdini-parent-alias' => [
          'name' => t('Parent term alias'),
          'description' => t('The parent term alias of the taxonomy term, if one exists.'),
          'type' => 'alias',
        ],
      ],
    ],
  ];

  if ($fields = \Drupal::service('entity_field.manager')->getFieldStorageDefinitions('node')) {
    foreach ($fields as $field_name => $field) {
      if ($field instanceof FieldStorageConfigInterface && $field->getType() == 'entity_reference' && $field->getSetting('target_type') == 'taxonomy_term') {
        $info['tokens']['virdini-term-alias'][$field_name . ':alias'] = [
          'name' => $field_name,
          'description' => t('@field_name field.', ['@field_name' => $field_name]),
          'type' => 'alias',
        ];
      }
    }

    if (isset($info['tokens']['virdini-term-alias'])) {
      $info['types']['virdini-term-alias'] = [
        'name' => t('Virdini term alias'),
        'description' => t('Tokens from node reference fields.'),
        'needs-data' => 'node',
      ];
      $info['tokens']['node']['virdini-term-alias'] = [
        'name' => t('Virdini term alias'),
        'description' => t('Tokens from node reference fields.'),
        'type' => 'virdini-term-alias',
      ];
    }
  }

  return $info;
}

/**
 * Implements hook_tokens().
 */
function vbase_tokens($type, array $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $replacements = [];
  $langcode = isset($options['langcode']) ? $options['langcode'] : LanguageInterface::LANGCODE_DEFAULT;

  if ($type == 'node' && !empty($data['node'])) {
    foreach ($tokens as $name => $original) {
      $parts = explode(':', $name);

      switch ($parts[0]) {
        case 'virdini-term-alias':
          $entity = \Drupal::service('entity.repository')->getTranslationFromContext($data['node'], $langcode, ['operation' => 'node_tokens']);

          if ($entity->hasField($parts[1]) && !$entity->get($parts[1])->isEmpty()) {
            $term = $entity->get($parts[1])->first()->entity;
            $bubbleable_metadata->addCacheableDependency($term);
            $path = '/'. $term->toUrl()->getInternalPath();
            $alias = \Drupal::service('path_alias.manager')->getAliasByPath($path, $langcode);

            if ($path != $alias) {
              $replacements[$original] = ltrim($alias, '/');
            }
          }
          break;
      }
    }
  }
  elseif ($type == 'term' && !empty($data['term'])) {
    foreach ($tokens as $name => $original) {
      switch ($name) {
        case 'virdini-parent-alias':
          if ($parents = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadParents($data['term']->id())) {
            $term = array_pop($parents);
            $bubbleable_metadata->addCacheableDependency($term);
            $path = '/'. $term->toUrl()->getInternalPath();
            $alias = \Drupal::service('path_alias.manager')->getAliasByPath($path, $langcode);

            if ($path != $alias) {
              $replacements[$original] = ltrim($alias, '/');
              $pattern = \Drupal::service('pathauto.generator')->getPatternByEntity($term)->getPattern();

              if (preg_match('/[^\[]*/', $pattern, $matches) && ($prefix = $matches[0])) {
                $replacements[$original] = substr($replacements[$original], strlen($prefix));
              }
            }
          }
          break;
      }
    }
  }

  return $replacements;
}

/**
 * Implements hook_tokens_alter().
 */
function vbase_tokens_alter(array &$replacements, array $context, BubbleableMetadata $bubbleable_metadata) {
  if ($context['type'] == 'node' && !empty($context['data']['node'])) {
    if (isset($context['tokens']['title']) && !$replacements[$context['tokens']['title']]) {
      $replacements[$context['tokens']['title']] = \Drupal::service('token')->replace('[current-page:title]');
    }
  }
  elseif ($context['type'] == 'current-page' && isset($context['tokens']['title'])) {
    $route = \Drupal::routeMatch();
    $route_name = $route->getRouteName();

    if (substr($route_name, 0, 5) == 'view.') {
      $view = \Drupal::entityTypeManager()->getStorage('view')->load($route->getParameter('view_id'))->getExecutable();
      $view->setDisplay($route->getParameter('display_id'));
      $replacements[$context['tokens']['title']] = $view->getTitle();
    }
    elseif(!$replacements[$context['tokens']['title']] && preg_match('/entity\.(.*)\.(.*)/', $route_name, $matches)) {
      switch ($matches[2]) {
        case 'edit_form':
          $replacements[$context['tokens']['title']] = t('Edit');
          break;

        case 'content_translation_overview':
          $replacements[$context['tokens']['title']] = t('Translations');
          break;
      }
    }
  }
}
