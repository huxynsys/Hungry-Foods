<?php
// Redirect the admin folder URL to the login page instead of showing a directory listing.
header('Location: admin-login.php', true, 302);
exit;
