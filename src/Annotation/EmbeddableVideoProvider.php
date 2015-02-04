<?php

/**
 * @file
 * Contains \Drupal\media_entity_embeddable_video\Annotation\EmbeddableVideoProvider.
 */

namespace Drupal\media_entity_embeddable_video\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines an embeddable video provider plugin annotation object.
 *
 * @see hook_media_entity_embeddable_video_provider_info_alter()
 *
 * @Annotation
 */
class EmbeddableVideoProvider extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the provider.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

  /**
   * A brief description of the plugin.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation (optional)
   */
  public $description = '';

  /**
   * List of regular expressions that match embed codes and URLs of videos.
   *
   * @var array
   */
  public $regular_expressions = [];

}
