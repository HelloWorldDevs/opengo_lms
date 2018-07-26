(function ($, Drupal) {
  Drupal.behaviors.opignoLearningPathAchievements = {
    attach(context) {
      const $detailsShow = $('.lp_details_show', context);
      const $detailsHide = $('.lp_details_hide', context);
      const $lpStepTitleWrapper = $('.lp_step_title_wrapper', context);

      $lpStepTitleWrapper.once('click').click(function () {
        if ($(this).hasClass('open')) {
          $(this)
            .removeClass('open')
            .next('.lp_step_content')
            .hide();
        }
        else {
          $(this)
            .addClass('open')
            .next('.lp_step_content')
            .show();
        }
      });

      $detailsShow.once('click').click(function (e) {
        e.preventDefault();

        const $this = $(this);
        const $parent = $this.parent('.lp_wrapper');

        if (!$parent) {
          return false;
        }

        const $details = $parent.find('.lp_details[data-ajax-loaded]');
        if ($details.length === 0) {
          if (!$this.attr('data-ajax-loading')) {
            const training = $this.attr('data-training');
            $this.attr('data-ajax-loading', true);
            Drupal.ajax({
              url: `ajax/achievements/training-steps/${training}`,
            }).execute()
                .done(() => {
                  $parent.find('.lp_details').show();
                  $parent.find('.lp_details_show').hide();
                  $parent.find('.lp_details_hide').show();
                })
                .always(() => {
                  $this.removeAttr('data-ajax-loading');
                });
          }
        }
        else {
          $details.show();
          $parent.find('.lp_details_show').hide();
          $parent.find('.lp_details_hide').show();
        }

        return false;
      });

      $detailsHide.once('click').click(function (e) {
        e.preventDefault();

        const $parent = $(this).parents('.lp_wrapper');

        if (!$parent) {
          return false;
        }

        const $details = $parent.find('.lp_details');
        const height = $details.height();

        $details.hide();
        $parent.find('.lp_details_show').show();
        $parent.find('.lp_details_hide').hide();

        window.scrollBy(0, -height);

        return false;
      });

      const $moduleRow = $('.lp_course_steps tr td:nth-child(4) a', context);
      $moduleRow.once('click').click(function (e) {
        e.preventDefault();

        const $panels = $('.lp_module_panel', context);
        $panels.hide();

        const $this = $(this);
        const $wrapper = $this.parents('.lp_course_steps_wrapper');
        const training = $this.closest('tr').attr('data-training');
        const course = $this.closest('tr').attr('data-course');
        const module = $this.closest('tr').attr('data-module');
        const panelSelector = `#module_panel_${training}_${course}_${module}[data-ajax-loaded]`;
        const $panel = $wrapper.find(panelSelector);
        if ($panel.length === 0) {
          if (!$this.closest('tr').attr('data-ajax-loading')) {
            $this.closest('tr').attr('data-ajax-loading', true);
            Drupal.ajax({
              url: `ajax/achievements/module-panel/${training}/${course}/${module}`,
            }).execute()
                .done(() => {
                  $wrapper.find(panelSelector).show();
                })
                .always(() => {
                  $this.closest('tr').removeAttr('data-ajax-loading');
                });
          }
        }
        else {
          $panel.show();
        }

        return false;
      });

      const $moduleStep = $('.lp_step_content_module .lp_step_summary_clickable', context);
      $moduleStep.once('click').click(function (e) {
        e.preventDefault();

        const $panels = $('.lp_module_panel', context);
        $panels.hide();

        const $this = $(this);
        const $wrapper = $this.parents('.lp_step_summary_wrapper');
        const training = $this.attr('data-training');
        const module = $this.attr('data-module');
        const panelSelector = `#module_panel_${training}_${module}[data-ajax-loaded]`;
        const $panel = $wrapper.find(panelSelector);
        if ($panel.length === 0) {
          if (!$this.attr('data-ajax-loading')) {
            $this.attr('data-ajax-loading', true);
            Drupal.ajax({
              url: `ajax/achievements/module-panel/${training}/${module}`,
            }).execute()
                .done(() => {
                  $wrapper.find(panelSelector).show();
                })
                .always(() => {
                  $this.removeAttr('data-ajax-loading');
                });
          }
        }
        else {
          $panel.show();
        }

        return false;
      });

      const $modulePanelClose = $('.lp_module_panel_close', context);
      $modulePanelClose.once('click').click(function (e) {
        e.preventDefault();

        const $panel = $(this).parents('.lp_module_panel');
        $panel.hide();

        return false;
      });

      function getDocHeight() {
        const D = document;
        return Math.max(
            D.body.scrollHeight, D.documentElement.scrollHeight,
            D.body.offsetHeight, D.documentElement.offsetHeight,
            D.body.clientHeight, D.documentElement.clientHeight,
        );
      }

      let achievementsPage = 0;
      let achievementsAjaxLoading = false;
      const $window = $(window);
      $window.once('scroll').scroll(() => {
        if (!achievementsAjaxLoading) {
          if ($window.scrollTop() >= getDocHeight() - (2 * $window.height())) {
            achievementsAjaxLoading = true;
            Drupal.ajax({
              url: `ajax/achievements/${achievementsPage + 1}`,
            }).execute()
                .done(() => {
                  achievementsPage += 1;
                })
                .always(() => {
                  achievementsAjaxLoading = false;
                });
          }
        }
      });

      this.donutsCharts(context);
    },

    donutsCharts(c) {
      $('.donut', c).each(function () {
        const canvas = $(this)[0];
        const context = canvas.getContext('2d');
        const centerX = canvas.width / 2;
        const centerY = canvas.height / 2;
        const radius = canvas.height / 2;
        const angle = parseInt($(this).attr('data-value'));
        const color = (typeof $(this).attr('data-color') !== 'undefined') ? $(this).attr('data-color') : '#000';
        const radAngle = angle * 2 / 100;
        const trackColor = (typeof $(this).attr('data-track-color') !== 'undefined') ? $(this).attr('data-track-color') : 'rgba(0,0,0,.2)';

        $(this).css('box-shadow', `0 0 0 ${parseInt($(this).attr('data-width')) / 2}px ${trackColor} inset`);

        context.beginPath();
        context.arc(centerX, centerY, radius, -Math.PI / 2, radAngle * Math.PI - Math.PI / 2, false);
        context.lineWidth = parseInt($(this).attr('data-width'));
        context.strokeStyle = color;
        context.stroke();
      });
    },
  };
}(jQuery, Drupal, drupalSettings));
