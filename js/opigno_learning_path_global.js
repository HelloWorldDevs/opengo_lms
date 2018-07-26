/* eslint-disable func-names */

(function ($, Drupal) {
  Drupal.behaviors.opignoLearningPathGlobal = {
    attach(context, settings) {
      if (this.inIframe()) {
        $('html').addClass('inIframe');
      }
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
