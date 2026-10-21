const viewSwitch = document.querySelector('[data-view-switch]');
const viewButtons = [...document.querySelectorAll('[data-view-target]')];
const accountViews = [...document.querySelectorAll('[data-account-view]')];
const credentialStatus = document.querySelector('#credential-status');
const credentialContent = document.querySelector('#credential-content');
const profileStatus = document.querySelector('#profile-status');
const profileContent = document.querySelector('#profile-content');
let credentialLoaded = false;
let profileLoaded = false;

function setText(selector, value) {
  const element = document.querySelector(selector);
  if (element) element.textContent = value || '—';
}

function formatDate(value, includeTime = false) {
  if (!value) return '—';
  const normalized = includeTime ? value.replace(' ', 'T') : `${value}T00:00:00`;
  const date = new Date(normalized);
  if (Number.isNaN(date.getTime())) return value;
  return new Intl.DateTimeFormat('es-AR', includeTime
    ? { dateStyle: 'long', timeStyle: 'short' }
    : { dateStyle: 'long' }).format(date);
}

function setBadge(element, value, inactiveValues = []) {
  element.textContent = value || '—';
  element.classList.toggle('status-badge--inactive', inactiveValues.includes(value));
}

async function readEndpoint(url) {
  const response = await fetch(url, { credentials: 'include' });
  const data = await response.json();
  if (!response.ok || !data.ok) {
    throw new Error(data.error || 'No se pudo cargar la información.');
  }
  return data;
}

const virtualCredential = CredencialVirtual.crear(document.querySelector('#mi-credencial'));

function displayInsurance(credential, insurance) {
  const vehicle = insurance?.vehiculo || {};
  virtualCredential.actualizar({
    datosPersonales: {
      nombresYApellidos: credential.asegurado_nombre,
      fechaNacimiento: credential.fecha_nacimiento,
    },
    automovil: {
      marca: vehicle.marca,
      modelo: vehicle.modelo,
      anio: vehicle.anio,
      patente: vehicle.patente,
      motorNumero: vehicle.motor,
      chasisNumero: vehicle.chasis,
    },
    seguro: {
      aseguradora: insurance?.aseguradora,
      seguro: insurance?.cobertura,
      polizaNumero: insurance?.nro_poliza,
      vigenciaDesde: insurance?.vigencia_desde,
      vigenciaHasta: insurance?.vigencia_hasta,
    },
  });
  virtualCredential.mostrar('frente');
}

async function loadCredential() {
  if (credentialLoaded) return;
  credentialStatus.classList.remove('view-status--error');
  credentialStatus.textContent = 'Cargando tu credencial…';
  try {
    const data = await readEndpoint('../backend/api/credential.php');
    const credential = data.credencial;
    if (!credential) throw new Error('Todavía no hay una credencial asociada a esta cuenta.');
    const insurances = Array.isArray(credential.seguros) ? credential.seguros : [];
    if (insurances.length > 1) {
      throw new Error('La credencial tiene más de una póliza. Por ahora esta pantalla admite un solo auto y una póliza; solicitá ayuda al administrador.');
    }

    displayInsurance(credential, insurances[0] || null);
    credentialStatus.hidden = insurances.length > 0;
    if (!insurances.length) {
      credentialStatus.textContent = 'Esta credencial todavía no tiene una póliza asociada.';
    }
    credentialContent.hidden = false;
    credentialLoaded = true;
  } catch (error) {
    virtualCredential.actualizar(null);
    credentialContent.hidden = true;
    credentialStatus.hidden = false;
    credentialStatus.textContent = error.message;
    credentialStatus.classList.add('view-status--error');
  }
}
async function loadProfile() {
  if (profileLoaded) return;
  try {
    const data = await readEndpoint('../backend/api/user_profile.php');
    const user = data.usuario;
    const fullName = [user.nombres, user.apellidos].filter(Boolean).join(' ');
    const initials = [user.nombres, user.apellidos]
      .filter(Boolean)
      .map((part) => part.trim().charAt(0))
      .join('')
      .slice(0, 2)
      .toUpperCase();

    setText('#profile-full-name', fullName);
    setText('#profile-username', user.nombre_usuario ? `@${user.nombre_usuario}` : '—');
    setText('#profile-email', user.email);
    setText('#profile-birth-date', formatDate(user.fecha_nacimiento));
    setText('#profile-created-at', formatDate(user.fecha_registro, true));
    setText('#profile-initials', initials || 'SY');
    setBadge(document.querySelector('#profile-state'), user.estado, ['inactivo']);

    profileStatus.hidden = true;
    profileContent.hidden = false;
    profileLoaded = true;
  } catch (error) {
    profileStatus.textContent = error.message;
    profileStatus.classList.add('view-status--error');
  }
}

function showView(target) {
  const showProfile = target === 'profile';
  viewSwitch.classList.toggle('is-profile', showProfile);

  viewButtons.forEach((button) => {
    const isActive = button.dataset.viewTarget === target;
    button.classList.toggle('is-active', isActive);
    button.setAttribute('aria-selected', String(isActive));
    button.tabIndex = isActive ? 0 : -1;
  });

  accountViews.forEach((view) => {
    view.hidden = view.dataset.accountView !== target;
  });

  if (showProfile) loadProfile();
  else loadCredential();
}

viewButtons.forEach((button) => {
  button.addEventListener('click', () => showView(button.dataset.viewTarget));
  button.addEventListener('keydown', (event) => {
    if (!['ArrowLeft', 'ArrowRight'].includes(event.key)) return;
    event.preventDefault();
    const nextTarget = button.dataset.viewTarget === 'credential' ? 'profile' : 'credential';
    showView(nextTarget);
    document.querySelector(`[data-view-target="${nextTarget}"]`).focus();
  });
});

loadCredential();
