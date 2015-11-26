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
 *     "@(?:(?<protocol>http|https):)?//(?:www\.)?youtube(?<cookie>-nocookie)?\.com/embed/(?<id>[a-z0-9_-]+)@i",
 *     "@(?:(?<protocol>http|https):)?//(?:www\.)?youtube(?<cookie>-nocookie)?\.com/v/(?<id>[a-z0-9_-]+)@i",
 *     "@(?:(?<protocol>http|https):)?//(?:www\.)?youtube(?<cookie>-nocookie)?\.com/watch\?v=(?<id>[a-z0-9_-]+)@i",
 *     "@(?:(?<protocol>http|https):)?//youtu(?<cookie>-nocookie)?\.be/(?<id>[a-z0-9_-]+)@i"
 *   }
 * )
 */
class YouTube extends VideoProviderBase implements VideoProviderInterface {

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
    $maxres_thumb = 'http://img.youtube.com/vi/' . $this->matches['id'] . '/maxresdefault.jpg';

    try {
      /** @var \GuzzleHttp\Client $response */
      $this->httpClient->head($maxres_thumb);
    }
    catch (ClientException $e) {
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
