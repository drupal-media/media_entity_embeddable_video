<?php

/**
 * @file
 * Contains \Drupal\media_entity_embeddable_video\Plugin\MediaEntity\VideoProvider\YouTube.
 */

namespace Drupal\media_entity_embeddable_video\Plugin\MediaEntity\VideoProvider;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Url;
use Drupal\media_entity_embeddable_video\VideoProviderBase;
use Drupal\media_entity_embeddable_video\VideoProviderInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides embedding support for YouTube videos.
 *
 * @EmbeddableVideoProvider(
 *   id = "youtube",
 *   label = @Translation("YouTube"),
 *   description = @Translation("Provides embedding support for YouTube videos."),
 *   regular_expressions = {
 *     "@(?:(?<protocol>http|https):)?//(?:www\.)?youtube(?<cookie>-nocookie)?\.com/embed/(?<id>[a-z0-9_-]+)@i",
 *     "@(?:(?<protocol>http|https):)?//(?:www\.)?youtube(?<cookie>-nocookie)?\.com/v/(?<id>[a-z0-9_-]+)@i",
 *     "@(?:(?<protocol>http|https):)?//(?:www\.)?youtube(?<cookie>-nocookie)?\.com/watch(\?|\?.*\&)v=(?<id>[a-z0-9_-]+)@i",
 *     "@(?:(?<protocol>http|https):)?//youtu(?<cookie>-nocookie)?\.be/(?<id>[a-z0-9_-]+)@i"
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
   * The logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $log;

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
   * @param \Drupal\Core\Logger\LoggerChannelInterface $log
   *   The logger channel.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Client $http_client, ConfigFactoryInterface $config_factory, LoggerChannelInterface $log) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $http_client);
    $this->apiKey = $config_factory->get('media_entity_embeddable_video.settings')->get('youtube.api_key');
    $this->log = $log;
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
      $container->get('config.factory'),
      $container->get('logger.factory')->get('media_entity')
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
    $thumbnail = 'http://img.youtube.com/vi/' . $this->matches['id'] .'/hqdefault.jpg';

    if ($this->apiKey) {
      $options = [
        'query' => [
          'part' => 'snippet',
          'id' => $this->matches['id'],
          'key' => $this->apiKey,
        ],
      ];
      $url = Url::fromUri('https://www.googleapis.com/youtube/v3/videos', $options)->toString();

      try {
        $response = $this->httpClient->get($url, ['http_errors' => TRUE]);
        $response = json_decode($response->getBody(), TRUE);
        return $response['items'][0]['snippet']['thumbnails']['high']['url'];
      }
      catch (ClientException $e) {
        $this->log->error($e->getMessage());
        return $thumbnail;
      }
    }
    else {
      return $thumbnail;
    }
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

    array_walk(
      $args,
      function (&$item, $key) {$item = $key . '=' . $item;}
    );

    $src .= '?' . implode('&', $args);

    return [
      '#type' => 'html_tag',
      '#tag' => 'iframe',
      '#attributes' => [
        'src' => $src,
      ],
    ];
  }

}
