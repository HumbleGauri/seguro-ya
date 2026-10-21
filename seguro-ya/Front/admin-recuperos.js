const recoveryRefreshButton = document.querySelector('#refresh-recovery-requests');
const recoveryRequestsStatus = document.querySelector('#recovery-requests-status');
const recoveryRequestsResults = document.querySelector('#recovery-requests-results');
const recoveryRequestsRows = document.querySelector('#recovery-requests-rows');

async function readRecoveryRequests() {
  const response = await fetch('../backend/api/admin_recovery_requests.php', {
    credentials: 'include', cache: 'no-store',
  });
  const data = await response.json();
  if (!response.ok || !data.ok) throw new Error(data.error || 'No se pudieron cargar las solicitudes.');
  return data.solicitudes;
}

function addRecoveryRequest(request) {
  const row = document.createElement('tr');
  [request.nombre_usuario, request.email, request.token,
    request.expira, request.estado].forEach((value) => {
    const cell = document.createElement('td');
    cell.textContent = value ?? '—';
    row.append(cell);
  });
  recoveryRequestsRows.append(row);
}

async function loadRecoveryRequests() {
  recoveryRefreshButton.disabled = true;
  recoveryRequestsResults.hidden = true;
  recoveryRequestsRows.replaceChildren();
  recoveryRequestsStatus.classList.remove('admin-status--error');
  recoveryRequestsStatus.textContent = 'Cargando solicitudes…';
  try {
    const requests = await readRecoveryRequests();
    requests.forEach(addRecoveryRequest);
    recoveryRequestsStatus.textContent = requests.length
      ? `${requests.length} ${requests.length === 1 ? 'solicitud encontrada' : 'solicitudes encontradas'}.`
      : 'Todavía no hay solicitudes de recupero.';
    recoveryRequestsResults.hidden = requests.length === 0;
  } catch (error) {
    recoveryRequestsStatus.classList.add('admin-status--error');
    recoveryRequestsStatus.textContent = error.message || 'No se pudieron cargar las solicitudes.';
  } finally {
    recoveryRefreshButton.disabled = false;
  }
}

recoveryRefreshButton.addEventListener('click', loadRecoveryRequests);
loadRecoveryRequests();
