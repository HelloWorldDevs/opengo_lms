(function ($, Drupal) {
  Drupal.behaviors.opignoDropzonejsWidgets = {
    attach: function (context, settings) {

      $(document).ready(function () {
        // Make form auto submission.
        $("form.entity-browser-form", context).once().bind('DOMSubtreeModified', () => {
          // Check if file is loaded.
          let value = $("input[name='upload[uploaded_files]']").attr('value');
          if (value.length) {
            // Trigger click on submit button.
            $("form.entity-browser-form").find("#edit-actions .button").click();
          }
        })
      })
    }
  }
}(jQuery, Drupal));
