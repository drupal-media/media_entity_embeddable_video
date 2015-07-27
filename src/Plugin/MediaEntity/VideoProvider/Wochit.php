<?php

/**
 * Contains \Drupal\media_entity_embeddable_video\Plugin\MediaEntity\VideoProvider\Wochit.
 */

namespace Drupal\media_entity_embeddable_video\Plugin\MediaEntity\VideoProvider;

use Drupal\Core\Config\Config;
use GuzzleHttp\Client;
use Drupal\Core\Url;
use Drupal\media_entity_embeddable_video\VideoProviderBase;
use Drupal\media_entity_embeddable_video\WochitProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides embedding support for Wochit videos.
 *
 * @EmbeddableVideoProvider(
 *   id = "wochit",
 *   label = @Translation("Wochit"),
 *   description = @Translation("Provides embedding support for Wochit videos."),
 *   regular_expressions = {
 *     "@http://api\.wochit\.com/api/wochitplayer.js\?code=(?<id>[0-9a-zA-Z=\%]+)@i",
 *   }
 * )
 */
class Wochit extends VideoProviderBase implements WochitProviderInterface {

  /**
   * Wochit video UID.
   *
   * @var string|false
   */
  protected $wochitUid = NULL;

  /**
   * Config object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Client $http_client, Config $config) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $http_client);
    $this->config = $config;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('http_client'),
      $container->get('config.factory')->get('media_entity_embeddable_video.settings')
    );
  }
  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'height' => '640',
      'width' => '360',
      'autostart' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function thumbnailURI() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $src_options = [
      'query' => [
        'code' => $this->videoId(),
        'autostart' => $this->configuration['autostart'] ? 'true' : 'false',
        'width' => $this->configuration['width'],
        'height' => $this->configuration['height'],
      ],
    ];

    if ($progn = $this->config->get('wochit_progn')) {
      $src_options['query']['progn'] = $progn;
    }

    $src = Url::fromUri('http://api.wochit.com/api/wochitplayer.js', $src_options);
    $render = [
      '#type' => 'html_tag',
      '#tag' => 'script',
      '#attributes' => [
        'language' => 'javascript',
        'type' => 'text/javascript',
        'src' => $src->toString(),
      ],
    ];

    if ($uid = $this->wochitUid()) {
      $render['#attributes']['data-wochit-uid'] = $uid;
    }

    return $render;
  }

  /**
   * {@inheritdoc}
   */
  public function wochitUid() {
    if ($this->wochitUid === NULL) {
      $this->wochitUid = FALSE;
      if (preg_match('/data-wochit-uid=\'(?<uid>[a-z0-9]+)\'/', $this->embedCode, $wochit_uid)) {
        $this->wochitUid = $wochit_uid['uid'];
      }
    }

    return $this->wochitUid;
  }

}
