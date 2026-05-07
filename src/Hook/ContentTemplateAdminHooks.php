<?php

declare(strict_types=1);

namespace Drupal\lupus_decoupled_group_canvas\Hook;

use Drupal\canvas\Entity\ContentTemplate;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Url;

/**
 * Replicates Canvas's content-template admin UX hooks for the group entity.
 *
 * Canvas's ContentTemplateRoutes::VIEW_MODE_ROUTES is final/internal and only
 * lists the node route name, so we duplicate the relevant hook logic here for
 * the group view-mode route.
 *
 * @see \Drupal\canvas\Hook\ContentTemplateHooks::menuLocalTasksAlter()
 * @see \Drupal\canvas\Hook\ContentTemplateHooks::preprocessMenuLocalTask()
 */
final class ContentTemplateAdminHooks {

  private const string GROUP_VIEW_MODE_ROUTE = 'entity.entity_view_display.group.view_mode';
  private const string GROUP_DEFAULT_DISPLAY_ROUTE = 'entity.entity_view_display.group.default';

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  #[Hook('menu_local_tasks_alter')]
  public function menuLocalTasksAlter(array &$data, string $route_name, RefinableCacheableDependencyInterface &$cacheability): void {
    if ($route_name === self::GROUP_VIEW_MODE_ROUTE || $route_name === self::GROUP_DEFAULT_DISPLAY_ROUTE) {
      $storage = $this->entityTypeManager->getStorage(ContentTemplate::ENTITY_TYPE_ID);
      $cacheability->addCacheableDependency($storage);
    }
  }

  #[Hook('preprocess_menu_local_task')]
  public function preprocessMenuLocalTask(array &$variables): void {
    $url = $variables['element']['#link']['url'] ?? NULL;
    if (!$url instanceof Url || !$url->isRouted() || $url->getRouteName() !== self::GROUP_VIEW_MODE_ROUTE) {
      return;
    }
    $params = $url->getRouteParameters();
    $bundle = $params['group_type'] ?? NULL;
    $view_mode_id = $params['view_mode_name'] ?? 'default';
    if ($bundle === NULL) {
      return;
    }
    $template = $this->entityTypeManager
      ->getStorage(ContentTemplate::ENTITY_TYPE_ID)
      ->load("group.$bundle.$view_mode_id");
    if (!$template instanceof ConfigEntityInterface || !$template->status()) {
      return;
    }
    $variables['link']['#url'] = Url::fromUri("base:canvas/template/group/$bundle/$view_mode_id");
    $variables['link']['#options']['attributes']['class'][] = 'menu-icon';
    $variables['link']['#options']['attributes']['class'][] = 'external-link';
    $variables['link']['#attached']['library'][] = 'canvas/menu-icons';
  }

}
