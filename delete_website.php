<?php
// Uključi datoteku za konekciju sa bazom podataka i funkcije za CRUD operacije
include 'db_connection.php';

// Provjeri da li je dostavljen ID veb stranice
if (!isset($_GET['id'])) {
  // Preusmjeri nazad na pregled stranica
  header("Location: index.php");
  exit;
}

// Dobavi ID veb stranice iz URL-a
$website_id = $_GET['id'];

// Obriši veb stranicu i njene povezane naloge
deleteWebsite($website_id);

// Preusmjeri nazad na pregled veb stranica
header("Location: index.php");
exit;
