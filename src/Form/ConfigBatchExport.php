<?php

namespace Drupal\vbase\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Archiver\ArchiveTar;
use Drupal\Core\Serialization\Yaml;
use Drupal\Core\Link;
use Drupal\Core\File\Exception\FileException;

class ConfigBatchExport extends FormBase {

  /**
   * Batch Builder.
   *
   * @var \Drupal\Core\Batch\BatchBuilder
   */
  protected $batchBuilder;

  /**
   * The file system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The export storage.
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $exportStorage;

  /**
   * Constructs a ConfigBatchExportForm object.
   *
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system.
   * @param \Drupal\Core\Config\StorageInterface $export_storage
   *   The export storage.
   */
  public function __construct(FileSystemInterface $file_system, StorageInterface $export_storage) {
    $this->batchBuilder = new BatchBuilder();
    $this->fileSystem = $file_system;
    $this->exportStorage = $export_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('file_system'),
      $container->get('config.storage.export')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'vbase_config_batch_export';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Export'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    try {
      $this->fileSystem->delete($this->fileSystem->getTempDirectory() . '/config.tar.gz');
    }
    catch (FileException $e) {
      // Ignore failed deletes.
    }

    $configs = [];

    // Get configuration names.
    foreach ($this->exportStorage->listAll() as $name) {
      $configs[] = [
        'name' => $name,
      ];
    }

    // Get configuration names from the remaining collections.
    foreach ($this->exportStorage->getAllCollectionNames() as $collection) {
      $collection_storage = $this->exportStorage->createCollection($collection);
      foreach ($collection_storage->listAll() as $name) {
        $configs[] = [
          'name' => $name,
          'collection' => $collection,
        ];
      }
    }

    // Build batch
    $this->batchBuilder
      ->setTitle($this->t('Exporting the full configuration'))
      ->setInitMessage($this->t('Initializing.'))
      ->setProgressMessage($this->t('Completed @current of @total.'))
      ->setErrorMessage($this->t('An error has occurred.'));
    $this->batchBuilder->setFile(drupal_get_path('profile', 'vbase') . '/src/Form/ConfigBatchExport.php');
    $this->batchBuilder->addOperation([$this, 'processItems'], [$configs]);
    $this->batchBuilder->setFinishCallback([$this, 'finished']);
    batch_set($this->batchBuilder->toArray());
  }

  /**
   * Processor for batch operations.
   */
  public function processItems($items, array &$context) {
    // Set default progress values.
    if (empty($context['sandbox']['progress'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['max'] = count($items);
    }

    $archiver = new ArchiveTar($this->fileSystem->getTempDirectory() . '/config.tar.gz', 'gz');

    for ($i = 0; $i < 30; $i++) {
      $index = $context['sandbox']['progress'];

      if (!isset($items[$index])) {
        break;
      }

      $name = $items[$index]['name'];
      $context['message'] = $name;

      // Get raw configuration data without overrides.
      if (!isset($items[$index]['collection'])) {
        $archiver->addString("$name.yml", Yaml::encode($this->exportStorage->read($name)));
      }
      // Get all override data from the remaining collections.
      else {
        $collection = $items[$index]['collection'];
        $collection_storage = $this->exportStorage->createCollection($collection);
        $archiver->addString(str_replace('.', '/', $collection) . "/$name.yml", Yaml::encode($collection_storage->read($name)));
      }

      $context['sandbox']['progress']++;

      // If not finished all tasks, we count percentage of process. 1 = 100%.
      if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
        $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
      }
    }
  }

  /**
   * Finished callback for batch.
   */
  public function finished($success, $results, $operations) {
    if ($success) {
      $link = Link::createFromRoute('Download', 'vbase.config.download')->toRenderable();
      $this->messenger()->addStatus($link);
    }
  }

}
