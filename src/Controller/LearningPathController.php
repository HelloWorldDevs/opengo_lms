<?php

namespace Drupal\opigno_learning_path\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\tft\Controller\TFTController;

class LearningPathController extends ControllerBase {

  /**
   * @param array $step
   *
   * @return array
   */
  protected function build_step_score_cell($step) {
    if ($step['typology'] === 'Module' || $step['typology'] === 'Course') {
      $score = $step['best score'];

      return [
        '#type' => 'container',
        [
          '#type' => 'html_tag',
          '#tag' => 'span',
          '#value' => $score . '%',
        ],
        [
          '#type' => 'container',
          '#attributes' => [
            'class' => ['lp_step_result_bar'],
          ],
          [
            '#type' => 'html_tag',
            '#tag' => 'div',
            '#attributes' => [
              'class' => ['lp_step_result_bar_value'],
              'style' => "width: $score%",
            ],
            '#value' => '',
          ],
        ],
      ];
    }
    else {
      return ['#markup' => '&dash;'];
    }
  }

  /**
   * @param array $step
   *
   * @return array
   */
  protected function build_step_state_cell($step) {
    $user = $this->currentUser();
    $uid = $user->id();

    if ($step['typology'] === 'Module') {
      $activities = opigno_learning_path_get_module_activities($step['id'], $uid);
    }
    elseif ($step['typology'] === 'Course') {
      $activities = opigno_learning_path_get_activities($step['id'], $uid);
    }
    else {
      return ['#markup' => '&dash;'];
    }

    $total = count($activities);
    $attempted = count(array_filter($activities, function ($activity) {
      return $activity['answers'] > 0;
    }));

    $progress = $total > 0
      ? $attempted / $total
      : 0;

    if ($progress < 1) {
      $state = '<span class="lp_step_state_pending"></span>' . t('Pending');
    }
    else {
      $score = $step['best score'];
      $min_score = $step['required score'];

      if ($score < $min_score) {
        $state = '<span class="lp_step_state_failed"></span>' . t('Failed');
      }
      else {
        $state = '<span class="lp_step_state_passed"></span>' . t('Passed');
      }
    }

    return ['#markup' => $state];
  }

  /**
   * @param array $step
   *
   * @return array
   */
  protected function build_course_row($step) {
    $result = $this->build_step_score_cell($step);
    $state = $this->build_step_state_cell($step);

    return [
      $step['name'],
      [
        'class' => 'lp_step_details_result',
        'data' => $result,
      ],
      [
        'class' => 'lp_step_details_state',
        'data' => $state,
      ],
    ];
  }

