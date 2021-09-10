'use strict';

(function ($) {
  Drupal.behaviors.opignoCatalog = {
    attach: function (context, settings) {
      $.fn.generateProps = function () {
        var props = {};
        $(this).each(function () {
          $.each(this.attributes, function () {
            if (this.specified) {
              props['data-' + this.name] = this.value;
            }
          });
          var user_data_list = {}
          $('option', this).each(function (i, e) {
            user_data_list[jQuery(e).val()] = $(e).text()
          })
          props['data-user-list'] = user_data_list;
          props['data-user-default'] = jQuery(this).val();
        });
        return props;
      };
      $('[data-user-load]', context).each(function (index, element) {
        var props = $(element).generateProps();
        new Vue({
          el: element,
          render: function (h) {
            return h(userselect, {
              props,
            })
          }
        })
      });

    },
  }
}(jQuery));
