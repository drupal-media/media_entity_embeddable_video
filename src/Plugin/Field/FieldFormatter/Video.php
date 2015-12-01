<?php

/**
 * @file
 * Contains \Drupal\media_entity_embeddable_video\Plugin\Field\FieldFormatter\Video.
 */

namespace Drupal\media_entity_embeddable_video\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'video' formatter.
 *
 * @FieldFormatter(
 *   id = "video",
 *   label = @Translation("Video"),
 *   field_types = {
 *     "string_long"
 *   }
 * )
 */
class Video extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = array();
    $provider_manager = \Drupal::service('plugin.manager.media_entity_embeddable_video.provider');

    foreach ($items as $delta => $item) {
      /** @var \Drupal\media_entity_embeddable_video\VideoProviderInterface $provider */
      $provider = $provider_manager->getProviderByEmbedCode($item->value);
      if ($provider) {
        $element[$delta] = $provider->render();
      }
    }

    return $element;
  }

}