  /**
   * @return array
   */
  public function progress() {
    /** @var \Drupal\group\Entity\GroupInterface $group */
    $group = \Drupal::routeMatch()->getParameter('group');
    $user = \Drupal::currentUser();

    $id = $group->id();
    $uid = $user->id();

    $progress = opigno_learning_path_progress($id, $uid);
    $progress = round(100 * $progress);

    if (opigno_learning_path_is_passed($group, $uid)) {
      $steps = opigno_learning_path_get_steps($id, $uid);
      $mandatory_steps = array_filter($steps, function ($step) {
        return $step['mandatory'];
      });

      if (!empty($mandatory_steps)) {
        $score = round(array_sum(array_map(function ($step) {
          return $step['best score'];
        }, $mandatory_steps)) / count($mandatory_steps));
      }
      else {
        $score = 0;
      }

      /** @var \Drupal\Core\Datetime\DateFormatterInterface $date_formatter */
      $date_formatter = \Drupal::service('date.formatter');

      $completed = opigno_learning_path_completed_on($id, $uid);
      $completed = $completed > 0
        ? $date_formatter->format($completed, 'custom', 'F d, Y')
        : '';

      $summary = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['lp_progress_summary'],
        ],
        [
          '#type' => 'html_tag',
          '#tag' => 'p',
          '#attributes' => [
            'class' => ['lp_progress_summary_passed'],
          ],
          '#value' => '',
        ],
        [
          '#type' => 'html_tag',
          '#tag' => 'h3',
          '#attributes' => [
            'class' => ['lp_progress_summary_title'],
          ],
          '#value' => t('Passed'),
        ],
        [
          '#type' => 'html_tag',
          '#tag' => 'p',
          '#attributes' => [
            'class' => ['lp_progress_summary_score'],
          ],
          '#value' => t('Average score : @score%', [
            '@score' => $score,
          ]),
        ],
        [
          '#type' => 'html_tag',
          '#tag' => 'p',
          '#attributes' => [
            'class' => ['lp_progress_summary_date'],
          ],
          '#value' => t('Completed on @date', [
            '@date' => $completed,
          ]),
        ],
      ];
    }

    $content = [];

    $content[] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['col-9'],
      ],
      [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['lp_progress'],
        ],
        [
          '#type' => 'html_tag',
          '#tag' => 'p',
          '#attributes' => [
            'class' => ['lp_progress_label'],
          ],
          '#value' => t('Global Training Progress'),
        ],
        [
          '#type' => 'html_tag',
          '#tag' => 'p',
          '#attributes' => [
            'class' => ['lp_progress_value'],
          ],
          '#value' => $progress . '%',
        ],
        [
          '#type' => 'container',
          '#attributes' => [
            'class' => ['lp_progress_bar'],
          ],
          [
            '#type' => 'html_tag',
            '#tag' => 'div',
            '#attributes' => [
              'class' => ['lp_progress_bar_completed'],
              'style' => "width: $progress%",
            ],
            '#value' => '',
          ],
        ],
      ],
      (isset($summary) ? $summary : []),
      '#attached' => [
        'library' => [
          'opigno_learning_path/training_content',
        ],
      ],
    ];

    $buttons = [];

    $route = 'opigno_learning_path.steps.start';
    $args = ['group' => $group->id()];
    $url = Url::fromRoute($route, $args, []);

    if ($group->hasPermission('edit', $user)) {
      $buttons[] = [
        '#markup' => '<a class="lp_progress_admin_continue" href="' . $url->toString() . '"></a>'
          . '<a class="lp_progress_admin_edit" href="/group/' . $group->id() . '/edit"></a>',
      ];
    }
    else {
      $buttons[] = [
        '#markup' => '<a class="lp_progress_continue" href="' . $url->toString() . '">Continue Training</a>',
      ];
    }

    $content[] = array_merge([
      '#type' => 'container',
      '#attributes' => [
        'class' => ['col-3'],
      ],
    ], $buttons);

    return $content;
  }

  /**
   * @return array
   */
  public function trainingContent() {
    $group = \Drupal::routeMatch()->getParameter('group');
    $user = \Drupal::currentUser();

    $steps = opigno_learning_path_get_steps($group->id(), $user->id());
    $steps = array_map(function ($step) use ($user) {
      $sub_title = '';
      $score = $this->build_step_score_cell($step);
      $state = $this->build_step_state_cell($step);
      $rows = [];

      if ($step['typology'] === 'Course') {
        $course_steps = opigno_learning_path_get_steps($step['id'], $user->id());
        $sub_title = t('@count Modules', [
          '@count' => count($course_steps),
        ]);

        $rows = array_map([$this, 'build_course_row'], $course_steps);
      }

      return [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['lp_step'],
        ],
        [
          '#type' => 'container',
          '#attributes' => [
            'class' => ['lp_step_title_wrapper'],
          ],
          ($step['mandatory']
            ? [
              '#type' => 'html_tag',
              '#tag' => 'span',
              '#attributes' => [
                'class' => ['lp_step_required'],
              ],
              '#value' => '',
            ]
            : []),
          [
            '#type' => 'html_tag',
            '#tag' => 'h3',
            '#attributes' => [
              'class' => ['lp_step_title'],
            ],
            '#value' => $step['name'],
          ],
        ],
        [
          '#type' => 'container',
          '#attributes' => [
            'class' => ['lp_step_content'],
          ],
          [
            '#type' => 'container',
            '#attributes' => [
              'class' => ['lp_step_summary'],
            ],
            [

              '#type' => 'container',
              '#attributes' => [
                'class' => ['lp_step_summary_title_wrapper'],
              ],
              [
                '#type' => 'html_tag',
                '#tag' => 'h3',
                '#attributes' => [
                  'class' => ['lp_step_summary_title'],
                ],
                '#value' => $step['name'],
              ],
              [
                '#type' => 'html_tag',
                '#tag' => 'h4',
                '#attributes' => [
                  'class' => ['lp_step_summary_subtitle'],
                ],
                '#value' => $sub_title,
              ],
            ],
            [
              '#type' => 'table',
              '#attributes' => [
                'class' => ['lp_step_summary_details'],
              ],
              '#header' => [
                t('Score'),
                t('State'),
              ],
              '#rows' => [
                [
                  [
                    'class' => 'lp_step_details_result',
                    'data' => $score,
                  ],
                  [
                    'class' => 'lp_step_details_state',
                    'data' => $state,
                  ],
                ],
              ],
            ],
          ],
          [
            '#type' => 'container',
            '#attributes' => [
              'class' => ['lp_step_details_wrapper'],
            ],
            ($step['typology'] === 'Course'
              ? [
                '#type' => 'table',
                '#attributes' => [
                  'class' => ['lp_step_details'],
                ],
                '#header' => [
                  t('Module'),
                  t('Score'),
                  t('State'),
                ],
                '#rows' => $rows,
              ]
              : []),
          ],
        ],
        ($step['typology'] === 'Course'
        ? [
            [
              '#type' => 'container',
              '#attributes' => [
                'class' => ['lp_step_show'],
              ],
              [
                '#type' => 'html_tag',
                '#tag' => 'span',
                '#attributes' => [
                  'class' => ['lp_step_show_text'],
                ],
                '#value' => t('Show details'),
              ],
            ],
            [
              '#type' => 'container',
              '#attributes' => [
                'class' => ['lp_step_hide'],
              ],
              [
                '#type' => 'html_tag',
                '#tag' => 'span',
                '#attributes' => [
                  'class' => ['lp_step_hide_text'],
                ],
                '#value' => t('Hide details'),
              ],
            ],
          ] : []),
      ];
    }, $steps);

    $content = [];

    $content[] = [
      '#type' => 'container',
      '#markup' => '<div class="lp_tabs nav mb-4">
                      <a class="lp_tabs_link active" data-toggle="tab" href="#training-content">' . t('Training Content') . '</a>
                      <span></span>
                      <a class="lp_tabs_link" data-toggle="tab" href="#documents-library">' . t('Documents Library') . '</a>
                      <a class="lp_tabs_link" data-toggle="tab" href="#collaborative-workspace">' . t('Collaborative Workspace') . '</a>
                    </div>'
                  ];

    $content[] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'training-content', 'class' => ['tab-pane', 'fade', 'show', 'active']],
      '#prefix' => '<div class="tab-content">',
      'steps' => $steps,
      '#attached' => [
        'library' => [
          'opigno_learning_path/training_content',
        ],
      ],
    ];

    $TFTController = new TFTController();
    $listGroup = $TFTController->listGroup($group->id());
    $content[] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'documents-library', 'class' => ['tab-pane', 'fade']],
      '#markup' => render($listGroup),
    ];

    $content[] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'collaborative-workspace', 'class' => ['tab-pane', 'fade']],
      '#markup' => 'collaborative-workspace',
      '#suffix' => '</div>'
    ];

    return $content;
  }

  /**
   * Check the access for the learning path page.
   */
  public function access(AccountInterface $account) {
    if ($account->isAnonymous()) {
      return AccessResult::forbidden();
    }

    return AccessResult::allowed();
  }

}
