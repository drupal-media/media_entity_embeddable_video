<?php

/**
 * Contains \Drupal\media_entity_embeddable_video\VideoProviderBase.
 */

namespace Drupal\media_entity_embeddable_video;

use Drupal\Component\Plugin\PluginBase;

/**
 * Base implementation for video providers.
 */
abstract class VideoProviderBase extends PluginBase implements VideoProviderInterface {

  /**
   * Video embed code/URL.
   *
   * @var string
   */
  protected $embedCode;

  /**
   * Regular expression matches.
   *
   * @var array
   */
  protected $matches;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->embedCode = $configuration['embed_code'];
    foreach ($this->pluginDefinition['regular_expressions'] as $regular_expression) {
      if (preg_match($regular_expression, $this->embedCode, $this->matches)) {
        break;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->pluginDefinition['id'];
  }

  /**
   * {@inheritdoc}
   */
  public function name() {
    return $this->pluginDefinition['label']->render();
  }

  /**
   * {@inheritdoc}
   */
  public function videoId() {
    return empty($this->matches['id']) ? NULL : $this->matches['id'];
  }

}
