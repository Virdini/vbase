<?php

namespace Drupal\vbase;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\user\UserInterface;
use Drupal\Component\Transliteration\TransliterationInterface;

class Generator {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The transliteration helper.
   *
   * @var \Drupal\Component\Transliteration\TransliterationInterface
   */
  protected $transliteration;

  /**
   * Constructs a \Drupal\vbase\Generator object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Component\Transliteration\TransliterationInterface $transliteration
   *   The transliteration helper.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, TransliterationInterface $transliteration) {
    $this->entityTypeManager = $entity_type_manager;
    $this->transliteration = $transliteration;
  }

  /**
   * Generate a non-existent username.
   *
   * @param string $prefix
   *   The username prefix.
   *
   * @return string
   *   The username.
   */
  public function userName(string $prefix = '') {
    $name = $prefix . bin2hex(random_bytes(6));

    return $this->makeUniqueName($name, FALSE);
  }

  /**
   * Generate a non-existent username based on mail.
   *
   * @param string $mail
   *   The user email.
   *
   * @return string
   *   The username.
   */
  public function userNameFromMail(string $mail) {
    return $this->makeUniqueName(str_replace('@', '_', $mail));
  }

  /**
   * Iterate until find a non-existent username.
   *
   * @param string $name
   *   The username.
   * @param bool $clean
   *   True, if remove unwanted characters.
   *
   * @return string
   *   The non-existent username.
   */
  public function makeUniqueName(string $name, $clean = TRUE) {
    if ($clean) {
      $name = $this->cleanName($name);
      
      if (mb_strlen($name) < 3) {
        $name = 'user_' . bin2hex(random_bytes(5));
      }
    }

    $storage = $this->entityTypeManager->getStorage('user');
    $i = 0;

    do {
      $new_name = $i ? mb_substr($name, 0, UserInterface::USERNAME_MAX_LENGTH - strlen($i)) . $i : $name;
      $query = $storage->getQuery()->condition('name', $new_name);
      $i++;
    } while (!empty($query->execute()));

    return $new_name;
  }

  /**
   * Remove unwanted characters.
   *
   * @param string $name
   *   The username.
   *
   * @return string
   *   The cleaned username.
   */
  public function cleanName(string $name) {
    // Force lowercase and transliterate.
    $name = $this->transliteration->transliterate(mb_strtolower($name), 'en', '', UserInterface::USERNAME_MAX_LENGTH);

    // Remove remaining unsafe characters.
    $name = preg_replace('/[^0-9a-z_.-]/', '', $name);

    return $name;
  }

}
