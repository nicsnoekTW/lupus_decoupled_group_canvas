<?php

declare(strict_types=1);

namespace Drupal\lupus_decoupled_group_canvas\Hook;

use Drupal\canvas\EntityHandlers\ContentTemplateAwareViewBuilder;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Attaches Canvas's ContentTemplateAwareViewBuilder to the group entity type.
 *
 * Canvas only decorates the node view builder with the content-template-aware
 * one. Mirror that wiring for group so content templates of the form
 * `canvas.content_template.group.{bundle}.{view_mode}` are honored at render
 * time.
 *
 * @see \Drupal\canvas\Hook\ContentTemplateHooks::entityTypeAlter()
 */
final class EntityTypeAlter {

  #[Hook('entity_type_alter')]
  public function entityTypeAlter(array $definitions): void {
    $group_type = $definitions['group'] ?? NULL;
    if ($group_type === NULL || !$group_type->entityClassImplements(FieldableEntityInterface::class)) {
      return;
    }
    $group_type
      ->setHandlerClass(
        ContentTemplateAwareViewBuilder::DECORATED_HANDLER_KEY,
        $group_type->getViewBuilderClass()
      )
      ->setViewBuilderClass(ContentTemplateAwareViewBuilder::class);
  }

}
