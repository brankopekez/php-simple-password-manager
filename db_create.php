<?php
// Parametri za povezivanje sa bazom podataka
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pm";

// Kreiranje konekcije
$conn = new mysqli($servername, $username, $password);

// Provjera konekcije
if ($conn->connect_error) {
  die("Neuspelo povezivanje: " . $conn->connect_error);
}

// Kreiranje baze podataka
$sql_create_db = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql_create_db) === TRUE) {
  echo "Baza podataka uspješno kreirana<br>";
} else {
  echo "Greška prilikom kreiranja baze podataka: " . $conn->error;
}

// Izbor baze podataka
$conn->select_db($dbname);

// SQL upiti za kreiranje tabela
$sql_create_websites_table = "CREATE TABLE IF NOT EXISTS websites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    address VARCHAR(255) NOT NULL
)";

$sql_create_accounts_table = "CREATE TABLE IF NOT EXISTS accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    website_id INT NOT NULL,
    username VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    FOREIGN KEY (website_id) REFERENCES websites(id)
)";

$sql_create_password_history_table = "CREATE TABLE IF NOT EXISTS password_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_id INT NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME,
    FOREIGN KEY (account_id) REFERENCES accounts(id)
)";

// Izvršavanje SQL upita za kreiranje tabela
if ($conn->query($sql_create_websites_table) === TRUE) {
  echo "Tabela 'websites' uspješno kreirana<br>";
} else {
  echo "Greška prilikom kreiranja tabele 'websites': " . $conn->error;
}

if ($conn->query($sql_create_accounts_table) === TRUE) {
  echo "Tabela 'accounts' uspješno kreirana<br>";
} else {
  echo "Greška prilikom kreiranja tabele 'accounts': " . $conn->error;
}

if ($conn->query($sql_create_password_history_table) === TRUE) {
  echo "Tabela 'password_history' uspješno kreirana<br>";
} else {
  echo "Greška prilikom kreiranja tabele 'password_history': " . $conn->error;
}

// Ubacivanje podataka u tabele
$sql_insert_websites = "INSERT INTO websites (name, address) VALUES
    ('Google', 'https://www.google.com'),
    ('Facebook', 'https://www.facebook.com'),
    ('Amazon', 'https://www.amazon.com'),
    ('Twitter', 'https://www.twitter.com'),
    ('Instagram', 'https://www.instagram.com'),
    ('LinkedIn', 'https://www.linkedin.com'),
    ('YouTube', 'https://www.youtube.com'),
    ('Reddit', 'https://www.reddit.com')";

$sql_insert_accounts = "INSERT INTO accounts (website_id, username, password) VALUES
    (1, 'korisnik1', 'lozinka1'),
    (1, 'korisnik2', 'lozinka2'),
    (2, 'korisnik3', 'lozinka3'),
    (3, 'korisnik4', 'lozinka4'),
    (3, 'korisnik5', 'lozinka5'),
    (3, 'korisnik6', 'lozinka6'),
    (4, 'korisnik7', 'lozinka7'),
    (5, 'korisnik8', 'lozinka8'),
    (5, 'korisnik9', 'lozinka9'),
    (5, 'korisnik10', 'lozinka10'),
    (6, 'korisnik11', 'lozinka11'),
    (7, 'korisnik12', 'lozinka12')";

$sql_insert_password_history = "INSERT INTO password_history (account_id, password, created_at) VALUES
    (1, 'lozinka1', NOW()),
    (2, 'lozinka2', NOW() - INTERVAL 1 DAY),
    (3, 'lozinka3', NOW() - INTERVAL 2 DAY),
    (4, 'lozinka4', NOW() - INTERVAL 3 DAY),
    (5, 'lozinka5', NOW() - INTERVAL 4 DAY),
    (6, 'lozinka6', NOW() - INTERVAL 5 DAY),
    (7, 'lozinka7', NOW() - INTERVAL 6 DAY),
    (8, 'lozinka8', NOW() - INTERVAL 7 DAY),
    (9, 'lozinka9', NOW() - INTERVAL 8 DAY),
    (10, 'lozinka10', NOW() - INTERVAL 9 DAY),
    (11, 'lozinka11', NOW() - INTERVAL 10 DAY),
    (12, 'lozinka12', NOW() - INTERVAL 11 DAY)";

if ($conn->query($sql_insert_websites) === TRUE) {
  echo "Podaci uspješno ubačeni u tabelu 'websites'<br>";
} else {
  echo "Greška prilikom ubacivanja podataka u tabelu 'websites': " . $conn->error;
}

if ($conn->query($sql_insert_accounts) === TRUE) {
  echo "Podaci uspješno ubačeni u tabelu 'accounts'<br>";
} else {
  echo "Greška prilikom ubacivanja podataka u tabelu 'accounts': " . $conn->error;
}

if ($conn->query($sql_insert_password_history) === TRUE) {
  echo "Podaci uspješno ubačeni u tabelu 'password_history'<br>";
} else {
  echo "Greška prilikom ubacivanja podataka u tabelu 'password_history': " . $conn->error;
}

// Zatvaranje konekcije
$conn->close();
