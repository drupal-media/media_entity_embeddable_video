<?php

/**
 * Contains \Drupal\media_entity_embeddable_video\Plugin\MediaEntity\VideoProvider\YouTube.
 */

namespace Drupal\media_entity_embeddable_video\Plugin\MediaEntity\Type;

use Drupal\Component\Plugin\PluginBase;
use Drupal\media_entity_embeddable_video\VideoProviderInterface;

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
class YouTube extends PluginBase implements VideoProviderInterface {

}
