/**
 * OHRS — Live House Search
 * Performs AJAX filtering as the user types / selects filters.
 */

$(function () {
  if (!$('#search-form').length) return;

  var $form    = $('#search-form');
  var $grid    = $('#houses-grid');
  var $loading = $('#search-loading');
  var $count   = $('#results-count');
  var timer    = null;

  // Render a house card from JSON data
  function renderCard(h) {
    var statusClass = {
      available: 'success', reserved: 'warning', occupied: 'danger', inactive: 'secondary'
    }[h.status] || 'secondary';

    var img = h.image
      ? 'assets/uploads/houses/' + h.image
      : 'assets/img/house-placeholder.jpg';

    return [
      '<div class="col-sm-6 col-lg-4 card-appear">',
        '<div class="house-card">',
          '<div class="house-card-img-wrap">',
            '<img src="' + img + '" alt="' + escHtml(h.title) + '" loading="lazy">',
            '<div class="house-card-status">',
              '<span class="badge bg-' + statusClass + '">' + ucFirst(h.status) + '</span>',
            '</div>',
          '</div>',
          '<div class="house-card-body">',
            '<div class="house-card-price">Rs. ' + Number(h.rent).toLocaleString() + '<span>/mo</span></div>',
            '<div class="house-card-title">' + escHtml(h.title) + '</div>',
            '<div class="house-card-location"><i class="bi bi-geo-alt"></i>' + escHtml(h.location) + '</div>',
            '<div class="house-card-meta">',
              '<span><i class="bi bi-people"></i>' + h.capacity + ' guests</span>',
              '<span><i class="bi bi-door-open"></i>' + h.bedrooms + ' bed</span>',
              '<span><i class="bi bi-droplet"></i>' + h.bathrooms + ' bath</span>',
            '</div>',
            '<a href="house-detail.php?id=' + h.id + '" class="btn btn-primary w-100 mt-3">View Details</a>',
          '</div>',
        '</div>',
      '</div>'
    ].join('');
  }

  function escHtml(str) {
    var d = document.createElement('div');
    d.appendChild(document.createTextNode(str || ''));
    return d.innerHTML;
  }
  function ucFirst(str) { return str.charAt(0).toUpperCase() + str.slice(1); }

  function doSearch() {
    var data = $form.serialize();
    $grid.addClass('loading');
    $loading.css('display', 'flex');

    $.getJSON('ajax/search-houses.php', data, function (res) {
      $grid.removeClass('loading').html('');
      $loading.hide();

      if (!res.houses || res.houses.length === 0) {
        $grid.html([
          '<div class="col-12">',
            '<div class="empty-state">',
              '<span class="icon"><i class="bi bi-house-x"></i></span>',
              '<h5>No properties found</h5>',
              '<p>Try adjusting your search filters.</p>',
            '</div>',
          '</div>'
        ].join(''));
        if ($count.length) $count.text('0 properties found');
        return;
      }

      if ($count.length) {
        $count.text(res.houses.length + ' propert' + (res.houses.length === 1 ? 'y' : 'ies') + ' found');
      }

      $.each(res.houses, function (i, h) {
        $grid.append(renderCard(h));
      });
    }).fail(function () {
      $grid.removeClass('loading').html('<div class="col-12"><div class="alert alert-danger">Search failed. Please try again.</div></div>');
      $loading.hide();
    });
  }

  // Debounce text inputs
  $form.on('input', 'input[type=text], input[type=number]', function () {
    clearTimeout(timer);
    timer = setTimeout(doSearch, 420);
  });

  // Immediate on select change
  $form.on('change', 'select', function () {
    clearTimeout(timer);
    doSearch();
  });

  // Manual submit (search button)
  $form.on('submit', function (e) {
    e.preventDefault();
    clearTimeout(timer);
    doSearch();
  });

  // Reset button
  $('#reset-search').on('click', function () {
    $form[0].reset();
    doSearch();
  });
});
