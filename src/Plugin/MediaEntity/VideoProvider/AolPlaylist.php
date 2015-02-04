<?php

/**
 * Contains \Drupal\media_entity_embeddable_video\Plugin\MediaEntity\VideoProvider\AolPlaylist.
 */

namespace Drupal\media_entity_embeddable_video\Plugin\MediaEntity\Type;

use Drupal\Component\Plugin\PluginBase;
use Drupal\media_entity_embeddable_video\VideoProviderInterface;

/**
 * Provides embedding support for AOL playlists.
 *
 * @EmbeddableVideoProvider(
 *   id = "aol_playlist",
 *   label = @Translation("AOL (5min) playlist"),
 *   description = @Translation("Provides embedding support for AOL playlists."),
 *   regular_expressions = {
 *     "@http://pshared.5min.com/Scripts/PlayerSeed\.js\?([^"\']*)videoGroupID=(?<id>[0-9]+)([^"\']*)@i",
 *   }
 * )
 */
class Aol extends PluginBase implements VideoProviderInterface {

}
