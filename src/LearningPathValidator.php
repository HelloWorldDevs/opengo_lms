<?php

namespace Drupal\opigno_learning_path;

use Drupal\Core\Url;
use Drupal\group\Entity\Group;
use Drupal\opigno_learning_path\Entity\LPManagedContent;
use Drupal\opigno_module\Entity\OpignoModule;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LearningPathValidator {

  /**
   * Check if the user has successfully passed all the conditions of a learning path.
   */
  public static function userHasPassed($uid, Group $learning_path) {
    // Check if all the mandatory contents are okay and if all the minimum score of the mandatories are good.
    $contents = LPManagedContent::loadByLearningPathId($learning_path->id());
    foreach($contents as $content) {

      // If the content is not mandatory, go to next iteration.
//      if ($content->isMandatory() == FALSE) {
//        continue;
//      }

      // Get the minimum score required.
      $min_score = $content->getSuccessScoreMin() / 100;

      // Compare the user score with the minimum score required.
      $content_type = $content->getLearningPathContentType();
      $user_score = $content_type->getUserScore($uid, $content->getEntityId());

      // If the minimum score is no good, return FALSE.
      if ($user_score < $min_score) {
        return FALSE;
      }

    }

    // If all the scores are okay, return TRUE.
    return TRUE;
  }

  /**
   *  Redirect user if one of learning path steps aren't completed.
   *
   * @param \Drupal\group\Entity\Group $group
   *
   * @return bool|\Symfony\Component\HttpFoundation\RedirectResponse
   */
  public static function stepsValidate(Group $group)  {
    $group_type = opigno_learning_path_get_group_type();
    $current_step = opigno_learning_path_get_current_step();
    $current_route = \Drupal::routeMatch()->getRouteName();
    $messenger = \Drupal::messenger();
    // Step 1 doesn't need validation because it has form validation.
    if ($current_step == 1) {
      return;
    };
    // Validate group type "learning_path".
    if ($group_type == 'learning_path') {
      $group_courses = $group->getContent('subgroup:opigno_course');
      $group_modules = $group->getContent('opigno_module_group');
      $route = '';
      // Check if group has at least one course or module.
      if (empty($group_courses) && empty($group_modules)) {
        $step = 2;
        $route = array_search($step, opigno_learning_path_get_routes_steps());
        $messenger->addError("Please, add some course or module!");
      }
      // Check if each course has at least one module.
      else if ($group_courses) {
        foreach ($group_courses as $cid => $content) {
          $course = $content->getEntity();
          $course_contents = $course->getContent('opigno_module_group');
          if (empty($course_contents)) {
            $step = 3;
            $route = array_search($step, opigno_learning_path_get_routes_steps());
            $messenger->addError("Please, add to course at least one module!");
          }
          // Check if each module of course has at least one activity.
          else {
            foreach ($course_contents as $cid => $content) {
              $module = $content->getEntity();
              $hasActivities = self::hasModuleActivities($module);
              if (empty($hasActivities)) {
                $step = 4;
                $route = array_search($step, opigno_learning_path_get_routes_steps());
                $messenger->addError("Please, add at least one activity to {$module->label()} module!");
              };
            }
          }
        }
      }
      // Skip 4 step if learning path hasn't any courses.
      else if (empty($group_courses) && $current_route == 'opigno_learning_path.learning_path_courses') {
        $step = 4;
        $route = array_search($step, opigno_learning_path_get_routes_steps());
        // $messenger->addError("You can't visit this page because you didn't add any course with modules!");
      }
      // Check if each learning path module has at least one activity.
      else if ($group_modules) {
        foreach ($group_modules as $cid => $content) {
          $module = $content->getEntity();
          $hasActivities = self::hasModuleActivities($module);
          if (empty($hasActivities)) {
            $step = 4;
            $route = array_search($step, opigno_learning_path_get_routes_steps());
            $messenger->addError("Please, add at least one activity to {$module->label()} module!");
          };
        }
      }
      if (!empty($route)) {
        if ($route == $current_route) {
          // Prevent redirect from current route
          return;
        };
        // Redirect to incompleted step.
        $response = new RedirectResponse(Url::fromRoute($route, ['group' => $group->id()])->toString());
        return $response->send();
      };
    }
    // If validation is passed successful.
    return;
  }

  /**
   * This method is called checking if opigno module has any activities.
   *
   * @param \Drupal\opigno_module\Entity\OpignoModule $opigno_module
   *
   * @return bool
   */
  protected static function hasModuleActivities(OpignoModule $opigno_module) {
    /* @var $db_connection \Drupal\Core\Database\Connection */
    $db_connection = \Drupal::service('database');
    $query = $db_connection->select('opigno_activity', 'oa');
    $query->fields('oa', ['id', 'vid', 'type', 'name']);
    $query->fields('omr', [
      'weight',
      'max_score',
      'auto_update_max_score',
      'omr_id',
      'omr_pid',
      'child_id',
      'child_vid',
    ]);
    $query->addJoin('inner', 'opigno_module_relationship', 'omr', 'oa.id = omr.child_id');
    $query->condition('oa.status', 1);
    $query->condition('omr.parent_id', $opigno_module->id());
    if ($opigno_module->getRevisionId()) {
      $query->condition('omr.parent_vid', $opigno_module->getRevisionId());
    }
    $query->condition('omr_pid', NULL, 'IS');
    $query->orderBy('omr.weight');
    $result = $query->execute();
    // If there is at least one activity - return TRUE.
    if ($result->fetchAll()) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

}
