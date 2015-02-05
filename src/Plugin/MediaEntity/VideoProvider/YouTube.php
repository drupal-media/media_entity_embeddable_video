<?php

/**
 * Contains \Drupal\media_entity_embeddable_video\Plugin\MediaEntity\VideoProvider\YouTube.
 */

namespace Drupal\media_entity_embeddable_video\Plugin\MediaEntity\VideoProvider;

use Drupal\media_entity_embeddable_video\VideoProviderBase;
use Drupal\media_entity_embeddable_video\VideoProviderInterface;
use GuzzleHttp\Exception\ClientException;

/**
 * Provides embedding support for YouTube videos.
 *
 * @EmbeddableVideoProvider(
 *   id = "youtube",
 *   label = @Translation("YouTube"),
 *   description = @Translation("Provides embedding support for YouTube videos."),
 *   regular_expressions = {
 *     "@(http|https)://www\.youtube(-nocookie)?\.com/embed/(?<id>[a-z0-9_-]+)@i",
 *     "@(http|https)://www\.youtube(-nocookie)?\.com/v/(?<id>[a-z0-9_-]+)@i",
 *     "@//www\.youtube(-nocookie)?\.com/embed/(?<id>[a-z0-9_-]+)@i",
 *     "@//www\.youtube(-nocookie)?\.com/v/(?<id>[a-z0-9_-]+)@i"
 *   }
 * )
 */
class YouTube extends VideoProviderBase implements VideoProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function thumbnailURI() {
    $maxres_thumb = 'http://img.youtube.com/vi/' . $this->matches['id'] . '/maxresdefault.jpg';

    try {
      /** @var \GuzzleHttp\Message\ResponseInterface $response */
      $this->httpClient->head($maxres_thumb);
    } catch (ClientException $e) {
      $size = 0;
      $xml = simplexml_load_file('http://gdata.youtube.com/feeds/api/videos/' . $this->matches['id']);
      foreach ($xml->children('media', TRUE)->group->thumbnail as $thumb) {
        if ($size < (int) $thumb->attributes()->width) {
          $size = (int) $thumb->attributes()->width;
          $maxres_thumb = (string) $thumb->attributes()->url;
        }
      }
    }

    return $maxres_thumb;
  }

}
