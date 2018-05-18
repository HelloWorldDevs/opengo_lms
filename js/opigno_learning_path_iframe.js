/* eslint-disable func-names */

(function ($, Drupal) {
  Drupal.behaviors.opignoLearningPathIframe = {
    attach() {
      const self = this;

      $(document).once().ajaxComplete(() => {
        if (self.inIframe()) {
          parent.iframeFormValues = drupalSettings.formValues;
        }
      });
    },

    inIframe() {
      try {
        return window.self !== window.top;
      }
      catch (e) {
        return true;
      }
    },
  };
}(jQuery, Drupal));
