/* Panel de formularios: elegir imágenes de la biblioteca, avisar si la proporción no es la pedida y copiar el enlace. */
(function ($) {
	'use strict';

	function setImage(name, att) {
		$('input[name="' + name + '"]').val(att ? att.id : 0);
		var url = att ? ((att.sizes && att.sizes.medium) || att).url : '';
		$('[data-for="' + name + '"]').html(url ? $('<img class="ccf-img-prev" alt="">').attr('src', url) : '');
		var warn = $('[data-warn="' + name + '"]');
		var ratio = Number($('[data-media="' + name + '"]').data('ratio'));
		var real = att && att.height ? att.width / att.height : ratio;
		var off = Math.abs(real - ratio) > 0.05;
		warn.prop('hidden', !off).text(off ? 'Ojo: la imagen mide ' + att.width + ' × ' + att.height + ' px y no tiene proporción ' + ratio + ':1, así que se va a ver distinta de lo esperado.' : '');
	}

	$('[data-media]').on('click', function () {
		var name = $(this).data('media');
		var frame = wp.media({ title: 'Elegí la imagen de encabezado', library: { type: 'image' }, multiple: false, button: { text: 'Usar esta imagen' } });
		frame.on('select', function () { setImage(name, frame.state().get('selection').first().toJSON()); });
		frame.open();
	});

	$('[data-clear]').on('click', function () { setImage($(this).data('clear'), null); });

	$('[data-copy]').on('click', function () {
		var btn = $(this);
		navigator.clipboard.writeText($(btn.data('copy')).text()).then(function () {
			btn.text('Copiado');
			setTimeout(function () { btn.text('Copiar enlace'); }, 2000);
		});
	});
})(jQuery);
