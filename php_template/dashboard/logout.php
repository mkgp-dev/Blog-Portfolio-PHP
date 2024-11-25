<?php
// Destroy everything
session_unset();
session_destroy();

// Generate new id
session_regenerate_id(true);
header('Location: /');
exit();