<?php
session_start();
session_unset();        // Clear all session variables
session_destroy();      // Destroy the session
header("Location: home.html");  // Redirect to homepage after logout
exit();
?>
