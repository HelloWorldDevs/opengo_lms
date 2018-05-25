<?php

namespace Drupal\opigno_learning_path\TwigExtension;

use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\group\Entity\Group;
use Drupal\opigno_learning_path\Controller\LearningPathController;
use Drupal\opigno_learning_path\LearningPathAccess;

/**
 * Class DefaultTwigExtension.
 */
class DefaultTwigExtension extends \Twig_Extension {

 /**
  * {@inheritdoc}
  */
  public function getTokenParsers() {
    return [];
  }

 /**
  * {@inheritdoc}
  */
  public function getNodeVisitors() {
    return [];
  }

 /**
  * {@inheritdoc}
  */
  public function getFilters() {
    return [];
  }

 /**
  * {@inheritdoc}
  */
  public function getTests() {
    return [];
  }

 /**
  * {@inheritdoc}
  */
  public function getFunctions() {
    return [
        new \Twig_SimpleFunction(
            'is_group_member',
            [$this, 'is_group_member']
        ),
        new \Twig_SimpleFunction(
            'get_join_group_link',
            [$this, 'get_join_group_link']
        ),
        new \Twig_SimpleFunction(
            'get_start_link',
            [$this, 'get_start_link']
        ),
        new \Twig_SimpleFunction(
          'get_progress',
          [$this, 'get_progress']
        ),
        new \Twig_SimpleFunction(
          'get_training_content',
          [$this, 'get_training_content']
        ),
    ];
  }

 /**
  * {@inheritdoc}
  */
  public function getOperators() {
    return [];
  }

 /**
  * {@inheritdoc}
  */
  public function getName()
  {
    return 'opigno_learning_path.twig.extension';
  }

 /**
  * Test if user is member of a group
  */
  function is_group_member($group=null, $account=null)
  {
    if (!$group) {
      $group = \Drupal::routeMatch()->getParameter('group');
    }
    if (empty($group)) {
      return;
    }

    if (!$account) {
      $account = \Drupal::currentUser();
    }

    return $group->getMember($account) ? true : false ;
  }

  function get_join_group_link($group=null, $account=null, $attributes=[])
  {
    if (!$group) {
      $group = \Drupal::routeMatch()->getParameter('group');
    }

    if (!$account) {
      $account = \Drupal::currentUser();
    }

    $route = \Drupal::routeMatch()->getRouteName();
    $access = LearningPathAccess::getGroupAccess($group, $account);

    if ($group && $group->id() && $route == 'entity.group.canonical' && $access) {
      $link = NULL;
      $visibility = $group->field_learning_path_visibility->value;
      $validation = $group->field_requires_validation->value;
      $is_member = $group->getMember($account) ? TRUE : FALSE;
      $isAnonymous = in_array('anonymous', $account->getRoles()) ? TRUE : FALSE;

      if ($visibility == 'semiprivate' && $validation) {
        $joinLabel = t('Request group membership');
      }
      else {
        $joinLabel = t('Subscribe to group');
      }

      if ($isAnonymous) {
        if ($visibility === 'public') {
          $link = [
            'title' => t('Start'),
            'route' => 'opigno_learning_path.steps.start',
            'args' => ['group' => $group->id()],
          ];
        }
        else {
          $url = Url::fromRoute('entity.group.canonical', ['group' => $group->id()]);
          $link = [
            'title' => $joinLabel,
            'route' => 'user.login',
            'args' => ['destination' => render($url)->toString()],
          ];
        }
      }
      elseif (!$is_member) {
        $link = [
          'title' => $joinLabel,
          'route' => 'entity.group.join',
          'args' => ['group' => $group->id()],
        ];
      }

      if ($link) {
        $url = Url::fromRoute($link['route'], $link['args'], ['attributes' => $attributes]);
        $l = Link::fromTextAndUrl($link['title'], $url)->toRenderable();

        return render($l);
      }
    }
  }

  function get_start_link($group=null, $attributes=[])
  {

    if (!$group) {
      $group = \Drupal::routeMatch()->getParameter('group');
    }

    if(filter_var($group, FILTER_VALIDATE_INT) !== false) {
      $group = \Drupal\group\Entity\Group::load($group);
    }

    if (empty($group)) {
      return;
    }

    $current_route = \Drupal::routeMatch()->getRouteName();
    $visibility = $group->field_learning_path_visibility->value;
    $validation = $group->field_requires_validation->value;
    $account = \Drupal::currentUser();
    $isAnonymous = in_array('anonymous', $account->getRoles());
    $access = LearningPathAccess::getGroupAccess($group, $account);
    $member_pending = $visibility === 'semiprivate' && $validation
      && !LearningPathAccess::statusGroupValidation($group, $account);

    if ($visibility === 'public' && $isAnonymous) {
      $text = t('Start');
      $route = 'opigno_learning_path.steps.start';
      $attributes['class'][] = 'start-link';
    }
    elseif (!$group->getMember($account)) {
      $text = ($current_route == 'entity.group.canonical') ? t('Subscribe to group') : t('Learn more') ;
      $route = ($current_route == 'entity.group.canonical') ? 'entity.group.join' : 'entity.group.canonical';
      if ($current_route == 'entity.group.canonical') {
        $attributes['class'][] = 'join-link';
      }
    }
    elseif ($member_pending) {
      $text = t('Approval Pending');
      $route = 'entity.group.canonical';
      $attributes['class'][] = 'approval-pending-link';
    }
    else {
      $text = opigno_learning_path_started($group, $account) ? t('Continue training') : t('Start');
      $route = 'opigno_learning_path.steps.start';

      if (opigno_learning_path_started($group, $account)) {
        $attributes['class'][] = 'continue-link';
      } else {
        $attributes['class'][] = 'start-link';
      }
    }

    $args = ['group' => $group->id()];
    $url = Url::fromRoute($route, $args, ['attributes' => $attributes]);
    $l = Link::fromTextAndUrl($text, $url)->toRenderable();

    return render($l);
  }

  function get_progress() {
    $controller = new LearningPathController();
    $content = $controller->progress();
    return render($content);
  }

  function get_training_content() {
    $controller = new LearningPathController();
    $content = $controller->trainingContent();
    return render($content);
  }

}
