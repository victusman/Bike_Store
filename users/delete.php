<?php
require_once __DIR__ . '/../db.php';

$id = $_GET['id'] ?? null;
if (!$id) { header('Location: list.php'); exit; }

$pdo->prepare('DELETE FROM usuarios WHERE user_id = :id')->execute(['id'=>$id]);
header('Location: list.php');
exit;
?>