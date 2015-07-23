<?php

/**
 * Contains \Drupal\media_entity_embeddable_video\Plugin\MediaEntity\VideoProvider\GrabNetworks.
 */

namespace Drupal\media_entity_embeddable_video\Plugin\MediaEntity\VideoProvider;

use Drupal\Core\Config\Config;
use GuzzleHttp\Client;
use Drupal\Core\Url;
use Drupal\media_entity_embeddable_video\VideoProviderBase;
use Drupal\media_entity_embeddable_video\VideoProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides embedding support for Grab videos.
 *
 * @EmbeddableVideoProvider(
 *   id = "grab",
 *   label = @Translation("Grab networks"),
 *   description = @Translation("Provides embedding support for Grab videos."),
 *   regular_expressions = {
 *     "@http://player\.grabnetworks\.com/swf/GrabOSMFPlayer\.swf\?id=(?<publisher_id>[0-9]+)&content=v(?<id>[a-f0-9]+)@i",
 *     "@http://player\.grabnetworks\.com/js/Player\.js\?([^""']*)id=(?<publisher_id>[0-9]+)([^""']*)&content=(?<id>v?[a-f0-9]+)([^""']*)@i"
 *   }
 * )
 */
class GrabNetworks extends VideoProviderBase implements VideoProviderInterface {

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
        'http://content.grabnetworks.com/v/' . $this->matches['id'],
        ['query' => ['from' => $this->config->get('grab_from')]]
      )
    );

    if ($response->getStatusCode() == 200 && ($data = $response->json())) {
      return $data['video']['media']['preview']['url'];
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $query = [
      'id' => $this->config->get('grab_id') ? : $this->matches['publisher_id'],
      'content' => $this->matches['content'],
      'width' => $this->configuration['width'],
      'height' => $this->configuration['height'],
      'tgt' => 'grabnetworks',
    ];

    return [
      '#type' => 'html_tag',
      '#tag' => 'script',
      '#attributes' => [
        'type' => 'text/javascript',
        'src' => Url::fromUri('http://player.grabnetworks.com/js/Player.js', ['query' => $query])->toString(),
      ],
    ];
  }

}
