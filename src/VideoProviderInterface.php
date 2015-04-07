<?php

/**
 * @file
 * Contains \Drupal\media_entity_embeddable_video\VideoProviderInterface.
 */

namespace Drupal\media_entity_embeddable_video;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines the interface for embeddable video providers.
 */
interface VideoProviderInterface extends PluginInspectionInterface, ConfigurablePluginInterface {

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
   * @return string|false
   *   Video thumbnail URI or FALSE if couldn't find one.
   */
  public function thumbnailURI();

  /**
   * Builds basic render array for video type.
   *
   * @return array
   *   Render array for video item.
   */
  public function render();

}
