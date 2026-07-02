<?php
$admin_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
$cust_hash = '$2y$10$TKh8H1.PfuphPBtRcAlvIeUJEbMiYpfm5p.GBPlwFqHaVEFgZb3Oi';
echo "Admin 'password': " . (password_verify('password', $admin_hash) ? 'TRUE' : 'FALSE') . "\n";
echo "Cust 'password': " . (password_verify('password', $cust_hash) ? 'TRUE' : 'FALSE') . "\n";
