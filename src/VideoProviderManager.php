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

}
