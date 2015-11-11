<?php

/**
 * Contains \Drupal\media_entity_embeddable_video\Plugin\MediaEntity\VideoProvider\AolPlaylist.
 */

namespace Drupal\media_entity_embeddable_video\Plugin\MediaEntity\VideoProvider;

use Drupal\Core\Config\Config;
use GuzzleHttp\Client;
use Drupal\Core\Url;
use Drupal\media_entity_embeddable_video\VideoProviderBase;
use Drupal\media_entity_embeddable_video\VideoProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides embedding support for AOL playlists.
 *
 * @EmbeddableVideoProvider(
 *   id = "aol_playlist",
 *   label = @Translation("AOL (5min) playlist"),
 *   description = @Translation("Provides embedding support for AOL playlists."),
 *   regular_expressions = {
 *     "@http://pshared.5min.com/Scripts/PlayerSeed\.js\?([^""']*)sid=(?<sid>[0-9]+)([^""']*)videoGroupID=(?<id>[0-9]+)([^""']*)@i"
 *   }
 * )
 */
class Aol extends VideoProviderBase implements VideoProviderInterface {

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
      'width' => '640',
      'height' => '480',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function thumbnailURI() {
    $response = $this->httpClient->get(
      Url::fromUri(
        'http://api.5min.com/video/list/info.xml',
        [
          'query' => [
            'sid' => $this->config->get('aol_sid'),
            'restriction' => 'no_html',
            'video_group_id' => $this->matches['id'],
          ]
        ]
      )->toString()
    );

    if ($response->getStatusCode() == 200 && ($data = $response->getBody())) {
      $data = simplexml_load_string($data);
      return $data->channel->item->image->url;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $query = [
      'videoGroupID' => $this->matches['id'],
      'width' => $this->configuration['width'],
      'height' => $this->configuration['height'],
      'sid' => $this->config->get('aol_sid') ? : $this->matches['sid']
    ];

    return [
      '#type' => 'html_tag',
      '#tag' => 'script',
      '#attributes' => [
        'type' => 'text/javascript',
        'src' => Url::fromUri('http://pshared.5min.com/Scripts/PlayerSeed.js', ['query' => $query])->toString(),
      ],
    ];
  }

}
