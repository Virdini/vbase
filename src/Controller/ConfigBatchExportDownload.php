<?php

namespace Drupal\vbase\Controller;

use Drupal\system\FileDownloadController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Config file download controller.
 */
class ConfigBatchExportDownload extends FileDownloadController {

  /**
   * {@inheritdoc}
   */
  public function download(Request $request, $scheme = 'private') {
    return parent::download(new Request(['file' => 'config.tar.gz']), 'temporary');
  }

}
