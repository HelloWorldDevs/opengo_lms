<?php
/**
 * @file
 * Contains \Drupal\opigno_learning_path\Plugin\Block\StepsBlock.
 */

namespace Drupal\opigno_learning_path\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\group\Entity\Group;
use Drupal\opigno_group_manager\OpignoGroupContext;

/**
 * Provides a 'article' block.
 *
 * @Block(
 *   id = "lp_steps_block",
 *   admin_label = @Translation("LP Steps block")
 * )
 */
class StepsBlock extends BlockBase {

  protected function buildScore($step) {
    $is_attempted = $step['attempts'] > 0;

    if ($is_attempted) {
      $score = [
        '#type' => 'html_tag',
        '#tag' => 'span',
        '#value' => $step['best score'],
        '#attributes' => [
          'class' => ['lp_steps_block_score'],
        ],
      ];
    }
    else {
      $score = ['#markup' => '&dash;'];
    }

    return [
      'data' => $score,
    ];
  }

  protected function buildState($step) {
    $uid = \Drupal::currentUser()->id();
    $status = opigno_learning_path_get_step_status($step, $uid);
    $markups = [
      'pending' => '<span class="lp_steps_block_step_pending"></span>',
      'failed' => '<span class="lp_steps_block_step_failed"></span>'
        . $this->t('Failed'),
      'passed' => '<span class="lp_steps_block_step_passed"></span>'
        . $this->t('Passed'),
    ];
    $markup = isset($markups[$status]) ? $markups[$status] : '&dash;';
    return [
      'data' => [
        '#markup' => $markup,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $user = \Drupal::currentUser();

    $uid = $user->id();
    $gid = OpignoGroupContext::getCurrentGroupId();

    if (!isset($gid)) {
      return [];
    }

    $group = Group::load($gid);
    $title = $group->label();

    $group_steps = opigno_learning_path_get_steps($gid, $uid);
    $steps = [];

    // Load courses substeps.
    array_walk($group_steps, function ($step) use ($uid, &$steps) {
      if ($step['typology'] === 'Course') {
        $course_steps = opigno_learning_path_get_steps($step['id'], $uid);
        $steps = array_merge($steps, $course_steps);
      }
      else {
        $steps[] = $step;
      }
    });

    /** @var \Drupal\user\UserInterface $user */
    $user = \Drupal::currentUser();
    $steps = array_filter($steps, function ($step) use ($user) {
      if ($step['typology'] === 'Meeting') {
        // If the user have not the collaborative features role.
        if (!$user->hasPermission('view meeting entities')) {
          return FALSE;
        }

        // If the user is not a member of the meeting.
        /** @var \Drupal\opigno_moxtra\MeetingInterface $meeting */
        $meeting = \Drupal::entityTypeManager()
          ->getStorage('opigno_moxtra_meeting')
          ->load($step['id']);
        if (!$meeting->isMember($user->id())) {
          return FALSE;
        }
      }
      elseif ($step['typology'] === 'ILT') {
        // If the user is not a member of the ILT.
        /** @var \Drupal\opigno_ilt\ILTInterface $ilt */
        $ilt = \Drupal::entityTypeManager()
          ->getStorage('opigno_ilt')
          ->load($step['id']);
        if (!$ilt->isMember($user->id())) {
          return FALSE;
        }
      }

      return TRUE;
    });

    $score = opigno_learning_path_get_score($gid, $uid);
    $progress = opigno_learning_path_progress($gid, $uid);
    $progress = round(100 * $progress);

    $is_passed = opigno_learning_path_is_passed($group, $uid);

    if ($is_passed) {
      $state_class = 'lp_steps_block_summary_state_passed';
      $state_title = $this->t('Passed');
    }
    else {
      $state_class = 'lp_steps_block_summary_state_pending';
      $state_title = $this->t('In progress');
    }

    $steps = array_map(function ($step) {
      return [
        $step['name'],
        $this->buildScore($step),
        $this->buildState($step),
      ];
    }, $steps);

    $summary = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['lp_steps_block_summary'],
      ],
      [
        '#type' => 'html_tag',
        '#tag' => 'span',
        '#attributes' => [
          'class' => [$state_class],
        ],
        '#value' => '',
      ],
      [
        '#type' => 'html_tag',
        '#tag' => 'h3',
        '#attributes' => [
          'class' => ['lp_steps_block_summary_title'],
        ],
        '#value' => $state_title,
      ],
      [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#attributes' => [
          'class' => ['lp_steps_block_summary_score'],
        ],
        '#value' => t('Average score : @score%', [
          '@score' => $score,
        ]),
      ],
      [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#attributes' => [
          'class' => ['lp_steps_block_summary_progress'],
        ],
        '#value' => t('Progress : @progress%', [
          '@progress' => $progress,
        ]),
      ],
    ];

    return [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['lp_steps_block'],
      ],
      $summary,
      [
        '#type' => 'html_tag',
        '#tag' => 'h3',
        '#value' => $title,
        '#attributes' => [
          'class' => ['lp_steps_block_title'],
        ],
      ],
      [
        '#type' => 'table',
        '#header' => [
          t('Name'),
          t('Score'),
          t('State'),
        ],
        '#rows' => $steps,
        '#attributes' => [
          'class' => ['lp_steps_block_table'],
        ],
      ],
      '#attached' => [
        'library' => [
          'opigno_learning_path/steps_block',
        ],
      ],
    ];
  }

}
