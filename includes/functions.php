<?php
/**
 * Utility Functions — OHRS
 */

// ── Output sanitization ──────────────────────────────────────
function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

// ── Flash message helpers ────────────────────────────────────
function flash(string $type, string $message): void
{
    $_SESSION['flash'] = compact('type', 'message');
}

function get_flash(): ?array
{
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

function render_flash(): void
{
    $f = get_flash();
    if (!$f) return;
    $icons = ['success'=>'check-circle','danger'=>'x-circle','warning'=>'exclamation-triangle','info'=>'info-circle'];
    $icon  = $icons[$f['type']] ?? 'info-circle';
    echo '<div class="alert alert-' . e($f['type']) . ' alert-dismissible d-flex align-items-center fade show" role="alert">
            <i class="bi bi-' . $icon . '-fill me-2"></i>
            <div>' . e($f['message']) . '</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>';
}

// ── File upload helper ───────────────────────────────────────
function upload_image(array $file, string $dir, int $maxMB = 5): string|false
{
    $allowed = ['image/jpeg','image/png','image/webp'];
    if ($file['error'] !== UPLOAD_ERR_OK) return false;
    if ($file['size'] > $maxMB * 1048576) return false;
    $mime = mime_content_type($file['tmp_name']);
    if (!in_array($mime, $allowed, true)) return false;

    $ext  = match($mime) {
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        default      => 'jpg',
    };
    $filename = uniqid('img_', true) . '.' . $ext;
    $dest     = rtrim($dir, '/') . '/' . $filename;

    if (!is_dir($dir)) mkdir($dir, 0755, true);
    if (!move_uploaded_file($file['tmp_name'], $dest)) return false;

    return $filename;
}

// ── Pagination helper ────────────────────────────────────────
function paginate(int $total, int $perPage, int $current): array
{
    $pages = (int)ceil($total / $perPage);
    return [
        'total'   => $total,
        'pages'   => max($pages, 1),
        'current' => max(1, min($current, $pages)),
        'offset'  => ($current - 1) * $perPage,
        'perPage' => $perPage,
    ];
}

// ── Format currency ──────────────────────────────────────────
function fmt_money(float $amount): string
{
    return 'Rs. ' . number_format($amount, 0);
}

// ── Status badge HTML ────────────────────────────────────────
function status_badge(string $status): string
{
    $map = [
        'available'  => 'success',
        'reserved'   => 'warning',
        'occupied'   => 'danger',
        'inactive'   => 'secondary',
        'pending'    => 'warning',
        'approved'   => 'success',
        'cancelled'  => 'danger',
        'completed'  => 'info',
        'paid'       => 'success',
        'failed'     => 'danger',
        'active'     => 'success',
        'expired'    => 'secondary',
        'banned'     => 'danger',
    ];
    $cls = $map[$status] ?? 'secondary';
    return '<span class="badge bg-' . $cls . '">' . ucfirst(e($status)) . '</span>';
}

// ── House primary image ──────────────────────────────────────
function house_image(array $house_images, string $fallback = 'assets/img/house-placeholder.jpg'): string
{
    foreach ($house_images as $img) {
        if ($img['is_primary']) {
            return 'assets/uploads/houses/' . e($img['image_path']);
        }
    }
    return !empty($house_images)
        ? 'assets/uploads/houses/' . e($house_images[0]['image_path'])
        : $fallback;
}

// ── Active-offer check ───────────────────────────────────────
function active_offers(PDO $db): array
{
    $stmt = $db->prepare("SELECT o.*, h.title AS house_title
                          FROM offers o
                          LEFT JOIN houses h ON h.id = o.house_id
                          WHERE o.status = 'active' AND o.end_date >= CURDATE()
                          ORDER BY o.end_date ASC");
    $stmt->execute();
    return $stmt->fetchAll();
}

// ── Redirect ─────────────────────────────────────────────────
function redirect(string $url): never
{
    header("Location: $url");
    exit;
}

// ── CSRF helpers ─────────────────────────────────────────────
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): bool
{
    return isset($_POST['csrf'], $_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], $_POST['csrf']);
}
