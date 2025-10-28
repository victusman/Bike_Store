document.addEventListener('DOMContentLoaded', function(){
  const url = '/Bike_Store/api/admin_session_info.php';
  const countEl = document.getElementById('active-sessions-count');
  const listEl = document.getElementById('active-sessions-list');

  if (!countEl || !listEl) return;

  function render(data){
    const count = data.count ?? 0;
    countEl.textContent = count;

    // Clear previous
    listEl.innerHTML = '';

    const sessions = Array.isArray(data.sessions) ? data.sessions : [];
    if (!sessions.length) {
      listEl.textContent = 'No hay sesiones activas';
      return;
    }

    // Container for badges
    const container = document.createElement('div');
    container.className = 'session-list';

    // Limit visualized badges to avoid overflow
    const MAX_SHOW = 24;
    sessions.slice(0, MAX_SHOW).forEach(s => {
      const span = document.createElement('span');
      span.className = 'session-badge' + (s.usuario && s.usuario.toLowerCase() === 'admin' ? ' admin' : '');
      const label = s.usuario ? s.usuario : (s.id ? s.id : 'sess');
      span.textContent = label;
      span.title = (s.mtime ? s.mtime + ' — ' : '') + (s.id ? s.id : '');

      // copiar id al portapapeles al hacer click
      span.style.userSelect = 'none';
      span.addEventListener('click', function(){
        const textToCopy = s.id || label;
        if (!navigator.clipboard) return;
        navigator.clipboard.writeText(textToCopy).then(() => {
          span.classList.add('copy-success');
          const prev = span.textContent;
          span.textContent = 'Copiado';
          setTimeout(()=>{ span.classList.remove('copy-success'); span.textContent = prev; }, 1500);
        }).catch(()=>{});
      });

      container.appendChild(span);
    });

    // If there are more sessions than shown, append a '+N more' badge
    if (sessions.length > MAX_SHOW) {
      const more = document.createElement('span');
      more.className = 'session-more';
      more.textContent = `+${sessions.length - MAX_SHOW} más`;
      more.title = 'Hay más sesiones activas';
      container.appendChild(more);
    }

    listEl.appendChild(container);
  }

  function fetchSessions(){
    fetch(url, {credentials:'same-origin'})
      .then(r => r.json())
      .then(json => {
        if (json.success) render(json.data);
        else {
          listEl.textContent = 'No se pudo obtener sesiones';
        }
      }).catch(err => {
        console.error('Error cargando sesiones:', err);
        listEl.textContent = 'Error cargando sesiones';
      });
  }

  // Carga inicial
  fetchSessions();
  // Actualiza cada 30s
  setInterval(fetchSessions, 30000);
});
