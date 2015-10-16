<?php

/**
 * @file
 * Contains \Drupal\media_entity_embeddable_video\Kernel\Extension\VideoProviderMatchConstraintTest.
 */

namespace Drupal\media_entity_embeddable_video\Kernel\Extension;

use Drupal\Component\Annotation\Plugin\Discovery\AnnotatedClassDiscovery;
use Drupal\KernelTests\KernelTestBase;
use Drupal\media_entity_embeddable_video\Plugin\MediaEntity\VideoProvider\YouTube;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Drupal\media_entity_embeddable_video\Plugin\Validation\Constraint\VideoProviderMatchConstraint;
use Drupal\media_entity_embeddable_video\Plugin\Validation\Constraint\VideoProviderMatchConstraintValidator;

/**
 * Tests media_entity_embeddable_video constraints.
 *
 * @group media_entity_embeddable_video
 */
class VideoProviderMatchConstraintTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['system', 'user', 'media_entity', 'media_entity_embeddable_video'];

  /**
   * An array of regexes.
   *
   * @var array
   */
  public $regexes;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Get available regexes.
    $regexes = [];
    $video_providers = $this->container->get('plugin.manager.media_entity_embeddable_video.provider');
    foreach ($video_providers->getDefinitions() as $definition) {
      $regexes = array_merge($regexes, $definition['regular_expressions']);
    }
    $this->regexes = $regexes;
  }


  /**
   * Tests VideoProviderMatchConstraint constraint.
   *
   * @covers \Drupal\media_entity_embeddable_video\Plugin\Validation\Constraint\VideoProviderMatchConstraintValidator
   * @covers \Drupal\media_entity_embeddable_video\Plugin\Validation\Constraint\VideoProviderMatchConstraint
   * @dataProvider embedCodeProvider
   */
  public function testVideoProviderMatchConstraint($embed_code, $expected_violation_count) {
    $constraint = new VideoProviderMatchConstraint(['regular_expressions' => $this->regexes]);

    // Check message in constraint.
    $this->assertEquals('Not valid URL/embed code.', $constraint->message, 'Correct constraint message found.');

    // Test the get regexes method.
    $this->assertNotEmpty($constraint->getRegularExpressionsOption(), 'Regex array from provider plugins not empty');

    // Test the validator.
    $execution_context = $this->getMockBuilder('\Drupal\Core\TypedData\Validation\ExecutionContext')
      ->disableOriginalConstructor()
      ->getMock();

    if ($expected_violation_count) {
      $execution_context->expects($this->exactly($expected_violation_count))
        ->method('addViolation')
        ->with($constraint->message);
    }
    else {
      $execution_context->expects($this->exactly($expected_violation_count))
        ->method('addViolation');
    }

    $validator = new VideoProviderMatchConstraintValidator();
    $validator->initialize($execution_context);

    $value = new \stdClass();
    $value->value = $embed_code;
    $validator->validate($value, $constraint);
  }


  /**
   * Provides test data for testVideoProviderMatchConstraint().
   */
  public function embedCodeProvider() {
    return [
      'invalid URL' => ['https://drupal.org/project/media_entity_embeddable_video', 1],
      'invalid text' => ['I am a copy-cat and I copied this from the media entity twitter module', 1],
      'valid youtube URL' => ['https://www.youtube.com/watch?v=2YbmtzewgA4', 0],
      'valid youtube embed code' => ['<iframe width="560" height="315" src="https://www.youtube.com/embed/2YbmtzewgA4" frameborder="0" allowfullscreen></iframe>', 0],
      'valid grab embed URL' => ['http://player.grabnetworks.com/swf/GrabOSMFPlayer.swf?id=1742439&content=v62605a8d0c9b2a58eb60fb078638afb4d0034bdb', 0],
      'valid aol embed URL' => ['http://pshared.5min.com/Scripts/PlayerSeed.js?sid=1304&playList=517307220&width=100&height=100&hasCompanion=false&shuffle=0', 0],
      'valid aol playlist embed URL' => ['http://pshared.5min.com/Scripts/PlayerSeed.js?sid=1304&width=620&height=439&sequential=1&shuffle=0&videoGroupID=150728', 0],
    ];
  }
}
