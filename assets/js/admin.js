/**
 * OHRS — Admin Panel JavaScript
 */

$(function () {

  // ── Sidebar mobile toggle ──────────────────────────────
  $('#sidebar-toggle, #sidebar-overlay').on('click', function () {
    $('.admin-sidebar').toggleClass('open');
    $('#sidebar-overlay').toggleClass('active');
  });

  // ── Active sidebar link ───────────────────────────────
  var path = window.location.pathname.split('/').pop();
  $('.sidebar-nav a').each(function () {
    var href = $(this).attr('href');
    if (href && href.split('/').pop() === path) {
      $(this).addClass('active');
    }
  });

  // ── Confirm action ────────────────────────────────────
  $(document).on('click', '.confirm-action', function (e) {
    var msg = $(this).data('confirm') || 'Are you sure?';
    if (!confirm(msg)) e.preventDefault();
  });

  // ── Data table quick search ───────────────────────────
  $('#table-search').on('input', function () {
    var q = $(this).val().toLowerCase();
    $('table.ohrs-table tbody tr').each(function () {
      $(this).toggle($(this).text().toLowerCase().includes(q));
    });
  });

  // ── Status change via AJAX ────────────────────────────
  $(document).on('change', '.status-select', function () {
    var $el  = $(this);
    var url  = $el.data('url');
    var id   = $el.data('id');
    var val  = $el.val();
    var csrf = $('meta[name=csrf]').attr('content');

    $.post(url, { id: id, status: val, csrf: csrf, ajax: 1 }, function (res) {
      if (res.success) {
        showToast(res.message || 'Status updated.', 'success');
      } else {
        showToast(res.message || 'Update failed.', 'danger');
      }
    }, 'json').fail(function () { showToast('Request failed.', 'danger'); });
  });

  // ── Toast notification ────────────────────────────────
  window.showToast = function (message, type) {
    type = type || 'info';
    var id = 'toast_' + Date.now();
    var html = [
      '<div id="' + id + '" class="toast align-items-center text-bg-' + type + ' border-0 show" role="alert">',
        '<div class="d-flex">',
          '<div class="toast-body">' + message + '</div>',
          '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>',
        '</div>',
      '</div>'
    ].join('');

    if (!$('#toast-container').length) {
      $('body').append('<div id="toast-container" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:9999"></div>');
    }
    $('#toast-container').append(html);
    setTimeout(function () { $('#' + id).fadeOut(300, function () { $(this).remove(); }); }, 3500);
  };

  // ── Chart.js helpers ──────────────────────────────────
  window.createLineChart = function (canvasId, labels, data, label, color) {
    var ctx = document.getElementById(canvasId);
    if (!ctx) return;
    return new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: label,
          data: data,
          borderColor: color || '#1a56db',
          backgroundColor: (color || '#1a56db') + '18',
          borderWidth: 2.5,
          pointRadius: 4,
          pointBackgroundColor: color || '#1a56db',
          tension: 0.4,
          fill: true,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { font: { size: 11 } } },
          x: { grid: { display: false }, ticks: { font: { size: 11 } } }
        }
      }
    });
  };

  window.createBarChart = function (canvasId, labels, data, label, colors) {
    var ctx = document.getElementById(canvasId);
    if (!ctx) return;
    return new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
          label: label,
          data: data,
          backgroundColor: colors || '#1a56db',
          borderRadius: 4,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          y: { beginAtZero: true, grid: { color: '#f3f4f6' }, ticks: { font: { size: 11 } } },
          x: { grid: { display: false }, ticks: { font: { size: 11 } } }
        }
      }
    });
  };

  window.createDoughnutChart = function (canvasId, labels, data, colors) {
    var ctx = document.getElementById(canvasId);
    if (!ctx) return;
    return new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: labels,
        datasets: [{ data: data, backgroundColor: colors, borderWidth: 0 }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'bottom', labels: { font: { size: 11 }, padding: 16 } }
        },
        cutout: '72%',
      }
    });
  };

  // ── Auto-dismiss alerts ───────────────────────────────
  setTimeout(function () {
    $('.alert.auto-dismiss').fadeOut(400);
  }, 4000);

  // ── Tooltip init ──────────────────────────────────────
  $('[data-bs-toggle="tooltip"]').each(function () {
    new bootstrap.Tooltip(this);
  });

  // ── Image preview ─────────────────────────────────────
  $(document).on('change', '.img-preview-input', function () {
    var file = this.files[0];
    if (!file) return;
    var $p = $($(this).data('preview'));
    if (!$p.length) return;
    var r = new FileReader();
    r.onload = function (e) { $p.attr('src', e.target.result).show(); };
    r.readAsDataURL(file);
  });

  // ── Print report ─────────────────────────────────────
  $('#print-report').on('click', function () { window.print(); });
});
