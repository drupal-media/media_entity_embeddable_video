<?php

/**
 * Contains \Drupal\media_entity_embeddable_video\Plugin\MediaEntity\Type\EmbeddableVideo.
 */

namespace Drupal\media_entity_embeddable_video\Plugin\MediaEntity\Type;

use Drupal\Core\Config\Config;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\media_entity\MediaTypeBase;
use Drupal\media_entity_embeddable_video\EmbeddableVideoTypeInterface;
use GuzzleHttp\Client;
use Drupal\media_entity\MediaBundleInterface;
use Drupal\media_entity\MediaInterface;
use Drupal\media_entity_embeddable_video\VideoProviderInterface;
use Drupal\media_entity_embeddable_video\VideoProviderManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides media type plugin for embedded videos.
 *
 * @MediaType(
 *   id = "embeddable_video",
 *   label = @Translation("Embeddable video"),
 *   description = @Translation("Provides business logic and metadata for videos.")
 * )
 */
class EmbeddableVideo extends MediaTypeBase implements EmbeddableVideoTypeInterface {

  /**
   * Plugin label.
   *
   * @var string
   */
  protected $label;

  /**
   * HTTP client service.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * Video provider plugin manager.
   *
   * @var \Drupal\media_entity_embeddable_video\VideoProviderManager
   */
  protected $videoProviders;

  /**
   * Media entity embeddable video config object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $moduleConfig;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager'),
      $container->get('config.factory')->get('media_entity_embeddable_video.settings'),
      $container->get('http_client'),
      $container->get('plugin.manager.media_entity_embeddable_video.provider'),
      $container->get('config.factory')->get('media_entity_embeddable_video.settings')
    );
  }

  /**
   * Constructs a new class instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   Entity field manager service.
   * @param \Drupal\Core\Config\Config $config
   *   Media entity config object.
   * @param \GuzzleHttp\Client $http_client
   *   Http client.
   * @param \Drupal\media_entity_embeddable_video\VideoProviderManager $video_providers
   *   Video provider plugin manager.
   * @param \Drupal\Core\Config\Config $module_config
   *   Media entity embeddable video config object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager, Config $config, Client $http_client, VideoProviderManager $video_providers, Config $module_config) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $entity_field_manager, $config);
    $this->httpClient = $http_client;
    $this->videoProviders = $video_providers;
    $this->moduleConfig = $module_config;
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    return $this->label;
  }

  /**
   * {@inheritdoc}
   */
  public function providedFields() {
    return array(
      'id' => $this->t('Video ID.'),
      'source' => $this->t('Video source machine name.'),
      'source_name' => $this->t('Video source human name.'),
      'image_local' => $this->t('Copies thumbnail image to the local filesystem and returns the URI.'),
      'image_local_uri' => $this->t('Gets URI of the locally saved image.'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getField(MediaInterface $media, $name) {
    if ($provider = $this->matchProvider($media)) {
      switch ($name) {
        case 'id':
          return $provider->videoId();

        case 'source':
          return $provider->id();

        case 'source_name':
          return $provider->name();

        case 'image_local':
          $local_uri = $this->localThumbURI($provider);
          if ($this->downloadThumb($provider, $local_uri)) {
            return $local_uri;
          }
          return FALSE;

        case 'image_local_uri':
          return $this->localThumbURI($provider);
      }
    }
    return FALSE;
  }

  /**
   * Gets URI to local copy of the thumbnail.
   *
   * @param VideoProviderInterface $provider
   *   Video provider plugin.
   *
   * @return string
   *   URI of local copy of thumbnail.
   */
  protected function localThumbURI(VideoProviderInterface $provider) {
    return $this->moduleConfig->get('local_images') . '/' . $provider->id() . '_' . $provider->videoId() . '.jpg';
  }

  /**
   * Downloads thumbnail image to $destination.
   *
   * @param VideoProviderInterface $media
   *   Media item.
   * @param $destination
   *   Thumbnail destination.
   *
   * @return bool
   *   TRUE on success and FALSE in case of a failure.
   */
  protected function downloadThumb(VideoProviderInterface $provider, $destination) {
    if ($thumb_uri = $provider->thumbnailURI()) {
      $dir = dirname($destination);
      file_prepare_directory($dir, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS);
      file_unmanaged_save_data(file_get_contents($thumb_uri), $destination, FILE_EXISTS_REPLACE);
      return TRUE;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    /** @var MediaBundleInterface $bundle */
    $bundle = $form_state->getFormObject()->getEntity();
    $options = [];
    $allowed_field_types = ['text', 'text_long', 'string', 'string_long', 'link'];
    foreach ($this->entityFieldManager->getFieldDefinitions('media', $bundle->id()) as $field_name => $field) {
      if (in_array($field->getType(), $allowed_field_types)) {
        $options[$field_name] = $field->getLabel();
      }
    }

    $form['source_field'] = [
      '#type' => 'select',
      '#title' => t('Field with source information'),
      '#description' => t('Field on media entity that stores video embed code or URL.'),
      '#default_value' => empty($this->configuration['source_field']) ? NULL : $this->configuration['source_field'],
      '#options' => $options,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function attachConstraints(MediaInterface $media) {
    parent::attachConstraints($media);

    $source_field_name = $this->configuration['source_field'];

    // Get all providers regexes. Wehen we will be able to select providers
    // per field we should handle that here.
    $video_provider_definitions = $this->videoProviders->getDefinitions();
    $regexes = [];
    foreach ($video_provider_definitions as $definition) {
      $regexes = array_merge($regexes, $definition['regular_expressions']);
    }

    foreach ($media->get($source_field_name) as &$embed_code) {
      /** @var \Drupal\Core\TypedData\DataDefinitionInterface $typed_data */
      $typed_data = $embed_code->getDataDefinition();
      $typed_data->addConstraint('VideoProviderMatch', ['regular_expressions' => $regexes]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function thumbnail(MediaInterface $media) {
    if ($local_image = $this->getField($media, 'image_local')) {
      return $local_image;
    }

    return $this->config->get('icon_base') . '/embeddable_video.png';
  }

  /**
   * {@inheritdoc}
   */
  public function matchProvider(MediaInterface $media) {
    $source_field = $this->configuration['source_field'];
    $property_name = $media->{$source_field}->first()->mainPropertyName();
    return $this->videoProviders->getProviderByEmbedCode($media->{$source_field}->{$property_name});
  }

}
