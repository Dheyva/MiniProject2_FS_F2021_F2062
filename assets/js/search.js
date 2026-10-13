/**
 * search.js - AJAX (fetch API) live search + dynamic loading for the Student Dashboard.
 * No page refresh: results are fetched as JSON and rendered into #projectList.
 */
(function () {
  'use strict';

  var list = document.getElementById('projectList');
  var input = document.getElementById('liveSearch');
  var status = document.getElementById('searchStatus');
  if (!list || !input) return;

  var endpoint = list.dataset.endpoint;
  var downloadUrl = list.dataset.download;
  var timer = null;
  var requestId = 0;            // ignore out-of-order responses

  function esc(s) {
    var d = document.createElement('div');
    d.textContent = s == null ? '' : String(s);
    return d.innerHTML;
  }

  function card(p) {
    var tags = p.tech_stack.map(function (t) { return '<span class="tech-badge me-1">' + esc(t) + '</span>'; }).join('');
    var btn = p.has_file
      ? '<a class="btn btn-outline-primary btn-sm" href="' + downloadUrl + p.id + '"><i class="bi bi-download me-1"></i>Report</a>'
      : '<span class="text-muted small">No file</span>';
    return '<div class="col-12 col-md-6 col-xl-4">' +
      '<div class="card project-card"><div class="card-body d-flex flex-column">' +
        '<div class="d-flex justify-content-between mb-2">' +
          '<span class="badge bg-primary">' + esc(p.category) + '</span>' +
          '<small class="text-muted"><i class="bi bi-clock me-1"></i>' + esc(p.date) + '</small></div>' +
        '<h6 class="fw-bold mb-1">' + esc(p.title) + '</h6>' +
        '<p class="desc mb-2">' + esc(p.description) + '</p>' +
        '<div class="small fw-semibold mb-1">Tech Stack:</div><div class="mb-3">' + tags + '</div>' +
        '<div class="d-flex justify-content-between align-items-center border-top pt-2 mt-auto">' +
          '<small><i class="bi bi-person-fill me-1"></i>' + esc(p.student) + '</small>' + btn + '</div>' +
      '</div></div></div>';
  }

  function render(data) {
    if (!data.projects.length) {
      list.innerHTML = '<div class="col-12"><div class="alert alert-secondary mb-0">No projects found.</div></div>';
    } else {
      list.innerHTML = data.projects.map(card).join('');
    }
    status.textContent = data.count + ' project' + (data.count === 1 ? '' : 's') + ' found';
  }

  function load(q) {
    var id = ++requestId;
    status.textContent = 'Loading...';
    fetch(endpoint + '?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
      .then(function (res) {
        if (res.status === 401) { window.location.href = endpoint.replace('student/ajax_search.php', 'login.php'); throw new Error('expired'); }
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return res.json();
      })
      .then(function (data) { if (id === requestId) render(data); })
      .catch(function (err) {
        if (err.message === 'expired' || id !== requestId) return;
        list.innerHTML = '';
        status.innerHTML = '<span class="text-danger">Could not load projects. Please try again.</span>';
      });
  }

  // debounce typing so we don't hit the server on every keystroke
  input.addEventListener('input', function () {
    clearTimeout(timer);
    timer = setTimeout(function () { load(input.value.trim()); }, 250);
  });

  load('');  // dynamic loading on page open
})();
