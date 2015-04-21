<?php

/**
 * @file
 * Contains \Drupal\media_entity_embeddable_video\EmbeddableVideoTypeInterface.
 */

namespace Drupal\media_entity_embeddable_video;

use Drupal\media_entity\MediaInterface;
use Drupal\media_entity\MediaTypeInterface;

/**
 * Defines the interface for embeddable video providers.
 */
interface EmbeddableVideoTypeInterface extends MediaTypeInterface {

  /**
   * Matches video provider.
   *
   * @param MediaInterface $media
   *   Media object.
   * @return \Drupal\media_entity_embeddable_video\VideoProviderInterface|bool
   *   Video provider or FALSE if no match.
   */
  public function matchProvider(MediaInterface $media);

}
