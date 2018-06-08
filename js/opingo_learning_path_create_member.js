(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.opignoLearningPathCreateMember = {
    attach(context) {
      const $dialog = $('#drupal-modal');
      $dialog.on('dialogopen', function(event, ui) {
        $dialog.dialog('option', 'position', {
          'my': 'right top',
          'at': 'right top',
          'of': '#block-platon-content',
          'collision': 'none',
        });
        $dialog.dialog('option', 'minWidth', 490);
        $dialog.dialog('option', 'minHeight', 450);
      });

      const $class_users = $('#class_users', context);
      const $class_users_autocomplete = $('#class_users_autocomplete', context);
      if ($class_users && $class_users_autocomplete) {
        $class_users_autocomplete
            .once('autocompleteselect')
            .on('autocompleteselect', function (e, ui) {
              // Get ids of the already added options.
              const selected_ids = $('option', $class_users)
                  .map(function () {
                    return $(this).val();
                  }).get();

              // Add selected option to list if not added already.
              if (selected_ids.indexOf(ui.item.id) === -1) {
                const option_html = '<option value="' + ui.item.id + '">'
                    + ui.item.label
                    + '</option>';
                $class_users.append(option_html);
              }
            });
      }

      const $select_all = $('#select_all', context);
      $select_all
          .once('click')
          .on('click', function (e) {
            e.preventDefault();
            $('option', $class_users).prop('selected', true);
            return false;
          });
    },
  };
}(jQuery, Drupal, drupalSettings));
