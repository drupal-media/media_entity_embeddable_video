<?php

/**
 * @file
 * Contains \Drupal\media_entity_embeddable_video\WochitProviderInterface.
 */

namespace Drupal\media_entity_embeddable_video;

/**
 * Defines the interface for embeddable video providers.
 */
interface WochitProviderInterface extends VideoProviderInterface {

  /**
   * Gets Wochit video UID.
   *
   * @return string
   *   Video UID.
   */
  public function wochitUid();
}
