<?php
require_once 'includes/init.php';
logout_user();
flash('success', 'You have been logged out successfully.');
redirect('login.php');
