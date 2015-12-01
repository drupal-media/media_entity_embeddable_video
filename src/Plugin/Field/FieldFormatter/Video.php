<?php

/**
 * @file
 * Contains \Drupal\media_entity_embeddable_video\Plugin\Field\FieldFormatter\Video.
 */

namespace Drupal\media_entity_embeddable_video\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\media_entity_embeddable_video\VideoProviderManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
class Video extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The video provider manager.
   *
   * @var \Drupal\media_entity_embeddable_video\VideoProviderManager
   */
  protected $videoProviderManager;

  /**
   * Video constructor.
   *
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definitin.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field definition.
   * @param array $settings
   *   Formatter settings.
   * @param string $label
   *   Field label.
   * @param string $view_mode
   *   The view mode ID.
   * @param array $third_party_settings
   *   Additional third-party settings.
   * @param \Drupal\media_entity_embeddable_video\VideoProviderManager $video_provider_manager
   *   The video provider manager.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, VideoProviderManager $video_provider_manager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->videoProviderManager = $video_provider_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('plugin.manager.media_entity_embeddable_video.provider')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = array();

    foreach ($items as $delta => $item) {
      /** @var \Drupal\media_entity_embeddable_video\VideoProviderInterface $provider */
      $provider = $this->videoProviderManager->getProviderByEmbedCode($item->value);
      if ($provider) {
        $element[$delta] = $provider->render();
      }
    }

    return $element;
  }

}
