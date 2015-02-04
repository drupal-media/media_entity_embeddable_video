<?php

/**
 * @file
 * Contains \Drupal\media_entity_embeddable_video\VideoProviderInterface.
 */

namespace Drupal\media_entity_embeddable_video;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines the interface for embeddable video providers.
 */
interface VideoProviderInterface extends PluginInspectionInterface {

  /**
   * Gets video source ID.
   *
   * @return string
   *   Source ID.
   */
  public function id();

  /**
   * Gets video source name.
   *
   * @return string
   *   Source name.
   */
  public function name();

  /**
   * Gets video ID.
   *
   * @return string
   *   Video ID.
   */
  public function videoId();

  /**
   * Video thumbnail URI.
   *
   * @return string
   *   Video thumbnail.
   */
  public function thumbnailURI();

  /**
   * Matches embed code to provider and returns provider object.
   *
   * @param string $embed_code
   *   Video embed code.
   *
   * @return VideoProviderInterface|false
   *   Video provider object or FALSE if no match.
   */
  public static function matchEmbed($embed_code);

}
