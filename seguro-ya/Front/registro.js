const registerForm = document.querySelector('#register-form');
const vehicleForm = document.querySelector('#vehicle-form');
const insuranceForm = document.querySelector('#insurance-form');
const insuranceMessage = document.querySelector('#insurance-message');
const createButton = document.querySelector('#insurance-create');
const backButton = document.querySelector('#insurance-back');
const resultDialog = document.querySelector('#registration-result');
const steps = ['user-step', 'vehicle-step', 'insurance-step'].map((id) => document.getElementById(id));
let submitting = false;

function showRegistrationResult({ title, message, success = false }) {
  document.querySelector('#registration-result-title').textContent = title;
  document.querySelector('#registration-result-message').textContent = message;
  document.querySelector('#registration-result-close').textContent = success ? 'Ir al inicio de sesión' : 'Aceptar';
  resultDialog.classList.toggle('registration-result--success', success);
  return new Promise((resolve) => {
    resultDialog.addEventListener('close', resolve, { once: true });
    resultDialog.showModal();
  });
}

function showStep(index, focusSelector) {
  steps.forEach((step, current) => { step.hidden = current !== index; });
  document.querySelector(focusSelector).focus();
  window.scrollTo({ top: 0 });
}

function validateForm(form) {
  const message = form.querySelector('.form-message');
  message.textContent = '';
  form.querySelectorAll('input[type="text"]').forEach((input) => {
    input.value = input.value.trim();
  });
  if (!form.checkValidity()) {
    const invalidInput = form.querySelector('input:invalid, select:invalid');
    const label = invalidInput.labels[0].textContent;
    message.textContent = `${label}: ${invalidInput.validationMessage}`;
    if (!form.closest('[hidden]')) form.reportValidity();
    return false;
  }
  return true;
}

function validateUser() {
  const username = registerForm.elements.nombre_usuario;
  username.value = username.value.trim();
  username.setCustomValidity(
    /^[A-Za-z0-9_.]{3,50}$/.test(username.value)
      ? '' : 'Usá entre 3 y 50 caracteres: letras, números, punto o guion bajo.'
  );
  const password = registerForm.elements.password;
  password.setCustomValidity(
    password.value.length >= 8 && /[A-Z]/.test(password.value) && /[0-9]/.test(password.value) && /[^A-Za-z0-9]/.test(password.value)
      ? '' : 'La contraseña debe tener mínimo 8 caracteres, una mayúscula, un número y un símbolo.'
  );
  return validateForm(registerForm);
}

function validateVehicle() {
  const year = vehicleForm.elements.anio;
  const value = Number(year.value);
  year.setCustomValidity(Number.isInteger(value) && value >= 1900 && value <= 2155 ? '' : 'El año debe estar entre 1900 y 2155.');
  vehicleForm.elements.patente.value = vehicleForm.elements.patente.value.trim().toUpperCase();
  return validateForm(vehicleForm);
}

function validateInsurance() {
  const start = insuranceForm.elements.vigencia_desde;
  const end = insuranceForm.elements.vigencia_hasta;
  end.setCustomValidity(start.value && end.value && end.value < start.value ? 'La vigencia hasta no puede ser anterior a la vigencia desde.' : '');
  return validateForm(insuranceForm);
}

// Validar desde JavaScript para normalizar el texto antes de comprobar el formato
// y mostrar el motivo del bloqueo junto al botón del paso correspondiente.
[registerForm, vehicleForm, insuranceForm].forEach((form) => {
  form.noValidate = true;
  form.addEventListener('input', (event) => {
    event.target.setCustomValidity?.('');
    form.querySelector('.form-message').textContent = '';
  });
});
insuranceForm.elements.vigencia_desde.addEventListener('input', () => {
  insuranceForm.elements.vigencia_hasta.setCustomValidity('');
});

registerForm.addEventListener('submit', (event) => {
  event.preventDefault();
  if (validateUser()) showStep(1, '#vehicle-heading');
});

document.querySelector('#vehicle-back').addEventListener('click', () => showStep(0, '#first-name'));
vehicleForm.addEventListener('submit', (event) => {
  event.preventDefault();
  if (validateVehicle()) showStep(2, '#insurance-heading');
});
backButton.addEventListener('click', () => {
  if (!submitting) showStep(1, '#vehicle-heading');
});

insuranceForm.addEventListener('submit', async (event) => {
  event.preventDefault();
  if (submitting) return;
  insuranceMessage.textContent = '';
  if (!validateUser()) { showStep(0, '#first-name'); registerForm.reportValidity(); return; }
  if (!validateVehicle()) { showStep(1, '#vehicle-heading'); vehicleForm.reportValidity(); return; }
  if (!validateInsurance()) return;

  const body = Object.fromEntries(new FormData(registerForm));
  body.vehiculo = Object.fromEntries(new FormData(vehicleForm));
  body.vehiculo.anio = Number(body.vehiculo.anio);
  body.seguro = Object.fromEntries(new FormData(insuranceForm));
  submitting = true;
  createButton.disabled = true;
  backButton.disabled = true;
  insuranceForm.setAttribute('aria-busy', 'true');
  createButton.firstChild.textContent = 'Creando cuenta ';

  try {
    const response = await fetch('../backend/api/register.php', {
      method: 'POST', credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body),
    });
    const data = await response.json();
    if (!response.ok || !data.ok) {
      insuranceMessage.textContent = data.error || 'No se pudo completar el registro.';
      await showRegistrationResult({ title: 'No se pudo crear la cuenta', message: insuranceMessage.textContent });
      return;
    }
    await showRegistrationResult({
      title: 'Cuenta creada',
      message: 'Tu cuenta, vehículo y seguro se registraron correctamente. Ya podés iniciar sesión.',
      success: true,
    });
    registerForm.reset(); vehicleForm.reset(); insuranceForm.reset();
    window.location.href = 'prototipo-login.html';
  } catch (error) {
    insuranceMessage.textContent = 'No se pudo confirmar el registro con el servidor. Revisá la conexión e intentá iniciar sesión para comprobar si la cuenta se creó.';
    await showRegistrationResult({ title: 'Registro sin confirmar', message: insuranceMessage.textContent });
  } finally {
    submitting = false;
    createButton.disabled = false;
    backButton.disabled = false;
    insuranceForm.removeAttribute('aria-busy');
    createButton.firstChild.textContent = 'Crear cuenta ';
    createButton.focus();
  }
});
