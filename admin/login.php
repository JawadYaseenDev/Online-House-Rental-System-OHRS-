<?php
/**
 * OHRS — Admin panel login page (separate from public login)
 * Accessed when admin visits admin/ directly without a session.
 */
require_once dirname(__DIR__) . '/includes/init.php';

if (is_admin()) redirect('index.php');

// If logged in as customer, redirect home
if (is_logged_in()) {
    flash('danger','You do not have administrator access.');
    redirect('../index.php');
}

// Redirect to public login (which handles admin routing)
redirect('../login.php');
