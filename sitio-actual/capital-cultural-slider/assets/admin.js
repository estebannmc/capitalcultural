(function ($) {
  $(function () {
    const $list = $('#cc-slider-sortable');
    const $button = $('#cc-slider-save-order');
    const $feedback = $('#cc-slider-order-feedback');

    if (!$list.length) return;

    $list.sortable({
      handle: '.cc-drag-handle',
      placeholder: 'cc-slider-sortable-placeholder',
      forcePlaceholderSize: true
    });

    $button.on('click', function () {
      const ids = $list.find('.cc-slider-sortable-item').map(function () {
        return $(this).data('id');
      }).get();

      $button.prop('disabled', true);
      $feedback.removeClass().addClass('cc-slider-order-saving').text('Guardando...');

      $.post(ccSliderAdmin.ajaxUrl, {
        action: 'cc_slider_save_order',
        nonce: ccSliderAdmin.nonce,
        ids: ids
      }).done(function (response) {
        if (response && response.success) {
          $feedback.removeClass().addClass('cc-slider-order-success').text('Orden guardado.');
        } else {
          $feedback.removeClass().addClass('cc-slider-order-error').text('No se pudo guardar.');
        }
      }).fail(function () {
        $feedback.removeClass().addClass('cc-slider-order-error').text('Error al guardar.');
      }).always(function () {
        $button.prop('disabled', false);
      });
    });
  });
})(jQuery);
