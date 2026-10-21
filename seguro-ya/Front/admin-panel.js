const refreshAccessLogButton = document.querySelector('#refresh-access-log');
const accessLogStatus = document.querySelector('#access-log-status');
const accessLogResults = document.querySelector('#access-log-results');
const accessLogRows = document.querySelector('#access-log-rows');
const userSearchForm = document.querySelector('#user-search-form');
const userSearchStatus = document.querySelector('#user-search-status');
const userSearchResults = document.querySelector('#user-search-results');
const userSearchRows = document.querySelector('#user-search-rows');

function addRow(tbody, values) {
  const row = document.createElement('tr');
  values.forEach((value) => {
    const cell = document.createElement('td');
    cell.textContent = value ?? '—';
    row.append(cell);
  });
  tbody.append(row);
}

async function readAdminEndpoint(url) {
  const response = await fetch(url, { credentials: 'include' });
  const data = await response.json();
  if (!response.ok || !data.ok) {
    throw new Error(data.error || 'No se pudo completar la consulta.');
  }
  return data;
}

async function loadAccessLog() {
  refreshAccessLogButton.disabled = true;
  accessLogStatus.classList.remove('admin-status--error');
  accessLogStatus.textContent = 'Cargando registros…';
  accessLogResults.hidden = true;

  try {
    const data = await readAdminEndpoint('../backend/api/admin_access_log.php');
    accessLogRows.replaceChildren();
    const entries = data.log || [];
    entries.forEach((entry) => addRow(accessLogRows, [entry.fecha_hora, entry.email, entry.resultado, entry.ip]));
    accessLogStatus.textContent = entries.length ? `${entries.length} ${entries.length === 1 ? 'registro encontrado' : 'registros encontrados'}.` : 'Todavía no hay accesos registrados.';
    accessLogResults.hidden = entries.length === 0;
  } catch (error) {
    accessLogStatus.classList.add('admin-status--error');
    accessLogStatus.textContent = error.message || 'No se pudieron cargar los registros.';
  } finally {
    refreshAccessLogButton.disabled = false;
  }
}

if (refreshAccessLogButton) {
  refreshAccessLogButton.addEventListener('click', loadAccessLog);
  loadAccessLog();
}

if (userSearchForm) userSearchForm.addEventListener('submit', async (event) => {
  event.preventDefault();
  const email = userSearchForm.elements.email.value.trim();
  if (!email) return;

  const searchButton = userSearchForm.querySelector('button[type="submit"]');
  searchButton.disabled = true;
  userSearchStatus.classList.remove('admin-status--error');
  userSearchStatus.textContent = 'Buscando usuarios…';
  userSearchResults.hidden = true;

  try {
    const params = new URLSearchParams({ email });
    const data = await readAdminEndpoint(`../backend/api/admin_search_user.php?${params}`);
    userSearchRows.replaceChildren();
    const users = data.usuarios || [];
    users.forEach((user) => addRow(userSearchRows, [user.id, user.email, user.perfil, user.fecha_nacimiento, user.fecha_registro]));
    userSearchStatus.textContent = users.length ? `${users.length} ${users.length === 1 ? 'usuario encontrado' : 'usuarios encontrados'}.` : 'No se encontraron usuarios con ese correo.';
    userSearchResults.hidden = users.length === 0;
  } catch (error) {
    userSearchStatus.classList.add('admin-status--error');
    userSearchStatus.textContent = error.message || 'No se pudo buscar usuarios.';
  } finally {
    searchButton.disabled = false;
  }
});
