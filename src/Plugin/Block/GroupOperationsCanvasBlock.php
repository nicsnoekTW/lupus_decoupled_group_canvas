<?php

declare(strict_types=1);

namespace Drupal\lupus_decoupled_group_canvas\Plugin\Block;

use Drupal\Component\Utility\SortArray;
use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\group\Entity\GroupInterface;
use Drupal\group\Plugin\Group\Relation\GroupRelationInterface;
use Drupal\group\Plugin\Group\Relation\GroupRelationTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Group operations block usable as a Canvas component.
 *
 * Mirrors `\Drupal\group\Plugin\Block\GroupOperationsBlock` but resolves the
 * group from the current route instead of declaring a required context, so
 * Canvas's BlockComponentDiscovery does not skip it.
 *
 * @see \Drupal\canvas\Plugin\Canvas\ComponentSource\BlockComponentDiscovery::checkRequirements()
 */
#[Block(
  id: 'lupus_decoupled_group_canvas_group_operations',
  admin_label: new TranslatableMarkup('Group operations (Canvas)'),
)]
final class GroupOperationsCanvasBlock extends BlockBase implements ContainerFactoryPluginInterface {

  public function __construct(
    array $configuration,
    string $plugin_id,
    mixed $plugin_definition,
    private readonly GroupRelationTypeManagerInterface $pluginManager,
    private readonly ModuleHandlerInterface $moduleHandler,
    private readonly RouteMatchInterface $routeMatch,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('group_relation_type.manager'),
      $container->get('module_handler'),
      $container->get('current_route_match'),
    );
  }

  public function build(): array {
    $build = [];
    $cacheable_metadata = (new CacheableMetadata())
      ->setCacheContexts(['user.group_permissions', 'route']);

    $group = $this->routeMatch->getParameter('group');
    if ($group instanceof GroupInterface && $group->id()) {
      $cacheable_metadata->addCacheableDependency($group);

      $links = [];
      foreach ($group->getGroupType()->getInstalledPlugins() as $plugin) {
        \assert($plugin instanceof GroupRelationInterface);
        $operations = $this->pluginManager
          ->getOperationProvider($plugin->getRelationTypeId())
          ->getGroupOperations($group);
        $cacheable_metadata = $cacheable_metadata->merge(CacheableMetadata::createFromRenderArray($operations));
        unset($operations['#cache']);
        $links += $operations;
      }

      if ($links) {
        $this->moduleHandler->alter('group_operations', $links, $group);
        uasort($links, [SortArray::class, 'sortByWeightElement']);
        $build['#type'] = 'operations';
        $build['#links'] = $links;
        $build['#attached']['library'][] = 'lupus_decoupled_group_canvas/group_operations_dropdown';
      }
    }

    $cacheable_metadata->applyTo($build);
    return $build;
  }

}
