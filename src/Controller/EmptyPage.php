<?php

namespace Drupal\vbase\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Provides a 'Empty'
 */
class EmptyPage extends ControllerBase {

  public function build() {
    return [];
  }

}
