## About Media entity

Media entity provides a 'base' entity for a media element. This is a
very basic entity which can reference to all kinds of media-objects
(local files, YouTube videos, tweets, CDN-files, ...). This entity
only provides a relation between Drupal (because it is an entity) and
the resource. You can reference to this entity within any other Drupal
entity.


## About Media entity embeddable video

This module provides integration for embeddable videos (all remote
videos that can be embbeded using the embed code or referenced by the
URL).

In order to provide integration for a new video source you need to
implement a video provider plugin. See
\Drupal\media_entity_embeddable_video\Plugin\MediaEntity\VideoProvider
for examples.

Project page: http://drupal.org/project/media_entity_embeddable_video

Maintainers:
 - Janez Urevc (@slashrsm) drupal.org/user/744628

IRC channel: #drupal-media
