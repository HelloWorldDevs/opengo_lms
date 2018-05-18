(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.opignoLearningPathAchievements = {
    attach(context) {
      const $details_show = $('.lp_details_show', context);
      const $details_hide = $('.lp_details_hide', context);

      $details_show.once('click').click(function (e) {
        e.preventDefault();

        const $parent = $(this).parent('.lp_wrapper');

        if (!$parent) {
          return false;
        }

        $parent.find('.lp_details').show();
        $parent.find('.lp_details_show').hide();
        $parent.find('.lp_details_hide').show();

        return false;
      });

      $details_hide.once('click').click(function (e) {
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

      const $module_row = $('.lp_course_steps tr', context);
      $module_row.once('click').click(function (e) {
        e.preventDefault();

        const $panels = $('.lp_module_panel', context);
        $panels.hide();

        const $this = $(this);
        const $wrapper = $this.parents('.lp_course_steps_wrapper');
        const id = $this.attr('data-module-id');
        const $panel = $wrapper.find('.lp_module_panel[data-module-id="' + id + '"]');
        $panel.show();

        return false;
      });

      const $module_step = $('.lp_step_content_module .lp_step_summary_clickable', context);
      $module_step.once('click').click(function (e) {
        e.preventDefault();

        const $panels = $('.lp_module_panel', context);
        $panels.hide();

        const $this = $(this);
        const $wrapper = $this.parents('.lp_step_summary_wrapper');
        const id = $this.attr('data-module-id');
        const $panel = $wrapper.find('.lp_module_panel[data-module-id="' + id + '"]');
        $panel.show();

        return false;
      });

      const $module_panel_close = $('.lp_module_panel_close', context);
      $module_panel_close.once('click').click(function (e) {
        e.preventDefault();

        const $panel = $(this).parents('.lp_module_panel');
        $panel.hide();

        return false;
      });
    },
  };
}(jQuery, Drupal, drupalSettings));
