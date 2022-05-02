<?php

namespace Drupal\vbase\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Component\Transliteration\TransliterationInterface;
use Drupal\Core\File\Event\FileUploadSanitizeNameEvent;

/**
 * The subscriber to 'file.upload.sanitize.name'.
 */
class FileUploadEventSubscriber implements EventSubscriberInterface {

  /**
   * The transliteration helper.
   *
   * @var \Drupal\Component\Transliteration\TransliterationInterface
   */
  protected $transliteration;

  /**
   * Constructs a new file event listener.
   *
   * @param \Drupal\Component\Transliteration\TransliterationInterface $transliteration
   *   The transliteration helper.
   */
  public function __construct(TransliterationInterface $transliteration) {
    $this->transliteration = $transliteration;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[FileUploadSanitizeNameEvent::class][] = ['sanitizeName'];

    return $events;
  }

  /**
   * Transliterate the upload's filename.
   *
   * @param \Drupal\Core\File\Event\FileUploadSanitizeNameEvent $event
   *   File upload sanitize name event.
   */
  public function sanitizeName(FileUploadSanitizeNameEvent $event): void {
    $filename = $event->getFilename();

    // Transliterate and sanitize the destination filename.
    $filename_fixed = $this->transliteration->transliterate($filename, 'en', '');

    // Replace whitespace.
    $filename_fixed = str_replace(' ', '_', $filename_fixed);

    // Remove remaining unsafe characters.
    $filename_fixed = preg_replace('/[^0-9A-Za-z_.-]/', '', $filename_fixed);

    // Remove multiple consecutive non-alphabetical characters.
    $filename_fixed = preg_replace('/(_)_+|(\.)\.+|(-)-+/', '\\1\\2\\3', $filename_fixed);

    // Force lowercase to prevent issues on case-insensitive file systems.
    $filename_fixed = mb_strtolower($filename_fixed);

    if ($filename != $filename_fixed) {
      $event->setFilename($filename_fixed);
    }
  }

}
