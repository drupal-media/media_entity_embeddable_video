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
    $response = $this->httpClient->get(
      Url::fromUri(
        'http://vimeo.com/api/v2/video/' . $this->matches['id'] . '.json?callback=showThumb'
      )->toString()
    );

    if ($response->getStatusCode() == 200 && ($data = $response->getBody())) {
      $data = preg_replace('/^\/\*\*\/showThumb\(\[/', '', $data);
      $data = preg_replace('/\]\)/', '', $data);
      $data = json_decode($data);
      return $data->thumbnail_medium;
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
