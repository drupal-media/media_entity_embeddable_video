<?php

/**
 * Contains \Drupal\media_entity_embeddable_video\Plugin\MediaEntity\VideoProvider\Aol.
 */

namespace Drupal\media_entity_embeddable_video\Plugin\MediaEntity\VideoProvider;

use Drupal\Core\Url;
use Drupal\media_entity_embeddable_video\VideoProviderBase;
use Drupal\media_entity_embeddable_video\VideoProviderInterface;

/**
 * Provides embedding support for AOL videos.
 *
 * @EmbeddableVideoProvider(
 *   id = "aol",
 *   label = @Translation("AOL (5min)"),
 *   description = @Translation("Provides embedding support for AOL videos."),
 *   regular_expressions = {
 *     "@http://pshared.5min.com/Scripts/PlayerSeed\.js\?([^""']*)playList=(?<id>[0-9]+)([^""']*)@i"
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
        'http://api.5min.com/video/' . $this->matches['id'] . '/info.json',
        [
          'query' => [
            'sid' => '1304',
            'multiple_thumbnails' => 'true',
            'restriction' => 'no_html',
          ]
        ]
      )
    );

    if ($response->getStatusCode() == 200 && ($data = $response->json())) {
      return $data['items'][0]['image'];
    }

    return FALSE;
  }

}
