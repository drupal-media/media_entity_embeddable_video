<?php

/**
 * Contains \Drupal\media_entity_embeddable_video\Plugin\MediaEntity\VideoProvider\YouTube.
 */

namespace Drupal\media_entity_embeddable_video\Plugin\MediaEntity\Type;

use Drupal\Component\Plugin\PluginBase;
use Drupal\media_entity_embeddable_video\VideoProviderInterface;

/**
 * Provides media type plugin for embedded videos.
 *
 * @MediaType(
 *   id = "embeddable_video",
 *   label = @Translation("Embeddable video"),
 *   description = @Translation("Provides business logic and metadata for videos.")
 * )
 */
class YouTube extends PluginBase implements VideoProviderInterface {

}
