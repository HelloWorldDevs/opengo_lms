/* eslint-disable func-names */

(function ($, Drupal) {
  Drupal.behaviors.opignoLearningPathJoin = {
    attach(context) {
      $('.opigno-quiz-app-course-button.join-link', context).click((e) => {
        e.preventDefault();
        $('#join-group-form-overlay').fadeIn(200);
      });

      $('#join-group-form-overlay button.close-overlay', context).click(() => {
        $('#join-group-form-overlay').fadeOut(200);
      });
    },
  };
}(jQuery, Drupal));
