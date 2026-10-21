const form = document.querySelector('#login-form');
const message = document.querySelector('#login-message');
const button = document.querySelector('#login-button');

form.addEventListener('submit', async (event) => {
  event.preventDefault();
  message.textContent = '';
  message.classList.remove('form-message--success');

  const nombreUsuario = form.nombre_usuario.value.trim();
  const password = form.password.value;

  if (!nombreUsuario || !password) {
    message.textContent = 'Completá el nombre de usuario y la contraseña.';
    return;
  }

  button.disabled = true;
  button.firstChild.textContent = 'Ingresando ';

  try {
    const response = await fetch('../backend/api/login.php', {
      method: 'POST',
      credentials: 'include',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ nombre_usuario: nombreUsuario, password }),
    });

    const data = await response.json();

    if (!response.ok || !data.ok) {
      message.textContent = data.error || 'No se pudo iniciar sesión.';
      return;
    }

    window.location.href = data.perfil === 'admin' ? 'admin.html' : 'usuario.html';
  } catch (error) {
    message.textContent = 'No se pudo conectar con el servidor.';
  } finally {
    button.disabled = false;
    button.firstChild.textContent = 'Iniciar sesión ';
  }
});
