<?php

/**
 * Contains \Drupal\media_entity_embeddable_video\Plugin\MediaEntity\VideoProvider\Vimeo.
 */

namespace Drupal\media_entity_embeddable_video\Plugin\MediaEntity\VideoProvider;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Url;
use Drupal\media_entity_embeddable_video\VideoProviderBase;
use Drupal\media_entity_embeddable_video\VideoProviderInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides embedding support for Vimeo videos.
 *
 * @EmbeddableVideoProvider(
 *   id = "vimeo",
 *   label = @Translation("Vimeo"),
 *   description = @Translation("Provides embedding support for Vimeo videos."),
 *   regular_expressions = {
 *     "@vimeo\.com/moogaloop\.swf\?clip_id=(?<id>[^""'\&]+)@i",
 *     "@vimeo\.com/[^""'\&\d]*(?<id>[^""'\&]+)@i"
 *   }
 * )
 */
class Vimeo extends VideoProviderBase implements VideoProviderInterface {

  /**
   * Access token used for accessing the Vimeo API.
   *
   * @var string
   */
  protected $accessToken;

  /**
   * Vimeo constructor.
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
    $this->accessToken = $config_factory->get('media_entity_embeddable_video.settings')->get('vimeo.access_token');
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
      'width' => '640',
      'height' => '480',
      'allowfullscreen' => TRUE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function thumbnailURI() {
    $headers = [];
    if (!empty($this->accessToken)) {
      $headers['Authorization'] = 'bearer ' . $this->accessToken;
    }
    $response = $this->httpClient->get(
      Url::fromUri(
        'https://api.vimeo.com/videos/' . $this->matches['id'] . '/pictures'
      )->toString(),
      [ 'headers' => $headers ]
    );
    if ($response->getStatusCode() == 200 && ($data = $response->getBody())) {
      $sizes = json_decode($data)->data[0]->sizes;
      $largest = count($sizes) - 1;
      return $sizes[$largest]->link;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    return [
      '#type' => 'html_tag',
      '#tag' => 'iframe',
      '#attributes' => [
        'src' => '//player.vimeo.com/video/' . $this->matches['id'],
        'width' => $this->configuration['width'],
        'height' => $this->configuration['height'],
        'mozallowfullscreen' => $this->configuration['allowfullscreen'],
        'webkitallowfullscreen' => $this->configuration['allowfullscreen'],
        'allowfullscreen' => $this->configuration['allowfullscreen']
      ],
    ];
  }

}
