<?php

namespace Drupal\opigno_learning_path\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Alters '/group/{group}/members' route path from Group module
    // to use this path in custom controller.
    if ($route = $collection->get('view.group_members.page_1')) {
      $route->setDefault('_controller', '\Drupal\opigno_learning_path\Controller\LearningPathMembershipController::alterGroupMembersRoute');
    }

    if ($route = $collection->get('entity.group.join')) {
      $route->setRequirement('_entity_access', 'group.join');
    }

    if ($route = $collection->get('view.opigno_classes.all')) {
      $route->setOption('_admin_route', TRUE);
    }
  }

}
