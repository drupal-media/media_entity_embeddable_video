<?php

/**
 * Contains \Drupal\media_entity_embeddable_video\Plugin\MediaEntity\Type\EmbeddableVideo.
 */

namespace Drupal\media_entity_embeddable_video\Plugin\MediaEntity\Type;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Http\Client;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\media_entity\MediaBundleInterface;
use Drupal\media_entity\MediaInterface;
use Drupal\media_entity\MediaTypeException;
use Drupal\media_entity\MediaTypeInterface;
use Drupal\media_entity_embeddable_video\VideoProviderInterface;
use Drupal\media_entity_embeddable_video\VideoProviderManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides media type plugin for embedded videos.
 *
 * @MediaType(
 *   id = "embeddable_video",
 *   label = @Translation("Embeddable video"),
 *   description = @Translation("Provides business logic and metadata for videos.")
 * )
 */
class EmbeddableVideo extends PluginBase implements MediaTypeInterface, ContainerFactoryPluginInterface {
  use StringTranslationTrait;

  /**
   * Plugin label.
   *
   * @var string
   */
  protected $label;

  /**
   * Config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * HTTP client service.
   *
   * @var \Drupal\Core\Http\Client
   */
  protected $httpClient;

  /**
   * Video provider plugin manager.
   *
   * @var \Drupal\media_entity_embeddable_video\VideoProviderManager
   */
  protected $videoProviders;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('http_client'),
      $container->get('config.factory'),
      $container->get('plugin.manager.media_entity_embeddable_video.provider')
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
   * @param \Drupal\Core\Http\Client $http_client
   *   Http client.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory service.
   * @param \Drupal\media_entity_embeddable_video\VideoProviderManager $video_providers
   *   Video provider plugin manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Client $http_client, ConfigFactoryInterface $config_factory, VideoProviderManager $video_providers) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->httpClient = $http_client;
    $this->videoProviders = $video_providers;
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    return $this->label;
  }

  /**
   * Returns list of video sources with all needed data.
   *
   * @var array
   */
  protected function sourcesDefinition() {
    return array(
      'youtube' => array(
        'name' => $this->t('Youtube'),
        'regex' => array(
          '@(http|https)://www\.youtube(-nocookie)?\.com/embed/(?<id>[a-z0-9_-]+)@i',
          '@(http|https)://www\.youtube(-nocookie)?\.com/v/(?<id>[a-z0-9_-]+)@i',
          '@//www\.youtube(-nocookie)?\.com/embed/(?<id>[a-z0-9_-]+)@i',
          '@//www\.youtube(-nocookie)?\.com/v/(?<id>[a-z0-9_-]+)@i',
        ),
      ),
      'grabnetworks' => array(
        'name' => $this->t('Grab networks'),
        'regex' => array(
          '@http://player\.grabnetworks\.com/swf/GrabOSMFPlayer\.swf\?id=(?<id>[0-9]+)&content=v([a-f0-9]+)@i',
          '@http://player\.grabnetworks\.com/js/Player\.js\?([^"\']*)id=(?<id>[0-9]+)([^"\']*)&content=(v?[a-f0-9]+)([^"\']*)@i',
        ),
      ),
      '5min' => array(
        'name' => $this->t('5min video'),
        'regex' => array(
          '@http://pshared.5min.com/Scripts/PlayerSeed\.js\?([^"\']*)playList=(?<id>[0-9]+)([^"\']*)@i',
        ),
      ),
      '5min_playlist' => array(
        'name' => $this->t('5min playlist'),
        'regex' => array(
          '@http://pshared.5min.com/Scripts/PlayerSeed\.js\?([^"\']*)videoGroupID=(?<id>[0-9]+)([^"\']*)@i',
        ),
      ),
    );
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
    return $this->configFactory->get('pub_media.settings')->get('local_images') . '/' . $provider->id() . '_' . $provider->videoId() . '.jpg';
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
      file_unmanaged_save_data(file_get_contents($thumb_uri), $destination, FILE_EXISTS_REPLACE);
      return TRUE;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(MediaBundleInterface $bundle) {
    $form = array();
    $options = array();
    $allowed_field_types = array('text', 'text_long', 'link');
    foreach (\Drupal::entityManager()->getFieldDefinitions('media', $bundle->id()) as $field_name => $field) {
      if (in_array($field->getType(), $allowed_field_types)) {
        $options[$field_name] = $field->getLabel();
      }
    }

    $form['source_field'] = array(
      '#type' => 'select',
      '#title' => t('Field with source information'),
      '#description' => t('Field on media entity that stores video embed code or URL.'),
      '#default_value' => empty($this->configuration['source_field']) ? NULL : $this->configuration['source_field'],
      '#options' => $options,
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validate(MediaInterface $media) {
    if ($this->matchProvider($media)) {
      return;
    }

    throw new MediaTypeException($this->configuration['source_field'], 'Not valid URL/embed code.');
  }

  /**
   * {@inheritdoc}
   */
  public function thumbnail(MediaInterface $media) {
    if ($local_image = $this->getField($media, 'local_image')) {
      return $local_image;
    }

    return $this->configFactory->get('media_entity.settings')->get('icon_base') . '/embeddable_video.png';
  }

  /**
   * Matches video provider.
   *
   * @param MediaInterface $media
   *   Media object.
   * @return \Drupal\media_entity_embeddable_video\VideoProviderInterface|bool
   *   Video provider or FALSE if no match.
   */
  protected function matchProvider(MediaInterface $media) {
    $source_field = $this->configuration['source_field'];
    foreach ($this->videoProviders->getDefinitions() as $definition) {
      /** @var \Drupal\media_entity_embeddable_video\VideoProviderInterface $video_provider */
      $video_provider = $this->videoProviders->createInstance($definition['id'], []);
      $property_name = $media->{$source_field}->first()->mainPropertyName();
      if ($video_provider->matchEmbed($media->{$source_field}->{$property_name})) {
        return $video_provider;
      }
    }

    return FALSE;
  }
}
