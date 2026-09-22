/* =====================================================================
   RECICLA+ | JavaScript de la interfaz (Mobile First)
   JavaScript moderno + Bootstrap. Sin dependencias adicionales.
   ===================================================================== */
(function () {
  'use strict';

  /* ------------------------------------------------ captura de fotos (RF-07)
     El ciudadano puede TOMAR la foto con la cámara o elegirla de la galería
     (el selector del sistema ofrece ambas opciones). Se muestra la vista previa. */
  document.addEventListener('change', function (ev) {
    var input = ev.target;
    if (!input.matches || !input.matches('input[type="file"][data-foto]')) { return; }
    var bloque = input.closest('.bloque-material') || input.closest('.tarjeta-form') || input.closest('form') || document;
    var destino = bloque.querySelector('.vista-previa');
    if (!destino) { return; }
    var archivo = input.files && input.files[0];
    if (!archivo) { destino.innerHTML = ''; return; }
    var maxBytes = parseInt(input.getAttribute('data-max') || '2097152', 10);
    if (archivo.size > maxBytes) {
      destino.innerHTML = '<span class="text-danger small"><i class="fa-solid fa-triangle-exclamation me-1"></i>' +
        'La imagen pesa ' + (archivo.size / 1048576).toFixed(1) + ' MB y el máximo es ' + (maxBytes / 1048576).toFixed(0) + ' MB.</span>';
      input.value = '';
      return;
    }
    var url = URL.createObjectURL(archivo);
    destino.innerHTML = '<img src="' + url + '" class="miniatura mt-2" alt="Vista previa de la fotografía">' +
      '<span class="hint d-block mt-1"><i class="fa-solid fa-circle-check texto-verde me-1"></i>' + archivo.name + '</span>';
  });

  /* ------------------------------------------------ bloques repetibles de materiales */
  var lista = document.getElementById('lista-materiales');
  var plantilla = document.getElementById('plantilla-material');
  var botonAgregar = document.getElementById('agregar-material');

  if (lista && plantilla && botonAgregar) {
    var max = parseInt(lista.getAttribute('data-max') || '6', 10);
    var numerar = function () {
      lista.querySelectorAll('.bloque-material').forEach(function (b, i) {
        var etiqueta = b.querySelector('.numero-material');
        if (etiqueta) { etiqueta.textContent = i + 1; }
      });
    };
    var sugerirUnidad = function (bloque) {
      var selMaterial = bloque.querySelector('select[name="material_id[]"]');
      var selUnidad = bloque.querySelector('select[name="unidad[]"]');
      if (!selMaterial || !selUnidad) { return; }
      selMaterial.addEventListener('change', function () {
        var opcion = selMaterial.options[selMaterial.selectedIndex];
        var unidad = opcion ? opcion.getAttribute('data-unidad') : null;
        if (unidad) { selUnidad.value = unidad; }
      });
    };

    lista.querySelectorAll('.bloque-material').forEach(sugerirUnidad);

    botonAgregar.addEventListener('click', function () {
      if (lista.querySelectorAll('.bloque-material').length >= max) {
        window.alert('Puede registrar máximo ' + max + ' materiales por solicitud.');
        return;
      }
      lista.appendChild(plantilla.content.cloneNode(true));
      var bloques = lista.querySelectorAll('.bloque-material');
      sugerirUnidad(bloques[bloques.length - 1]);
      numerar();
      bloques[bloques.length - 1].scrollIntoView({ behavior: 'smooth', block: 'center' });
    });

    lista.addEventListener('click', function (ev) {
      var boton = ev.target.closest('.quitar-material');
      if (!boton) { return; }
      if (lista.querySelectorAll('.bloque-material').length === 1) {
        window.alert('Debe registrar al menos un material.');
        return;
      }
      boton.closest('.bloque-material').remove();
      numerar();
    });
  }

  /* ------------------------------------------------ confirmación de acciones sensibles */
  document.querySelectorAll('form[data-confirmar]').forEach(function (form) {
    form.addEventListener('submit', function (ev) {
      if (!window.confirm(form.getAttribute('data-confirmar'))) { ev.preventDefault(); }
    });
  });

  /* ------------------------------------------------ filtros que se envían solos */
  document.querySelectorAll('select[data-auto-filtro]').forEach(function (select) {
    select.addEventListener('change', function () { select.form.submit(); });
  });

  /* ------------------------------------------------ contador de caracteres */
  document.querySelectorAll('textarea[maxlength]').forEach(function (area) {
    var aviso = document.createElement('div');
    aviso.className = 'hint text-end';
    area.parentNode.appendChild(aviso);
    var pintar = function () {
      aviso.textContent = area.value.length + ' / ' + area.getAttribute('maxlength') + ' caracteres';
    };
    area.addEventListener('input', pintar);
    pintar();
  });

  /* ------------------------------------------------ desplazamiento suave a secciones */
  document.querySelectorAll('a[data-scroll]').forEach(function (enlace) {
    enlace.addEventListener('click', function (ev) {
      var destino = document.querySelector(enlace.getAttribute('href'));
      if (destino) { ev.preventDefault(); destino.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
    });
  });
})();
