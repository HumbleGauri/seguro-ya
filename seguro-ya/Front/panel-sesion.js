const expectedProfile = document.body.dataset.profile;
const sessionMessage = document.querySelector('#session-message');

async function verifySession() {
  try {
    const response = await fetch('../backend/api/session_check.php', {
      credentials: 'include',
    });
    const data = await response.json();

    if (!response.ok || !data.ok || !data.logueado || data.perfil !== expectedProfile) {
      window.location.replace('prototipo-login.html');
      return;
    }

    const userEmail = document.querySelector('#user-email');
    if (userEmail && data.email) userEmail.textContent = data.email;
  } catch (error) {
    sessionMessage.textContent = 'No se pudo verificar la sesión.';
  }
}

const logoutButton = document.querySelector('#logout-button');
if (logoutButton) {
  logoutButton.addEventListener('click', async () => {
    logoutButton.disabled = true;
    try {
      const response = await fetch('../backend/api/logout.php', {
        method: 'POST',
        credentials: 'include',
      });
      const data = await response.json();

      if (!response.ok || !data.ok) {
        throw new Error(data.error || 'No se pudo cerrar la sesión.');
      }

      window.location.replace('prototipo-login.html');
    } catch (error) {
      sessionMessage.textContent = error.message || 'No se pudo cerrar la sesión.';
      logoutButton.disabled = false;
    }
  });
}

verifySession();
