<?php
require_once 'includes/init.php';
$page_title = 'Browse Houses';
$page_meta  = 'Search and filter available rental houses by location, price, bedrooms, and capacity.';
$extra_js   = ['assets/js/search.js'];

// Initial load: apply any GET filters
$where  = ['h.status != "inactive"'];
$params = [];

if (!empty($_GET['location'])) {
    $where[]  = 'h.location LIKE :loc';
    $params[':loc'] = '%' . $_GET['location'] . '%';
}
if (!empty($_GET['min_rent'])) {
    $where[]  = 'h.rent >= :min';
    $params[':min'] = (float)$_GET['min_rent'];
}
if (!empty($_GET['max_rent'])) {
    $where[]  = 'h.rent <= :max';
    $params[':max'] = (float)$_GET['max_rent'];
}
if (!empty($_GET['bedrooms'])) {
    $op = (int)$_GET['bedrooms'] >= 4 ? '>=' : '=';
    $where[]  = "h.bedrooms $op :bed";
    $params[':bed'] = (int)$_GET['bedrooms'];
}
if (!empty($_GET['capacity'])) {
    $where[]  = 'h.capacity >= :cap';
    $params[':cap'] = (int)$_GET['capacity'];
}
if (!empty($_GET['status'])) {
    $where[]  = 'h.status = :st';
    $params[':st'] = $_GET['status'];
}

// Pagination
$page    = max(1, (int)($_GET['p'] ?? 1));
$perPage = 9;

$sql   = "SELECT COUNT(*) FROM houses h WHERE " . implode(' AND ', $where);
$stmt  = db()->prepare($sql);
$stmt->execute($params);
$total = (int)$stmt->fetchColumn();
$pg    = paginate($total, $perPage, $page);

$sql   = "SELECT h.*, hi.image_path
          FROM houses h
          LEFT JOIN house_images hi ON hi.house_id = h.id AND hi.is_primary = 1
          WHERE " . implode(' AND ', $where) . "
          ORDER BY h.status ASC, h.rent ASC
          LIMIT :limit OFFSET :offset";
$stmt  = db()->prepare($sql);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':limit',  $pg['perPage'], PDO::PARAM_INT);
$stmt->bindValue(':offset', $pg['offset'],  PDO::PARAM_INT);
$stmt->execute();
$houses = $stmt->fetchAll();

include_once 'includes/header.php';
?>

<!-- ── Page Header ───────────────────────────────────────── -->
<div class="page-header">
  <div class="container">
    <h1>Browse Properties</h1>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
        <li class="breadcrumb-item active">Houses</li>
      </ol>
    </nav>
  </div>
</div>

