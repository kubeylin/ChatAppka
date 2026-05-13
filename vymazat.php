<?php
// pages/vymazat.php – Vymazanie hry (DELETE)
require_once __DIR__ . '/../includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: ../index.php'); exit; }

// Overime ze hra existuje
$hra = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM hry WHERE id = $id"));
if (!$hra) { header('Location: ../index.php'); exit; }

// Vymazeme
mysqli_query($conn, "DELETE FROM hry WHERE id = $id");

header('Location: ../index.php?msg=vymazana');
exit;
