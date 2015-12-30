<?php

/**
 * Contains \Drupal\media_entity_embeddable_video\Plugin\MediaEntity\VideoProvider\Vimeo.
 */

namespace Drupal\media_entity_embeddable_video\Plugin\MediaEntity\VideoProvider;

use Drupal\Core\Url;
use Drupal\media_entity_embeddable_video\VideoProviderBase;
use Drupal\media_entity_embeddable_video\VideoProviderInterface;
use GuzzleHttp\Exception\ClientException;

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
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'width' => '640',
      'height' => '480',
      'autoplay' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function thumbnailURI() {
    $headers = [];
    if ($token = \Drupal::config('media_entity_embeddable_video.settings')->get('vimeo.app_access_token')) {
      $headers['Authorization'] = 'bearer ' . $token;
    }
    $response = $this->httpClient->get(
      Url::fromUri(
        'https://api.vimeo.com/videos/' . $this->matches['id'] . '/pictures'
      )->toString(),
      [ 'headers' => $headers ]
    );
    if ($response->getStatusCode() == 200 && ($data = $response->getBody())) {
      return json_decode($data)->data[0]->sizes[4]->link; // 960x720
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
        'height' => $this->configuration['height']
      ],
    ];
  }

}
