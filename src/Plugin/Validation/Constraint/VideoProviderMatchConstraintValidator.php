<?php

/**
 * @file
 * Contains \Drupal\media_entity_embeddable_video\Plugin\Validation\Constraint\VideoProviderMatchConstraintValidator.
 */

namespace Drupal\media_entity_embeddable_video\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the VideoProviderMatch constraint.
 */
class VideoProviderMatchConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) {
    $regexes = $constraint->getRegularExpressionsOption();
    if (empty($value)) {
      return;
    }

    $matches = [];
    $main_property = $value->mainPropertyName();
    $embed_code = $value->get($main_property)->getValue();

    foreach ($regexes as $reqular_expr) {
      if (preg_match($reqular_expr, $embed_code, $item_matches)) {
        $matches[] = $item_matches;
      }
    }

    if (empty($matches)) {
      $this->context->addViolation($constraint->message);
    }
  }

}
