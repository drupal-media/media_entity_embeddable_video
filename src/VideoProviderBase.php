<?php

/**
 * Contains \Drupal\media_entity_embeddable_video\VideoProviderBase.
 */

namespace Drupal\media_entity_embeddable_video;

use Drupal\Component\Plugin\PluginBase;
use GuzzleHttp\Client;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base implementation for video providers.
 */
abstract class VideoProviderBase extends PluginBase implements VideoProviderInterface, ContainerFactoryPluginInterface {

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
   * HTTP client interface.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Client $http_client) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->setConfiguration($configuration);
    $this->httpClient = $http_client;

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
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('http_client')
    );
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

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $configuration += $this->defaultConfiguration();
    $this->configuration = $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [];
  }

}
