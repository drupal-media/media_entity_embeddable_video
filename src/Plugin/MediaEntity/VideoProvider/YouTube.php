<?php

/**
 * @file
 * Contains \Drupal\media_entity_embeddable_video\Plugin\MediaEntity\VideoProvider\YouTube.
 */

namespace Drupal\media_entity_embeddable_video\Plugin\MediaEntity\VideoProvider;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Url;
use Drupal\media_entity_embeddable_video\VideoProviderBase;
use Drupal\media_entity_embeddable_video\VideoProviderInterface;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides embedding support for YouTube videos.
 *
 * @EmbeddableVideoProvider(
 *   id = "youtube",
 *   label = @Translation("YouTube"),
 *   description = @Translation("Provides embedding support for YouTube videos."),
 *   regular_expressions = {
 *     "@(?<protocol>http|https)://www\.youtube(?<cookie>-nocookie)?\.com/embed/(?<id>[a-z0-9_-]+)@i",
 *     "@(?<protocol>http|https)://www\.youtube(?<cookie>-nocookie)?\.com/v/(?<id>[a-z0-9_-]+)@i",
 *     "@(?<protocol>http|https)://www\.youtube(?<cookie>-nocookie)?\.com/watch\?v=(?<id>[a-z0-9_-]+)@i",
 *     "@//www\.youtube(?<cookie>-nocookie)?\.com/embed/(?<id>[a-z0-9_-]+)@i",
 *     "@//www\.youtube(?<cookie>-nocookie)?\.com/v/(?<id>[a-z0-9_-]+)@i",
 *     "@//www\.youtube(?<cookie>-nocookie)?\.com/watch\?v=(?<id>[a-z0-9_-]+)@i"
 *   }
 * )
 */
class YouTube extends VideoProviderBase implements VideoProviderInterface {

  /**
   * Key used for accessing the YouTube API.
   *
   * @var string
   */
  protected $apiKey;

  /**
   * YouTube constructor.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \GuzzleHttp\Client $http_client
   *   The HTTP client.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Client $http_client, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $http_client);
    $this->apiKey = $config_factory->get('media_entity_embeddable_video.settings')->get('youtube.api_key');
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
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'video_quality' => 'HD720',
      'allowfullscreen' => TRUE,
      'autoplay' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function thumbnailURI() {
    $options = [
      'query' => [
        'part' => 'snippet',
        'id' => $this->matches['id'],
        'key' => $this->apiKey,
      ],
    ];
    $url = Url::fromUri('https://www.googleapis.com/youtube/v3/videos', $options)->toString();

    $response = json_decode($this->httpClient->get($url)->getBody(), TRUE);
    return $response['items'][0]['snippet']['thumbnails']['high']['url'];
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $args = [];
    $src = '';

    if (!empty($this->matches['protocol'])) {
      $src = $this->matches['protocol'] . ':';
    }

    $src .= '//www.youtube' . $this->matches['cookie'] . '.com/embed/' . $this->matches['id'];

    // Show suggested videos when the video finishes?
    if (preg_match('/rel=0/i', $this->configuration['embed_code'])) {
      $args['rel'] = '0';
    }

    $args['VQ'] = $this->configuration['video_quality'];
    if (!empty($this->configuration['allowfullscreen'])) {
      $args['allowfullscreen'] = 'true';
    }
    if (!empty($this->configuration['autoplay'])) {
      $args['autoplay'] = 'true';
    }

    $src .= '?' . implode('&', array_walk(
      $args,
      function (&$item, $key) {$item = $key . '=' . $item;}
    ));

    return [
      '#type' => 'html_tag',
      '#tag' => 'iframe',
      '#attributes' => [
        'src' => $src,
      ],
    ];
  }

}
