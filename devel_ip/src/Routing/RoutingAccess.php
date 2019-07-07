<?php

namespace Drupal\devel_ip\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RoutingAccess extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {

    // Validates for devel php access.
    if ($route = $collection->get('devel_php.execute_php')) {
      // Config file name of devel_ip.
      $name = 'devel_ip.settings';
      // Get array of IPs.
      $result = \Drupal::configFactory()->get($name)->get('ip');
      // Check whether current user ip address is in $result array.
      if ((in_array(\Drupal::request()->getClientIP(), $result))) {
        $route->setRequirement('_access', 'TRUE');
      }
      else {
        $route->setRequirement('_access', 'FALSE');
      }

    }

  }

}
