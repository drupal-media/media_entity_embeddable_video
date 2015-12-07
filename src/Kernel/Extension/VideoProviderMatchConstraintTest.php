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
  public static $modules = ['system', 'user', 'text', 'media_entity', 'media_entity_embeddable_video'];

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

    $value = new TestMediaEntityEmbeddableVideoFieldItem($embed_code);
    $validator = new VideoProviderMatchConstraintValidator();
    $validator->initialize($execution_context);
    $validator->validate($value, $constraint);
  }


  /**
   * Provides test data for testVideoProviderMatchConstraint().
   */
  public function embedCodeProvider() {
    return [
      'invalid URL' => ['https://drupal.org/project/media_entity_embeddable_video', 1],
      'invalid text' => ['I am a copy-cat and I copied this from the media entity twitter module', 1],
      'valid youtube url 1' => ['https://youtube.com/v/qT8YT-e3QBk&index=3&list=WL', 0],
      'valid youtube url 2' => ['http://youtube.com/v/qT8YT-e3QBk&index=3&list=WL', 0],
      'valid youtube url 3' => ['https://www.youtube.com/v/qT8YT-e3QBk&index=3&list=WL', 0],
      'valid youtube url 4' => ['http://www.youtube.com/v/qT8YT-e3QBk&index=3&list=WL', 0],
      'valid youtube url 5' => ['https://www.youtube.com/watch?v=qT8YT-e3QBk&index=3&list=WL', 0],
      'valid youtube url 6' => ['http://www.youtube.com/watch?v=qT8YT-e3QBk&index=3&list=WL', 0],
      'valid youtube url 7' => ['https://www.youtube.com/watch?v=qT8YT-e3QBk', 0],
      'valid youtube url 8' => ['http://www.youtube.com/watch?v=qT8YT-e3QBk', 0],
      'valid youtube url 9' => ['https://youtube.com/watch?v=qT8YT-e3QBk&index=3&list=WL', 0],
      'valid youtube url 10' => ['http://youtube.com/watch?v=qT8YT-e3QBk&index=3&list=WL', 0],
      'valid youtube url 11' => ['https://youtube.com/watch?v=qT8YT-e3QBk', 0],
      'valid youtube url 12' => ['http://youtube.com/watch?v=qT8YT-e3QBk', 0],
      'valid youtube url 13' => ['http://youtu.be/qT8YT-e3QBk', 0],
      'valid youtube url 14' => ['https://youtu.be/qT8YT-e3QBk', 0],
      'valid youtube url 15' => ['//youtube.com/v/qT8YT-e3QBk&index=3&list=WL', 0],
      'valid youtube url 16' => ['//www.youtube.com/v/qT8YT-e3QBk&index=3&list=WL', 0],
      'valid youtube url 17' => ['//www.youtube.com/watch?v=qT8YT-e3QBk&index=3&list=WL', 0],
      'valid youtube url 18' => ['//www.youtube.com/watch?v=qT8YT-e3QBk', 0],
      'valid youtube url 19' => ['//youtube.com/watch?v=qT8YT-e3QBk&index=3&list=WL', 0],
      'valid youtube url 20' => ['//youtube.com/watch?v=qT8YT-e3QBk', 0],
      'valid youtube url 21' => ['//youtu.be/qT8YT-e3QBk', 0],
      'valid youtube url 22' => ['https://www.youtube.com/watch?t=1m2s&v=C2Mbe5HzxVI', 0],
      'valid youtube url 23' => ['https://www.youtube.com/watch?t=68&v=C2Mbe5HzxVI', 0],
      'valid youtube url 24' => ['https://www.youtube.com/watch?t=68s&v=C2Mbe5HzxVI', 0],
      'valid youtube url 25' => ['https://youtu.be/C2Mbe5HzxVI?t=1m2s', 0],
      'valid youtube url 26' => ['https://youtu.be/C2Mbe5HzxVI?t=12', 0],
      'valid youtube url 27' => ['https://youtu.be/C2Mbe5HzxVI?t=12s', 0],
      'valid youtube embed code 1' => ['<iframe width="560" height="315" src="https://www.youtube.com/embed/qT8YT-e3QBk" frameborder="0" allowfullscreen></iframe>', 0],
      'valid youtube embed code 2' => ['<iframe width="560" height="315" src="https://youtube.com/embed/qT8YT-e3QBk" frameborder="0" allowfullscreen></iframe>', 0],
      'valid youtube embed code 3' => ['<iframe width="560" height="315" src="//www.youtube.com/embed/qT8YT-e3QBk" frameborder="0" allowfullscreen></iframe>', 0],
      'valid youtube embed code 4' => ['<iframe width="560" height="315" src="//youtube.com/embed/qT8YT-e3QBk" frameborder="0" allowfullscreen></iframe>', 0],
      'valid grab embed URL' => ['http://player.grabnetworks.com/swf/GrabOSMFPlayer.swf?id=1742439&content=v62605a8d0c9b2a58eb60fb078638afb4d0034bdb', 0],
      'valid aol embed URL' => ['http://pshared.5min.com/Scripts/PlayerSeed.js?sid=1304&playList=517307220&width=100&height=100&hasCompanion=false&shuffle=0', 0],
      'valid aol playlist embed URL' => ['http://pshared.5min.com/Scripts/PlayerSeed.js?sid=1304&width=620&height=439&sequential=1&shuffle=0&videoGroupID=150728', 0],
    ];
  }
}

/**
 * Mock class to test the testVideoProviderMatchConstraint.
 *
 * Support for static methods is not available when using core provided mocking
 * tools. Bot mainPropertyName and getValue are static methods on respective
 * interfaces. This might not be the most beatifull solution, but currently it
 * appears to be the only viable.
 */
class TestMediaEntityEmbeddableVideoFieldItem {
  /**
   * @var string
   *   The embed code string.
   */
  protected $embedCode;

  /**
   * MediaEntityEmbeddableVideoFieldItem constructor.
   *
   * @param string $embed_code
   *   The embed code used for this test.
   */
  public function __construct($embed_code) {
    $this->embedCode = $embed_code;
  }

  /**
   * Mocks mainPropertyName() on \Drupal\Core\Field\FieldItemInterface.
   */
  public function mainPropertyName() {
    return 'value';
  }

  /**
   * Mocks get() on \Drupal\Core\Field\FieldItemInterface.
   */
  public function get() {
    return $this;
  }

  /**
   * Mocs getValue() on \Drupal\Core\TypedData\Type\StringInterface.
   */
  public function getValue() {
    return $this->embedCode;
  }
}
