<?php

namespace Drupal\opigno_learning_path;

use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Class LearningPathGroupOperationsLinks.
 *
 * @package Drupal\opigno_learning_path
 */
class LearningPathGroupOperationsLinks {

  /**
   * Returns context Group operations links.
   */
  public function getLink() {
    $output = [];
    // $group = \Drupal::routeMatch()->getParameter('group');
    // if (empty($group)) {
    //   return $output;
    // }
    // $route = \Drupal::routeMatch()->getRouteName();
    // $account = \Drupal::currentUser();
    // $access = LearningPathAccess::getGroupAccess($group, $account);
    // if ($group && $group->id() && $route == 'entity.group.canonical' && $access) {
    //   $link = NULL;
    //   $visibility = $group->field_learning_path_visibility->value;
    //   $validation = $group->field_requires_validation->value;
    //   $is_member = $group->getMember($account) ? TRUE : FALSE;
    //   $isAnonymous = in_array('anonymous', $account->getRoles()) ? TRUE : FALSE;
    //
    //   if ($visibility == 'semiprivate' && $validation) {
    //     $joinLabel = t('Request group membership');
    //   }
    //   else {
    //     $joinLabel = t('Subscribe to group');
    //   }
    //
    //   if ($isAnonymous) {
    //     $link = [
    //       'title' => $joinLabel,
    //       'route' => 'user.login',
    //       'args' => ['destination' => render(Url::fromRoute('entity.group.canonical', ['group' => $group->id()])->toString())],
    //     ];
    //   }
    //   elseif (!$is_member) {
    //     $link = [
    //       'title' => $joinLabel,
    //       'route' => 'entity.group.join',
    //       'args' => ['group' => $group->id()],
    //     ];
    //   }
    //   elseif ($is_member && $group->hasPermission('leave group', $account)) {
    //     $link = [
    //       'title' => t('Leave group'),
    //       'route' => 'entity.group.leave',
    //       'args' => ['group' => $group->id()],
    //     ];
    //   }
    //
    //   if ($link) {
    //     $url = Url::fromRoute($link['route'], $link['args'], ['attributes' => ['class' => 'btn btn-success']]);
    //     $l = Link::fromTextAndUrl($link['title'], $url)->toRenderable();
    //     $output = [
    //       '#markup' => render($l),
    //     ];
    //   }
    // }
    //
    return $output;
  }

}
