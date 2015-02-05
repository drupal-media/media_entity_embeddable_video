<?php

/**
 * Contains \Drupal\media_entity_embeddable_video\Plugin\MediaEntity\VideoProvider\GrabNetworks.
 */

namespace Drupal\media_entity_embeddable_video\Plugin\MediaEntity\VideoProvider;

use Drupal\media_entity_embeddable_video\VideoProviderBase;
use Drupal\media_entity_embeddable_video\VideoProviderInterface;

/**
 * Provides embedding support for Grab videos.
 *
 * @EmbeddableVideoProvider(
 *   id = "grab",
 *   label = @Translation("Grab networks"),
 *   description = @Translation("Provides embedding support for Grab videos."),
 *   regular_expressions = {
 *     "@http://player\.grabnetworks\.com/swf/GrabOSMFPlayer\.swf\?id=(?<id>[0-9]+)&content=v([a-f0-9]+)@i",
 *     "@http://player\.grabnetworks\.com/js/Player\.js\?([^""']*)id=(?<id>[0-9]+)([^""']*)&content=(v?[a-f0-9]+)([^""']*)@i"
 *   }
 * )
 */
class GrabNetworks extends VideoProviderBase implements VideoProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function thumbnailURI() {

  }

  /**
   * {@inheritdoc}
   */
  public static function matchEmbed($embed_code) {

  }
}
