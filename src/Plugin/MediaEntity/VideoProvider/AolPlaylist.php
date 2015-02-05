<?php

/**
 * Contains \Drupal\media_entity_embeddable_video\Plugin\MediaEntity\VideoProvider\AolPlaylist.
 */

namespace Drupal\media_entity_embeddable_video\Plugin\MediaEntity\VideoProvider;

use Drupal\Core\Url;
use Drupal\media_entity_embeddable_video\VideoProviderBase;
use Drupal\media_entity_embeddable_video\VideoProviderInterface;

/**
 * Provides embedding support for AOL playlists.
 *
 * @EmbeddableVideoProvider(
 *   id = "aol_playlist",
 *   label = @Translation("AOL (5min) playlist"),
 *   description = @Translation("Provides embedding support for AOL playlists."),
 *   regular_expressions = {
 *     "@http://pshared.5min.com/Scripts/PlayerSeed\.js\?([^""']*)videoGroupID=(?<id>[0-9]+)([^""']*)@i"
 *   }
 * )
 */
class Aol extends VideoProviderBase implements VideoProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function thumbnailURI() {
    $response = $this->httpClient->get(
      Url::fromUri(
        'http://api.5min.com/video/list/info.xml',
        [
          'query' => [
            'sid' => '1304',
            'restriction' => 'no_html',
            'video_group_id' => $this->matches['id'],
          ]
        ]
      )
    );

    if ($response->getStatusCode() == 200 && ($data = $response->getBody())) {
      $data = simplexml_load_string($data);
      return $data->channel->item->image->url;
    }

    return FALSE;
  }

}
