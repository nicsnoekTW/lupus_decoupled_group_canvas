<?php

declare(strict_types=1);

namespace Drupal\lupus_decoupled_group_canvas;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Adds the group canonical path to the lupus decoupled frontend paths.
 */
class LupusDecoupledGroupCanvasServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container): void {
    if ($container->hasParameter('lupus_decoupled_ce_api.frontend_paths')) {
      $paths = $container->getParameter('lupus_decoupled_ce_api.frontend_paths') ?: [];
      $paths[] = '/group/{group}';
      $container->setParameter('lupus_decoupled_ce_api.frontend_paths', $paths);
    }
  }

}
