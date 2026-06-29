/**
 * OHRS — Main JavaScript
 * Dependencies: Bootstrap 5, jQuery (loaded via CDN)
 */

$(function () {

  // ── Auto-dismiss flash alerts ──────────────────────────
  setTimeout(function () {
    $('.alert.auto-dismiss').fadeOut(400, function () { $(this).remove(); });
  }, 4500);

  // ── Smooth navbar scroll shadow ───────────────────────
  $(window).on('scroll.nav', function () {
    var scrolled = $(this).scrollTop() > 10;
    $('.ohrs-nav').toggleClass('scrolled', scrolled);
  });

  // ── Active nav link highlight ─────────────────────────
  var path = window.location.pathname.split('/').pop();
  $('.ohrs-nav .nav-link').each(function () {
    var href = $(this).attr('href');
    if (href && href.split('/').pop() === path) {
      $(this).addClass('active');
    }
  });

  // ── Counter animation (stats strip) ──────────────────
  function animateCounter($el) {
    var target = parseInt($el.data('count'), 10);
    var duration = 1400;
    var step = target / (duration / 16);
    var current = 0;
    var timer = setInterval(function () {
      current = Math.min(current + step, target);
      $el.text(Math.floor(current).toLocaleString());
      if (current >= target) clearInterval(timer);
    }, 16);
  }

  // Trigger counter when stats strip enters viewport
  var statsTriggered = false;
  function checkStats() {
    if (statsTriggered) return;
    var $strip = $('.stats-strip');
    if (!$strip.length) return;
    var top = $strip.offset().top;
    if ($(window).scrollTop() + $(window).height() > top + 80) {
      statsTriggered = true;
      $('[data-count]').each(function () { animateCounter($(this)); });
    }
  }
  $(window).on('scroll.stats', checkStats);
  checkStats();

  // ── Password toggle ───────────────────────────────────
  $(document).on('click', '.toggle-password', function () {
    var $btn = $(this);
    var $input = $($btn.data('target'));
    var isText = $input.attr('type') === 'text';
    $input.attr('type', isText ? 'password' : 'text');
    $btn.find('i').toggleClass('bi-eye bi-eye-slash');
  });

  // ── Confirm delete ────────────────────────────────────
  $(document).on('click', '.confirm-delete', function (e) {
    if (!confirm('Are you sure you want to delete this? This action cannot be undone.')) {
      e.preventDefault();
    }
  });

  // ── Image preview on upload ───────────────────────────
  $(document).on('change', '.img-preview-input', function () {
    var file = this.files[0];
    if (!file) return;
    var $preview = $($(this).data('preview'));
    if (!$preview.length) return;
    var reader = new FileReader();
    reader.onload = function (e) { $preview.attr('src', e.target.result).show(); };
    reader.readAsDataURL(file);
  });

  // ── AJAX form submission helper ───────────────────────
  $(document).on('submit', 'form.ajax-form', function (e) {
    e.preventDefault();
    var $form = $(this);
    var $btn  = $form.find('[type=submit]');
    var $msg  = $form.find('.form-message');
    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Please wait…');
    $msg.html('').hide();

    $.ajax({
      url:         $form.attr('action') || window.location.href,
      method:      'POST',
      data:        new FormData(this),
      processData: false,
      contentType: false,
      dataType:    'json',
    }).done(function (res) {
      if (res.success) {
        $msg.html('<div class="alert alert-success">' + res.message + '</div>').show();
        if (res.redirect) setTimeout(function () { window.location = res.redirect; }, 1200);
      } else {
        $msg.html('<div class="alert alert-danger">' + res.message + '</div>').show();
      }
    }).fail(function () {
      $msg.html('<div class="alert alert-danger">Something went wrong. Please try again.</div>').show();
    }).always(function () {
      $btn.prop('disabled', false).html($btn.data('original-text') || 'Submit');
    });
  });

  // ── Tooltip init ──────────────────────────────────────
  $('[data-bs-toggle="tooltip"]').each(function () {
    new bootstrap.Tooltip(this);
  });

  // ── Page fade-in ─────────────────────────────────────
  $('main').addClass('page-fade');

});