<section class="py-5">
  <div class="container">
    <div class="row g-4">

      <!-- ── Sidebar Filters ──────────────────────────────── -->
      <div class="col-lg-3">
        <form id="search-form" method="GET">
          <div class="filter-bar d-flex flex-column gap-3">
            <h6 class="mb-0" style="font-weight:700;">Filter Properties</h6>

            <div>
              <label class="form-label">Location</label>
              <div class="input-icon-wrap">
                <i class="bi bi-geo-alt"></i>
                <input type="text" name="location" class="form-control" placeholder="City or area…"
                       value="<?= e($_GET['location'] ?? '') ?>">
              </div>
            </div>

            <div>
              <label class="form-label">Min Rent (Rs.)</label>
              <input type="number" name="min_rent" class="form-control" placeholder="0"
                     value="<?= e($_GET['min_rent'] ?? '') ?>">
            </div>

            <div>
              <label class="form-label">Max Rent (Rs.)</label>
              <input type="number" name="max_rent" class="form-control" placeholder="Any"
                     value="<?= e($_GET['max_rent'] ?? '') ?>">
            </div>

            <div>
              <label class="form-label">Bedrooms</label>
              <select name="bedrooms" class="form-select">
                <option value="">Any</option>
                <?php foreach ([1,2,3,'4+'] as $b): ?>
                  <option value="<?= is_string($b) ? 4 : $b ?>"
                    <?= (($_GET['bedrooms'] ?? '') == (is_string($b) ? 4 : $b)) ? 'selected' : '' ?>>
                    <?= $b ?> Bedroom<?= $b != 1 ? 's' : '' ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div>
              <label class="form-label">Min Capacity</label>
              <select name="capacity" class="form-select">
                <option value="">Any</option>
                <?php foreach ([1,2,4,6,8] as $c): ?>
                  <option value="<?= $c ?>" <?= (($_GET['capacity'] ?? '') == $c) ? 'selected' : '' ?>>
                    <?= $c ?>+ People
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div>
              <label class="form-label">Status</label>
              <select name="status" class="form-select">
                <option value="">All</option>
                <option value="available" <?= (($_GET['status'] ?? '') === 'available') ? 'selected' : '' ?>>Available</option>
                <option value="reserved"  <?= (($_GET['status'] ?? '') === 'reserved')  ? 'selected' : '' ?>>Reserved</option>
              </select>
            </div>

            <button type="submit" class="btn btn-primary w-100">
              <i class="bi bi-search me-1"></i> Search
            </button>
            <button type="button" id="reset-search" class="btn btn-outline-secondary w-100">
              <i class="bi bi-x me-1"></i> Reset
            </button>
          </div>
        </form>
      </div>

      <!-- ── Results Grid ─────────────────────────────────── -->
      <div class="col-lg-9">
        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
          <span id="results-count" style="font-size:.875rem;color:var(--neutral-500);">
            <?= $pg['total'] ?> propert<?= $pg['total'] == 1 ? 'y' : 'ies' ?> found
          </span>
        </div>

        <div id="search-loading">
          <div class="spinner-border text-primary"></div>
        </div>

        <div class="row g-4" id="houses-grid">
          <?php if (empty($houses)): ?>
            <div class="col-12">
              <div class="empty-state">
                <span class="icon"><i class="bi bi-house-x"></i></span>
                <h5>No properties found</h5>
                <p>Try adjusting your search filters.</p>
              </div>
            </div>
          <?php else: ?>
            <?php foreach ($houses as $h): ?>
              <div class="col-sm-6 col-xl-4 card-appear">
                <div class="house-card">
                  <div class="house-card-img-wrap">
                    <img src="<?= $h['image_path'] ? 'assets/uploads/houses/' . e($h['image_path']) : 'assets/img/house-placeholder.jpg' ?>"
                         alt="<?= e($h['title']) ?>" loading="lazy">
                    <div class="house-card-status"><?= status_badge($h['status']) ?></div>
                  </div>
                  <div class="house-card-body">
                    <div class="house-card-price"><?= fmt_money($h['rent']) ?><span>/mo</span></div>
                    <div class="house-card-title"><?= e($h['title']) ?></div>
                    <div class="house-card-location"><i class="bi bi-geo-alt"></i><?= e($h['location']) ?></div>
                    <div class="house-card-meta">
                      <span><i class="bi bi-people"></i><?= (int)$h['capacity'] ?></span>
                      <span><i class="bi bi-door-open"></i><?= (int)$h['bedrooms'] ?> bed</span>
                      <span><i class="bi bi-droplet"></i><?= (int)$h['bathrooms'] ?> bath</span>
                    </div>
                    <a href="house-detail.php?id=<?= (int)$h['id'] ?>" class="btn btn-primary w-100 mt-3">View Details</a>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($pg['pages'] > 1): ?>
          <nav class="mt-4" aria-label="Page navigation">
            <ul class="pagination justify-content-center">
              <?php if ($pg['current'] > 1): ?>
                <li class="page-item">
                  <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['p' => $pg['current']-1])) ?>">
                    <i class="bi bi-chevron-left"></i>
                  </a>
                </li>
              <?php endif; ?>
              <?php for ($i = 1; $i <= $pg['pages']; $i++): ?>
                <li class="page-item <?= $i === $pg['current'] ? 'active' : '' ?>">
                  <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['p' => $i])) ?>"><?= $i ?></a>
                </li>
              <?php endfor; ?>
              <?php if ($pg['current'] < $pg['pages']): ?>
                <li class="page-item">
                  <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['p' => $pg['current']+1])) ?>">
                    <i class="bi bi-chevron-right"></i>
                  </a>
                </li>
              <?php endif; ?>
            </ul>
          </nav>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<?php include_once 'includes/footer.php'; ?>
