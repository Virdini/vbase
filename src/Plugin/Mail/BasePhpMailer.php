<?php

namespace Drupal\vbase\Plugin\Mail;

use Drupal\Core\Mail\MailInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\File\FileSystemInterface;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use Drupal\Core\Mail\MailFormatHelper;
use Drupal\Component\Utility\Html;

/**
 * Implements the base PHPMailer class for the Drupal MailInterface.
 *
 * @Mail(
 *   id = "vbase_phpmailer",
 *   label = @Translation("Virdini base PHPMailer"),
 *   description = @Translation("Sends emails using the PHPMailer library.")
 * )
 */
class BasePhpMailer implements MailInterface, ContainerFactoryPluginInterface {

  /**
   * The factory for configuration objects.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The logger channel factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The file system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Constructs a \Drupal\vbase\Plugin\Mail\BasePhpMailer object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger channel factory.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LoggerChannelFactoryInterface $logger_factory, RendererInterface $renderer, FileSystemInterface $file_system) {
    $this->configFactory = $config_factory;
    $this->loggerFactory = $logger_factory;
    $this->renderer = $renderer;
    $this->fileSystem = $file_system;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('config.factory'),
      $container->get('logger.factory'),
      $container->get('renderer'),
      $container->get('file_system')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function format(array $message) {
    if ($this->isRenderable($message['body'])) {
      // Render body
      $message['html'] = $this->renderer->render($message['body']);
      // Attempt to convert relative URLs to absolute.
      $message['html'] = Html::transformRootRelativeUrlsToAbsolute((string) $message['html'], \Drupal::request()->getSchemeAndHttpHost());
      $message['body'] = $message['html'];
    }
    else {
      // Join the body array into one string.
      $message['body'] = implode("\n\n", $message['body']);
    }
    // Convert any HTML to plain-text.
    $message['body'] = MailFormatHelper::htmlToText($message['body']);
    return $message;
  }

  /**
   * {@inheritdoc}
   */
  public function mail(array $message) {
    try {
      $mailer = new PHPMailer(TRUE);
      $mailer->CharSet = PHPMailer::CHARSET_UTF8;
      $mailer->Subject = $message['subject'];
      // Add body
      if (isset($message['html'])) {
        $mailer->isHTML(TRUE);
        $mailer->Body = $message['html'];
        if (!empty($message['body'])) {
          $mailer->AltBody = $message['body'];
        }
      }
      else {
        $mailer->Body = $message['body'];
      }

      // Attachments
      if (isset($message['params']['attachments']) && is_array($message['params']['attachments'])) {
        foreach ($message['params']['attachments'] as $attachment) {
          if (is_array($attachment)) {
            // Set defaults
            $attachment += [
              'filename' => '',
              'filemime' => '',
              'encoding' => PHPMailer::ENCODING_BASE64,
              'disposition' => 'attachment',
            ];
            // Add an attachment from a path on the filesystem
            if (isset($attachment['filepath']) && ($filepath = $this->fileSystem->realpath($attachment['filepath']))) {
              $mailer->addAttachment($filepath, $attachment['filename'], $attachment['encoding'], $attachment['filemime'], $attachment['disposition']);
            }
            // Add a string or binary attachment (non-filesystem)
            elseif (isset($attachment['filecontent']) && $attachment['filename']) {
              $mailer->AddStringAttachment($attachment['filecontent'], $attachment['filename'], $attachment['encoding'], $attachment['filemime'], $attachment['disposition']);
            }
          }
          // Add an attachment from a path on the filesystem
          elseif (is_string($attachment) && ($filepath = $this->fileSystem->realpath($attachment))) {
            $mailer->addAttachment($filepath);
          }
        }
      }

      // Convert headers to lowercase.
      $headers = array_change_key_case($message['headers']);
      unset($message['headers']);

      // This is always set by PHPMailer.
      unset($headers['mime-version'], $headers['x-mailer'], $headers['content-transfer-encoding'], $headers['content-type'], $headers['return-path']);

      // Send messages using SMTP server if enabled
      $smtp = $this->configFactory->get('vbase.settings.smtp');
      if ($smtp->get('enabled')) {
        $mailer->isSMTP();
        $mailer->Host = $smtp->get('host');
        if ($smtp->get('username') && $smtp->get('password')) {
          $mailer->SMTPAuth = TRUE;
          $mailer->Username = $smtp->get('username');
          $mailer->Password = $smtp->get('password');
        }
        if ($smtp->get('from')) {
          unset($headers['from'], $headers['sender']);
          $from = $mailer->parseAddresses($smtp->get('from'), TRUE, $mailer->CharSet);
          $from = reset($from);
          $mailer->setFrom($from['address'], $from['name'], TRUE);
        }
      }

      // Extract 'From' from headers.
      if (isset($headers['from'])) {
        $from = $mailer->parseAddresses($headers['from'], TRUE, $mailer->CharSet);
        $from = reset($from);
        $mailer->setFrom($from['address'], $from['name']);
        unset($headers['from']);
      }

      // Extract 'Sender' from headers.
      if (isset($headers['sender'])) {
        $sender = $mailer->parseAddresses($headers['sender'], TRUE, $mailer->CharSet);
        $sender = reset($sender);
        $this->Sender = $sender['address'];
        unset($headers['sender']);
      }

      // Extract 'Reply-To' from headers.
      if (isset($headers['reply-to'])) {
        foreach ($mailer->parseAddresses($headers['reply-to']) as $address) {
          $mailer->AddReplyTo($address['address'], $address['name']);
        }
        unset($headers['reply-to']);
      }

      // Set recipients.
      foreach ($mailer->parseAddresses($message['to']) as $address) {
        $mailer->AddAddress($address['address'], $address['name']);
      }

      // Extract 'CC' from headers.
      if (isset($headers['cc'])) {
        foreach ($mailer->parseAddresses($headers['cc']) as $address) {
          $mailer->AddCC($address['address'], $address['name']);
        }
        unset($headers['cc']);
      }

      // Extract 'BCC' from headers.
      if (isset($headers['bcc'])) {
        foreach ($mailer->parseAddresses($headers['bcc']) as $address) {
          $mailer->AddBCC($address['address'], $address['name']);
        }
        unset($headers['bcc']);
      }

      // Add remaining header lines.
      // Note: Any header lines MUST already be checked by the caller for
      // unwanted newline characters to avoid header injection.
      // @see PHPMailer::SecureHeader()
      foreach ($headers as $key => $value) {
        $mailer->AddCustomHeader($key, $value);
      }

      // Send mail
      return $mailer->send();
    }
    catch (PHPMailerException $e) {
      $this->loggerFactory->get('vbase_phpmailer')->error($e->errorMessage());
      return FALSE;
    }
    return $message;
  }

  /**
   * Check if array is renderable.
   *
   * @see \Drupal\Core\Render\Element::children()
   */
  protected function isRenderable(array $elements) {
    foreach ($elements as $key => $value) {
      if (is_int($key) || $key === '' || $key[0] !== '#') {
        if (is_array($value)) {
          return $this->isRenderable($value);
        }
        // Only trigger an error if the value is not null.
        // @see https://www.drupal.org/node/1283892
        elseif (isset($value)) {
          return FALSE;
        }
      }
    }
    return TRUE;
  }

}
