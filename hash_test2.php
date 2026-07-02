<?php
$cust_hash = '$2y$10$TKh8H1.PfuphPBtRcAlvIeUJEbMiYpfm5p.GBPlwFqHaVEFgZb3Oi';
echo "Cust 'Test@1234': " . (password_verify('Test@1234', $cust_hash) ? 'TRUE' : 'FALSE') . "\n";
echo "Cust 'secret': " . (password_verify('secret', $cust_hash) ? 'TRUE' : 'FALSE') . "\n";
