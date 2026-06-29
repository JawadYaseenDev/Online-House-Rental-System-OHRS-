<?php
require_once '../../includes/init.php';
require_admin();

// This file handles house save (add/edit) — routed from house-add.php and house-edit.php
// Actual logic is in house-delete.php (named for routing, handles both save and delete)
// Redirect to house-delete.php which contains the full handler
redirect('../../actions/admin/house-delete.php');
