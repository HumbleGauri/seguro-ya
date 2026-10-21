/* Credencial virtual: HTML, CSS y JavaScript sin dependencias de ejecución. */
(function (global) {
  'use strict';

  const instancias = new WeakMap();
  let siguienteId = 0;

  function formatoFecha(texto) {
    const partes = /^(\d{4})-(\d{2})-(\d{2})$/.exec(texto);
    if (!partes) return texto;

    const [, anio, mes, dia] = partes;
    const year = Number(anio);
    const month = Number(mes);
    const day = Number(dia);
    const bisiesto = year % 4 === 0 && (year % 100 !== 0 || year % 400 === 0);
    const diasPorMes = [31, bisiesto ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    if (year < 1 || month < 1 || month > 12 || day < 1 || day > diasPorMes[month - 1]) return texto;
    return `${dia}/${mes}/${anio}`;
  }

  function crear(elemento, opciones = {}) {
    if (!elemento || typeof elemento.querySelector !== 'function') {
      throw new TypeError('CredencialVirtual.crear requiere el elemento contenedor de la credencial.');
    }

    if (instancias.has(elemento)) {
      const existente = instancias.get(elemento);
      if (Object.prototype.hasOwnProperty.call(opciones, 'datos')) existente.actualizar(opciones.datos);
      return existente;
    }

    const component = elemento.querySelector('.cv-component');
    const stage = elemento.querySelector('.cv-stage');
    const front = elemento.querySelector('.cv-front');
    const back = elemento.querySelector('.cv-back');
    const button = elemento.querySelector('.cv-flip');
    const buttonText = elemento.querySelector('.cv-button-text');
    const announcement = elemento.querySelector('.cv-announcement');
    const fields = Array.from(elemento.querySelectorAll('[data-field]'));

    if (![component, stage, front, back, button, buttonText, announcement].every(Boolean) || fields.length === 0) {
      throw new Error('Falta el HTML de la credencial. Copiá el contenido de credencial.html dentro del contenedor.');
    }

    // Permite insertar varias credenciales en la misma página.
    const stageId = `cv-contenido-${++siguienteId}`;
    stage.id = stageId;
    button.setAttribute('aria-controls', stageId);
    elemento.classList.add('cv-widget');

    /** Reemplaza los datos completos; los campos omitidos muestran Sin información. */
    function actualizar(datos) {
      fields.forEach((element) => {
        const value = element.dataset.field.split('.').reduce((result, key) => result?.[key], datos);
        const isEmpty = value === null || value === undefined || String(value).trim() === '';
        let display = isEmpty ? 'Sin información' : String(value);
        if (!isEmpty && element.dataset.format === 'date') display = formatoFecha(display);
        // Los valores recibidos se insertan como texto.
        element.textContent = display;
      });
    }

    /** Cambia la cara visible: frente o reverso. */
    function mostrar(cara) {
      if (cara !== 'frente' && cara !== 'reverso') {
        throw new TypeError('La cara debe ser "frente" o "reverso".');
      }
      const showBack = cara === 'reverso';
      component.dataset.cara = cara;
      front.inert = showBack;
      back.inert = !showBack;
      front.setAttribute('aria-hidden', String(showBack));
      back.setAttribute('aria-hidden', String(!showBack));
      buttonText.textContent = showBack ? 'Ver frente' : 'Ver reverso';
      button.setAttribute('aria-label', showBack ? 'Ver frente de la credencial' : 'Ver reverso de la credencial');
      announcement.textContent = showBack ? 'Reverso de la credencial visible.' : 'Frente de la credencial visible.';
    }

    function alternar() {
      mostrar(component.dataset.cara === 'frente' ? 'reverso' : 'frente');
    }

    /** Retira el evento del botón al desmontar el componente. */
    function destruir() {
      button.removeEventListener('click', alternar);
      instancias.delete(elemento);
    }

    const api = Object.freeze({ actualizar, mostrar, destruir });
    button.addEventListener('click', alternar);
    instancias.set(elemento, api);
    mostrar('frente');
    actualizar(opciones.datos ?? null);
    return api;
  }

  global.CredencialVirtual = Object.freeze({ crear });
})(globalThis);
