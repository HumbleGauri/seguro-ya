const recoveryRequestForm = document.querySelector('#recovery-request-form');
const recoveryTokenForm = document.querySelector('#recovery-token-form');
const resetPasswordForm = document.querySelector('#reset-password-form');
const recoveryRequestMessage = document.querySelector('#recovery-request-message');
const recoveryTokenMessage = document.querySelector('#recovery-token-message');
const resetPasswordMessage = document.querySelector('#reset-password-message');
const recoveryRequestButton = document.querySelector('#recovery-request-button');
const recoveryTokenButton = document.querySelector('#recovery-token-button');
const resetPasswordButton = document.querySelector('#reset-password-button');
const changeRecoveryEmailButton = document.querySelector('#change-recovery-email');
const recoveryTitle = document.querySelector('#recovery-title');
const recoveryDescription = document.querySelector('#recovery-description');

let recoveryEmail = '';
let recoveryToken = '';
let tokenValidated = false;
let recoveryComplete = false;

function setMessage(element, text, isSuccess = false) {
  element.textContent = text;
  element.classList.toggle('form-message--success', isSuccess);
}

function showRecoveryStep(step) {
  recoveryRequestForm.hidden = step !== 'email';
  recoveryTokenForm.hidden = step !== 'token';
  resetPasswordForm.hidden = step !== 'password';
  changeRecoveryEmailButton.hidden = step === 'email';
  const headings = {
    email: ['¿Olvidaste tu contraseña?', 'Ingresá el correo electrónico con el que te registraste.'],
    token: ['Validá tu solicitud', `Ingresá el token de recuperación para ${recoveryEmail}.`],
    password: ['Creá una nueva contraseña', 'Completá ambos campos para recuperar el acceso a tu cuenta.'],
  };
  [recoveryTitle.textContent, recoveryDescription.textContent] = headings[step];
}

function clearRecoveryValidation() {
  recoveryToken = '';
  tokenValidated = false;
  recoveryTokenForm.reset();
  resetPasswordForm.reset();
  setMessage(recoveryTokenMessage, '');
  setMessage(resetPasswordMessage, '');
}

function setRecoveryBusy(busy) {
  [recoveryRequestButton, recoveryTokenButton, resetPasswordButton, changeRecoveryEmailButton]
    .forEach((button) => { button.disabled = busy || recoveryComplete; });
  recoveryRequestForm.elements.email.disabled = busy || recoveryComplete;
  recoveryTokenForm.elements.token.disabled = busy || recoveryComplete;
}

function isStrongPassword(password) {
  return password.length >= 8 && /[A-Z]/.test(password)
    && /[0-9]/.test(password) && /[^A-Za-z0-9]/.test(password);
}

async function postRecovery(endpoint, payload) {
  const response = await fetch(`../backend/api/${endpoint}`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload),
    cache: 'no-store',
  });
  const data = await response.json();
  if (!response.ok || !data.ok) {
    const error = new Error(data.error || 'No se pudo completar la solicitud.');
    error.status = response.status;
    throw error;
  }
  return data;
}

recoveryRequestForm.elements.email.addEventListener('input', () => {
  recoveryEmail = '';
  clearRecoveryValidation();
});

changeRecoveryEmailButton.addEventListener('click', () => {
  recoveryEmail = '';
  clearRecoveryValidation();
  setMessage(recoveryRequestMessage, '');
  showRecoveryStep('email');
  recoveryRequestForm.elements.email.focus();
});

recoveryTokenForm.elements.token.addEventListener('input', () => {
  recoveryToken = '';
  tokenValidated = false;
});

recoveryRequestForm.addEventListener('submit', async (event) => {
  event.preventDefault();
  setMessage(recoveryRequestMessage, '');
  if (!recoveryRequestForm.checkValidity()) {
    recoveryRequestForm.reportValidity();
    return;
  }
  const email = recoveryRequestForm.elements.email.value.trim();
  clearRecoveryValidation();
  setRecoveryBusy(true);
  try {
    const data = await postRecovery('recover_password.php', { email });
    recoveryEmail = email;
    showRecoveryStep('token');
    setMessage(recoveryTokenMessage, data.mensaje, true);
  } catch (error) {
    setMessage(recoveryRequestMessage, error.message || 'No se pudo conectar con el servidor.');
  } finally {
    setRecoveryBusy(false);
    if (!recoveryTokenForm.hidden) recoveryTokenForm.elements.token.focus();
  }
});

recoveryTokenForm.addEventListener('submit', async (event) => {
  event.preventDefault();
  setMessage(recoveryTokenMessage, '');
  if (!recoveryTokenForm.checkValidity()) {
    recoveryTokenForm.reportValidity();
    return;
  }
  const token = recoveryTokenForm.elements.token.value.trim();
  recoveryToken = '';
  tokenValidated = false;
  setRecoveryBusy(true);
  try {
    await postRecovery('verify_recovery_token.php', { email: recoveryEmail, token });
    recoveryToken = token;
    tokenValidated = true;
    showRecoveryStep('password');
  } catch (error) {
    setMessage(recoveryTokenMessage, error.message || 'No se pudo conectar con el servidor.');
  } finally {
    setRecoveryBusy(false);
    if (!resetPasswordForm.hidden) resetPasswordForm.elements.nueva_password.focus();
  }
});

resetPasswordForm.addEventListener('submit', async (event) => {
  event.preventDefault();
  setMessage(resetPasswordMessage, '');
  if (!resetPasswordForm.checkValidity()) {
    resetPasswordForm.reportValidity();
    return;
  }
  const nuevaPassword = resetPasswordForm.elements.nueva_password.value;
  if (nuevaPassword !== resetPasswordForm.elements.confirmar_password.value) {
    setMessage(resetPasswordMessage, 'Las contraseñas no coinciden.');
    return;
  }
  if (!isStrongPassword(nuevaPassword)) {
    setMessage(resetPasswordMessage, 'La contraseña debe tener al menos 8 caracteres, una mayúscula, un número y un símbolo.');
    return;
  }
  if (!tokenValidated || !recoveryEmail || !recoveryToken || recoveryComplete) return;
  setRecoveryBusy(true);
  try {
    const data = await postRecovery('reset_password.php', {
      email: recoveryEmail, token: recoveryToken, nueva_password: nuevaPassword,
    });
    clearRecoveryValidation();
    recoveryEmail = '';
    recoveryComplete = true;
    setMessage(resetPasswordMessage, data.mensaje, true);
    window.setTimeout(() => { window.location.href = 'prototipo-login.html'; }, 1200);
  } catch (error) {
    if (error.status === 400) {
      clearRecoveryValidation();
      showRecoveryStep('token');
      setMessage(recoveryTokenMessage, error.message);
    } else {
      setMessage(resetPasswordMessage, error.message || 'No se pudo conectar con el servidor.');
    }
  } finally {
    setRecoveryBusy(false);
  }
});
