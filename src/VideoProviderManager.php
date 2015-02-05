<?php

/**
 * @file
 * Contains \Drupal\media_entity_embeddable_video\VideoProviderManager.
 */

namespace Drupal\media_entity_embeddable_video;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Manages embeddable video provider plugins.
 */
class VideoProviderManager extends DefaultPluginManager {

  /**
   * Static cache of embed code - plugin pairs.
   *
   * @var array
   */
  protected $matches = [];

  /**
   * Constructs a new MediaTypeManager.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/MediaEntity/VideoProvider', $namespaces, $module_handler, 'Drupal\media_entity_embeddable_video\VideoProviderInterface', 'Drupal\media_entity_embeddable_video\Annotation\EmbeddableVideoProvider');

    $this->alterInfo('media_entity_embeddable_video_provider_info');
    $this->setCacheBackend($cache_backend, 'media_entity_embeddable_video_provider_plugins');
  }

  /**
   * Gets provider based on embed code.
   *
   * @param $embed_code
   *   Embed code or URL of the video.
   * @return \Drupal\media_entity_embeddable_video\VideoProviderInterface|false
   *   Video provider plugin or FALSE if none found.
   */
  public function getProviderByEmbedCode($embed_code) {
    $hash = md5($embed_code);

    if (empty($this->matches[$hash])) {
      $this->matches[$hash] = FALSE;
      foreach ($this->getDefinitions() as $id => $definition) {
        foreach ($definition['regular_expressions'] as $reqular_expr) {
          if (preg_match($reqular_expr, $embed_code)) {
            $this->matches[$hash] = $this->createInstance($id, ['embed_code' => $embed_code]);
          }
        }
      }
    }

    return $this->matches[$hash];
  }

}
