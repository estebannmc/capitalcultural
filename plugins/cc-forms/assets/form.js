/*
 * Formulario por etapas de Capital Cultural.
 * Repite en el navegador las reglas de includes/class-ccf-schema.php para guiar a quien completa;
 * el servidor vuelve a validar todo. Sin dependencias.
 */
(function () {
	'use strict';

	var DRAFT_HOURS = 24; // Lo escrito se recupera en este navegador durante un día y se borra al enviar.

	function cond(c, v) {
		if (!c) return true;
		if (c.all) return c.all.every(function (x) { return cond(x, v); });
		if (c.any) return c.any.some(function (x) { return cond(x, v); });
		var val = v[c.field], t = c.value === undefined ? '' : String(c.value);
		var has = val !== undefined && val !== null;
		var isNum = function (x) { return x !== '' && x !== null && !isNaN(x); };
		switch (c.op) {
			case 'eq': return Array.isArray(val) ? val.indexOf(t) > -1 : has && String(val) === t;
			case 'neq': return Array.isArray(val) ? val.indexOf(t) === -1 : !has || String(val) !== t;
			case 'contains': return Array.isArray(val) ? val.indexOf(t) > -1 : has && t !== '' && String(val).toLowerCase().indexOf(t.toLowerCase()) > -1;
			case 'gt': return isNum(val) && isNum(c.value) && Number(val) > Number(c.value);
			case 'lt': return isNum(val) && isNum(c.value) && Number(val) < Number(c.value);
		}
		return false;
	}

	function check(f, v) {
		var choice = f.type === 'select' || f.type === 'radio';
		if (Array.isArray(v) ? !v.length : v === '') {
			if (!f.required) return '';
			return f.type === 'checkbox' ? 'Marcá al menos una opción.' : choice ? 'Elegí una opción.' : 'Completá este campo.';
		}
		var max = f.maxlength || (f.type === 'textarea' ? 2000 : 200);
		if (!Array.isArray(v) && String(v).length > max) return 'Es demasiado largo: el máximo es de ' + max + ' caracteres.';
		switch (f.type) {
			case 'email': return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v) ? '' : 'Escribí un correo válido, por ejemplo nombre@correo.com.';
			case 'dni': return /^\d{7,8}$/.test(v) ? '' : 'Escribí el DNI solo con números, sin puntos (7 u 8 cifras).';
			case 'tel':
				var d = v.replace(/\D/g, '');
				return /^[\d\s()+-]+$/.test(v) && d.length >= 8 && d.length <= 15 ? '' : 'Escribí un teléfono válido, con la característica. Por ejemplo: 342 4571609.';
			case 'number':
				if (!/^-?\d+([.,]\d+)?$/.test(v)) return 'Escribí un número.';
				var n = Number(String(v).replace(',', '.'));
				if ((f.min !== undefined && n < f.min) || (f.max !== undefined && n > f.max)) {
					return 'El número tiene que estar entre ' + (f.min !== undefined ? f.min : '…') + ' y ' + (f.max !== undefined ? f.max : '…') + '.';
				}
		}
		return '';
	}

	function display(f, v) {
		var labels = {};
		(f.options || []).forEach(function (o) { labels[String(o.value)] = o.label; });
		var one = function (x) { return labels[String(x)] || String(x); };
		return Array.isArray(v) ? v.map(one).join(', ') : one(v);
	}

	function init(form) {
		var root = form.closest('.ccf');
		var schema = JSON.parse(root.querySelector('.ccf-schema').textContent);
		var steps = schema.steps;
		var fields = {};
		steps.forEach(function (st, i) { st.fields.forEach(function (f) { f.step = i; fields[f.id] = f; }); });

		var paso = form.querySelector('.ccf-paso');
		var etapas = form.querySelector('.ccf-etapas');
		var prev = form.querySelector('[data-prev]');
		var next = form.querySelector('[data-next]');
		var alerta = form.querySelector('.ccf-alerta');
		var review = form.querySelector('.ccf-revision');
		var listo = root.querySelector('.ccf-listo');
		var acepto = form.querySelector('[name="_acepto"]');
		var key = 'ccf:' + form.dataset.form + ':' + form.dataset.version;
		var current = 0;
		var inReview = false;
		var vis = [];

		function wrap(id) { return form.querySelector('[data-field="' + id + '"]'); }

		function raw(f) {
			if (f.type === 'checkbox') {
				return Array.prototype.map.call(form.querySelectorAll('[name="' + f.id + '[]"]:checked'), function (i) { return i.value; });
			}
			if (f.type === 'radio') {
				var r = form.querySelector('[name="' + f.id + '"]:checked');
				return r ? r.value : '';
			}
			var el = form.elements[f.id];
			return el ? el.value.trim() : '';
		}

		function filterOptions(f, values) {
			if (f.type === 'select') {
				var el = form.elements[f.id];
				var allowed = f.options.filter(function (o) { return cond(o.show_if, values); });
				var sig = allowed.map(function (o) { return o.value; }).join('|');
				if (el.dataset.sig === sig) return;
				var keep = el.value;
				el.dataset.sig = sig;
				el.length = 1;
				allowed.forEach(function (o) { el.add(new Option(o.label, o.value)); });
				el.value = allowed.some(function (o) { return String(o.value) === keep; }) ? keep : '';
				return;
			}
			wrap(f.id).querySelectorAll('.ccf-opc[data-show-if]').forEach(function (label) {
				var ok = cond(JSON.parse(label.dataset.showIf), values);
				label.hidden = !ok;
				if (!ok) label.querySelector('input').checked = false;
			});
		}

		/* Una sola pasada, en orden: qué etapas y campos se ven y qué valores cuentan. */
		function state() {
			var values = {};
			vis = [];
			steps.forEach(function (st, i) {
				var stepOn = cond(st.show_if, values);
				if (stepOn) vis.push(i);
				st.fields.forEach(function (f) {
					var on = stepOn && cond(f.show_if, values);
					var w = wrap(f.id);
					if (w) w.hidden = !on;
					if (!on || f.type === 'info') return;
					if (f.options) filterOptions(f, values);
					var v = raw(f);
					if (Array.isArray(v) ? v.length : v !== '') {
						values[f.id] = f.type === 'number' && !isNaN(v.replace(',', '.')) ? Number(v.replace(',', '.')) : v;
					}
				});
			});
			return values;
		}

		function setError(id, msg) {
			var err = id === '_acepto' ? form.querySelector('#ccf-err-_acepto') : wrap(id) && wrap(id).querySelector('.ccf-err');
			if (!err) return;
			err.textContent = msg || '';
			err.hidden = !msg;
			var box = id === '_acepto' ? acepto.closest('.ccf-legal') : wrap(id);
			box.classList.toggle('ccf-con-error', !!msg);
			box.querySelectorAll('input, select, textarea').forEach(function (el) {
				if (msg) el.setAttribute('aria-invalid', 'true'); else el.removeAttribute('aria-invalid');
			});
		}

		function focusField(id) {
			var el = id === '_acepto' ? acepto : wrap(id).querySelector('input:not([type=hidden]), select, textarea');
			if (el) el.focus();
		}

		function announce(msg) {
			alerta.textContent = msg;
			alerta.hidden = !msg;
		}

		function go(index) {
			state();
			inReview = index >= vis.length;
			current = Math.max(0, Math.min(index, vis.length));
			form.querySelectorAll('.ccf-etapa').forEach(function (s) { s.hidden = true; });
			var sec = inReview ? review : form.querySelector('.ccf-etapa[data-step="' + vis[current] + '"]');
			sec.hidden = false;
			if (inReview) buildReview();
			prev.hidden = current === 0;
			next.textContent = inReview ? 'Enviar' : current === vis.length - 1 ? 'Revisar y enviar' : 'Siguiente';
			paso.textContent = inReview ? 'Último paso: revisá y enviá' : 'Paso ' + (current + 1) + ' de ' + vis.length + ' · ' + steps[vis[current]].title;
			etapas.innerHTML = '';
			vis.forEach(function (st, k) {
				var li = document.createElement('li');
				li.textContent = (k + 1) + '. ' + steps[st].title;
				li.className = inReview || k < current ? 'ccf-hecha' : k === current ? 'ccf-actual' : '';
				etapas.appendChild(li);
			});
			announce('');
			return sec;
		}

		function show(sec) {
			form.scrollIntoView({ block: 'start' });
			sec.querySelector('.ccf-etapa-titulo').focus({ preventScroll: true });
		}

		function validateStep(stepIndex) {
			var values = state();
			var first = '';
			steps[stepIndex].fields.forEach(function (f) {
				var w = wrap(f.id);
				if (f.type === 'info' || !w || w.hidden) return;
				var msg = check(f, raw(f));
				setError(f.id, msg);
				if (msg && !first) first = f.id;
			});
			return first;
		}

		function buildReview() {
			var values = state();
			var box = review.querySelector('.ccf-resumen');
			box.innerHTML = '';
			vis.forEach(function (st, k) {
				var part = document.createElement('div');
				part.className = 'ccf-resumen-parte';
				var head = document.createElement('div');
				head.className = 'ccf-resumen-head';
				var h = document.createElement('h3');
				h.textContent = steps[st].title;
				var b = document.createElement('button');
				b.type = 'button';
				b.className = 'ccf-cambiar';
				b.textContent = 'Cambiar';
				b.setAttribute('aria-label', 'Cambiar ' + steps[st].title);
				b.addEventListener('click', function () { show(go(k)); });
				head.appendChild(h);
				head.appendChild(b);
				var dl = document.createElement('dl');
				steps[st].fields.forEach(function (f) {
					if (!(f.id in values)) return;
					var dt = document.createElement('dt');
					dt.textContent = f.label;
					var dd = document.createElement('dd');
					dd.textContent = display(f, values[f.id]);
					dl.appendChild(dt);
					dl.appendChild(dd);
				});
				part.appendChild(head);
				part.appendChild(dl);
				box.appendChild(part);
			});
		}

		function save() {
			try {
				var all = {};
				Object.keys(fields).forEach(function (id) { if (fields[id].type !== 'info') all[id] = raw(fields[id]); });
				localStorage.setItem(key, JSON.stringify({ t: Date.now(), d: all }));
			} catch (e) { /* Sin almacenamiento: no se recupera, pero el formulario funciona igual. */ }
		}

		function restore() {
			var saved;
			try { saved = JSON.parse(localStorage.getItem(key) || 'null'); } catch (e) { return; }
			if (!saved || Date.now() - saved.t > DRAFT_HOURS * 3600e3) return;
			Object.keys(fields).forEach(function (id) {
				var f = fields[id], v = saved.d[id];
				if (v === undefined || f.type === 'info') return;
				state(); // Que las opciones filtradas ya estén armadas antes de elegir una.
				if (f.type === 'checkbox' || f.type === 'radio') {
					var list = Array.isArray(v) ? v : [v];
					wrap(id).querySelectorAll('input').forEach(function (i) { i.checked = list.indexOf(i.value) > -1; });
				} else if (form.elements[id]) {
					form.elements[id].value = v;
				}
			});
		}

		function clearDraft() {
			try { localStorage.removeItem(key); } catch (e) { /* nada */ }
		}

		function done(j, values) {
			clearDraft();
			form.hidden = true;
			var email = schema.email_field && values[schema.email_field];
			listo.innerHTML = '<span class="ccf-tilde" aria-hidden="true"><svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg></span>'
				+ '<h2></h2><p class="ccf-msg"></p><div class="ccf-registro"><span>Número de registro</span><strong></strong></div><p class="ccf-mail"></p>';
			listo.querySelector('h2').textContent = '¡Listo! Recibimos tu envío';
			listo.querySelector('.ccf-msg').textContent = j.message;
			listo.querySelector('strong').textContent = j.registro;
			listo.querySelector('.ccf-mail').textContent = email ? 'Te mandamos un correo a ' + email + ' con el resumen. Si no lo ves, revisá la carpeta de correo no deseado.' : 'Guardá el número de registro por si necesitás hacer una consulta.';
			listo.hidden = false;
			listo.scrollIntoView({ block: 'start' });
			listo.focus({ preventScroll: true });
		}

		function send() {
			if (!acepto.checked) {
				setError('_acepto', 'Para enviar tenés que aceptar el aviso de protección de datos personales.');
				acepto.focus();
				return;
			}
			var values = state();
			var data = {};
			Object.keys(values).forEach(function (k) { data[k] = values[k]; });
			data._acepto = true;
			next.disabled = true;
			next.textContent = 'Enviando…';
			announce('');
			fetch(form.dataset.endpoint, {
				method: 'POST',
				credentials: 'same-origin',
				headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': form.dataset.nonce },
				body: JSON.stringify({ form: form.dataset.form, version: Number(form.dataset.version), t: form.dataset.token, hp: form.elements.ccf_web.value, data: data })
			}).then(function (r) {
				return r.json().catch(function () { return {}; }).then(function (j) { return { status: r.status, j: j }; });
			}).then(function (res) {
				var j = res.j;
				if (j.ok) return done(j, values);
				next.disabled = false;
				next.textContent = 'Enviar';
				if (j.errors) {
					var ids = Object.keys(j.errors);
					ids.forEach(function (id) { setError(id, j.errors[id]); });
					var stepOf = j.steps && j.steps[ids[0]];
					if (stepOf !== undefined && vis.indexOf(stepOf) > -1) {
						go(vis.indexOf(stepOf));
						ids.forEach(function (id) { setError(id, j.errors[id]); });
					}
					announce(j.message || 'Revisá los campos marcados.');
					focusField(ids[0]);
					return;
				}
				announce(j.message || 'No pudimos enviar el formulario. Probá de nuevo en unos minutos.');
			}).catch(function () {
				next.disabled = false;
				next.textContent = 'Enviar';
				announce('No hay conexión. Lo que escribiste sigue guardado en este navegador: probá de nuevo en un rato.');
			});
		}

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			if (inReview) return send();
			var bad = validateStep(vis[current]);
			if (bad) {
				announce('Revisá los campos marcados antes de seguir.');
				focusField(bad);
				return;
			}
			save();
			show(go(current + 1));
		});

		prev.addEventListener('click', function () { show(go(current - 1)); });

		var timer;
		function onChange(e) {
			var w = e.target.closest('[data-field]');
			var err = w && w.querySelector('.ccf-err');
			if (err && !err.hidden) setError(w.dataset.field, '');
			if (e.target === acepto) setError('_acepto', '');
			state();
			clearTimeout(timer);
			timer = setTimeout(save, 400);
		}
		form.addEventListener('input', onChange);
		form.addEventListener('change', onChange);

		restore();
		go(0);
	}

	document.querySelectorAll('.ccf form.ccf-tarjeta').forEach(init);
})();
