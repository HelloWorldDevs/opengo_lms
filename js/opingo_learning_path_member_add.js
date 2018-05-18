(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.opignoLearningPathMemberAdd = {
    attach(context) {
      const gid = drupalSettings.opigno_learning_path.gid;

      const $btn_create = $('#btn_create', context);

      const $pnl_create = $('#pnl_create', context);
      const $pnl_create_btn_close = $('#pnl_create_btn_close', context);
      const $pnl_create_btn_create_user = $('#pnl_create_btn_create_user', context);
      const $pnl_create_btn_create_class = $('#pnl_create_btn_create_class', context);

      const $pnl_create_user = $('#pnl_create_user', context);
      const $pnl_create_user_btn_close = $('#pnl_create_user_btn_close', context);
      const $pnl_create_user_field_name = $('#pnl_create_user_field_name', context);
      const $pnl_create_user_field_email = $('#pnl_create_user_field_email', context);
      const $pnl_create_user_btn_create = $('#pnl_create_user_btn_create', context);

      const $pnl_create_class = $('#pnl_create_class', context);
      const $pnl_create_class_btn_close = $('#pnl_create_class_btn_close', context);
      const $pnl_create_class_btn_create = $('#pnl_create_class_btn_create', context);
      const $pnl_create_class_field_class_name = $('#pnl_create_class_field_class_name', context);
      const $pnl_create_class_btn_select_all = $('#pnl_create_class_btn_select_all', context);
      const $pnl_create_class_field_search = $('#pnl_create_class_field_search', context);
      const $pnl_create_class_field_class_users = $('#pnl_create_class_field_class_users', context);
      $pnl_create_class_field_class_users.empty();

      $btn_create.once('click').click(function (e) {
        e.preventDefault();
        $pnl_create.show();

        return false;
      });

      $pnl_create_btn_close.once('click').click(function (e) {
        e.preventDefault();
        $pnl_create.hide();

        return false;
      });

      $pnl_create_btn_create_user.once('click').click(function (e) {
        e.preventDefault();
        $pnl_create.hide();
        $pnl_create_user.show();

        return false;
      });

      $pnl_create_btn_create_class.once('click').click(function (e) {
        e.preventDefault();
        $pnl_create.hide();
        $pnl_create_class.show();

        return false;
      });

      $pnl_create_user_btn_close.once('click').click(function (e) {
        e.preventDefault();
        $pnl_create_user.hide();

        return false;
      });

      $pnl_create_user_btn_create.once('click').click(function (e) {
        e.preventDefault();

        const name = $pnl_create_user_field_name.val();
        const email = $pnl_create_user_field_email.val();

        if (!name.length || !email.length) {
          return;
        }

        $pnl_create_user.hide();
        $pnl_create.show();

        $.ajax({
          url: '/group/' + gid + '/learning-path/members/create-user',
          data: {
            name: name,
            email: email,
          },
        })
            .done(function (data) {
              const message = data.message;

              $(
'<div class="alert alert-success" role="alert" aria-label="Status message">' +
  '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
    '<span aria-hidden="true">×</span>' +
  '</button>' +
  message +
'</div>'
              ).insertAfter($pnl_create_btn_create_class);
            });

        return false;
      });

      $pnl_create_class_btn_close.once('click').click(function (e) {
        e.preventDefault();
        $pnl_create_class.hide();
        $pnl_create_class_field_class_users.empty();

        return false;
      });

      $pnl_create_class_btn_select_all.once('click').click(function (e) {
        e.preventDefault();
        $('option', $pnl_create_class_field_class_users).prop('selected', true);

        return false;
      });

      $pnl_create_class_field_search.once('autocompleteselect').on('autocompleteselect', function (e, ui) {
        e.preventDefault();

        if (!ui.item) {
          return false;
        }

        // Add option with matching id from all users list to class users list
        // if not added already.
        const selected = $('option', $pnl_create_class_field_class_users)
            .map(function() {
              return $(this).val();
            }).get();

        if (selected.indexOf(ui.item.id) !== -1) {
          return false;
        }

        $('option', $options)
            .filter(function () {
              return $(this).val() === ui.item.id;
            })
            .clone()
            .appendTo($pnl_create_class_field_class_users);

        return false;
      });

      $pnl_create_class_btn_create.once('click').click(function (e) {
        e.preventDefault();

        const name = $pnl_create_class_field_class_name.val();
        const users = $pnl_create_class_field_class_users.val();

        if (!name.length || !users.length) {
          return;
        }

        $pnl_create_class.hide();
        $pnl_create.show();

        $.ajax({
          url: '/group/' + gid + '/learning-path/members/create-class',
          data: {
            name: name,
            users: users,
          },
        })
            .done(function (data) {
              const message = data.message;

              $(
'<div class="alert alert-success" role="alert" aria-label="Status message">' +
  '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
    '<span aria-hidden="true">×</span>' +
  '</button>' +
  message +
'</div>'
              ).insertAfter($pnl_create_btn_create_class);
            });

        return false;
      });

      const $find_option = $('#find_option', context);

      const $add_option = $('#add_option', context);
      const $remove_option = $('#remove_option', context);

      const $available_options = $('#available_options', context);
      const $selected_options = $('#selected_options', context);
      const $options = $('#options', context);
      $options.hide();

      let $selected_options_list = $('#selected_options_list', context);

      // Builds list with buttons matching the selected_users multi-select.
      function buildSelectedOptionsList($list) {
        $list.empty();

        $('option', $selected_options).each(function () {
          const $self = $(this);
          const selected = $self.prop('selected');

          const $list_item = $(
              '<li>' +
                '<span class="username">' + this.textContent + '</span>' +
              '</li>');

          if (selected) {
            $list_item.addClass('selected');
          }

          $list_item.once('click').click(function (e) {
            e.preventDefault();

            // Simulate multi-select behaviour on the list.
            if (e.ctrlKey) {
              $self.prop('selected', !selected);
            } else {
              $selected_options.val($self.val());
            }

            buildSelectedOptionsList($list);

            return false;
          });

          const $remove_user_button = $('<button>&times;</button>');
          $remove_user_button.once('click').click(function (e) {
            e.preventDefault();

            // Move user from selected_users to available_users.
            $selected_options.val($self.val());

            $('option:selected', $selected_options)
                .remove()
                .appendTo($available_options);

            buildSelectedOptionsList($list);

            // Clear selection.
            $available_options.val(0);
            $selected_options.val(0);

            return false;
          });

          $list_item.append($remove_user_button);
          $list.append($list_item);
        });

        // Update users.
        const values = $('option', $selected_options)
            .map(function() {
              return $(this).val();
            }).get();

        $options.val(values);
      }

      if ($selected_options.length && !$selected_options_list.length) {
        // Hide selected_users multi-select
        // and create selected users list if not exists.
        $selected_options.hide();
        $selected_options.after('<ul id="selected_options_list"></ul>');
        $selected_options_list = $('#selected_options_list', context);
        buildSelectedOptionsList($selected_options_list);
      }

      $find_option.once('autocompletechange').on('autocompletechange', function (e, ui) {
        e.preventDefault();

        if (ui.item) {
          return false;
        }

        const filter = $(this).val().trim().toUpperCase();
        $available_options.empty();

        const selected = $('option', $selected_options)
            .map(function () {
              return $(this).val();
            }).get();

        // Get all available options, remove selected, apply filter.
        $('option', $options)
            .filter(function () {
              const $this = $(this);
              const value = $this.val();
              const text = $this.text().toUpperCase();

              return selected.indexOf(value) === -1
                  && text.indexOf(filter) !== -1;
            })
            .clone()
            .appendTo($available_options);

        return false;
      });

      $find_option.once('autocompleteselect').on('autocompleteselect', function (e, ui) {
        e.preventDefault();

        if (!ui.item) {
          return false;
        }

        // Show all options in available list.
        $available_options.empty();

        const selected = $('option', $selected_options)
            .map(function() {
              return $(this).val();
            }).get();

        // Get all available options, remove selected, apply filter.
        $('option', $options)
            .filter(function () {
              const $this = $(this);
              const value = $this.val();

              return selected.indexOf(value) === -1
                  && value === ui.item.id;
            })
            .clone()
            .appendTo($available_options);
      });

      $add_option.once('click').click(function (e) {
        e.preventDefault();

        // Move selected from available_options to selected_options.
        const $selected = $('option:selected', $available_options);

        if (!$selected.length) {
          return false;
        }

        $selected.remove().appendTo($selected_options);

        // Clear selection.
        $available_options.val(0);
        $selected_options.val(0);

        buildSelectedOptionsList($selected_options_list);

        return false;
      });

      $remove_option.once('click').click(function (e) {
        e.preventDefault();

        // Move selected from selected_options to available_options.
        const $selected = $('option:selected', $selected_options);

        if (!$selected.length) {
          return false;
        }

        $selected.remove().appendTo($available_options);

        // Clear selection.
        $available_options.val(0);
        $selected_options.val(0);

        buildSelectedOptionsList($selected_options_list);

        return false;
      });
    },
  };
}(jQuery, Drupal, drupalSettings));
