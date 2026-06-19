<?php
session_start();
session_unset();
session_destroy();

// Kembalikan ke halaman beranda setelah logout
header("Location: index.php");
exit();
?>