<?php

/**
 * @file
 * Contains Drupal\media_entity_embeddable_video\Plugin\Validation\Constraint\VideoProviderMatchConstraint.
 */

namespace Drupal\media_entity_embeddable_video\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks if a value is a valid video embed code/URL.
 *
 * @Constraint(
 *   id = "VideoProviderMatch",
 *   label = @Translation("Video provider matches", context = "Validation"),
 *   type = { "entity", "entity_reference" }
 * )
 */
class VideoProviderMatchConstraint extends Constraint {

  /**
   * The default violation message.
   *
   * @var string
   */
  public $message = 'Not valid URL/embed code.';

  /**
   * The regular expessions option.
   *
   * @var string|array
   */
  public $regular_expressions;

  /**
   * Gets the regular expressions as array.
   *
   * @return array
   */
  public function getRegularExpressionsOption() {
    // Support passing the embed_code as string, but force it to be an array.
    if (!is_array($this->regular_expressions)) {
      $this->regular_expressions = array($this->regular_expressions);
    }
    return $this->regular_expressions;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOption() {
    return 'regular_expressions';
  }

  /**
   * {@inheritdoc}
   */
  public function getRequiredOptions() {
    return array('regular_expressions');
  }
}
