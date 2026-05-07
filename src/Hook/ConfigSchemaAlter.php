<?php

declare(strict_types=1);

namespace Drupal\lupus_decoupled_group_canvas\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Adds 'group' to the allowed values of the content template entity-type ID.
 *
 * Canvas's `canvas.content_template.*.*.*` schema constrains
 * `content_entity_type_id` to `Choice: [node]`, which causes a
 * "not a valid choice" validation error when publishing a group content
 * template. Mirror that constraint to also accept 'group'.
 *
 * @see web/modules/contrib/canvas/config/schema/canvas.schema.yml (search
 *   for `content_entity_type_id`)
 * @see https://www.drupal.org/project/canvas/issues/3518272
 */
final class ConfigSchemaAlter {

  #[Hook('config_schema_info_alter')]
  public function configSchemaInfoAlter(array &$definitions): void {
    $key = 'canvas.content_template.*.*.*';
    if (!isset($definitions[$key]['mapping']['content_entity_type_id']['constraints']['Choice'])) {
      return;
    }
    $choices = &$definitions[$key]['mapping']['content_entity_type_id']['constraints']['Choice'];
    if (!\in_array('group', $choices, TRUE)) {
      $choices[] = 'group';
    }
  }

}
