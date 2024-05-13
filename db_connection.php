<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "pm";

// Kreiranje nove konekcije
$conn = new mysqli($servername, $username, $password, $database);

// Provjera
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Funkcije za CRUD operacije

// Kreiranje nove stranice
function createWebsite($name, $address)
{
  global $conn;
  $sql = "INSERT INTO websites (name, address) VALUES ('$name', '$address')";
  return $conn->query($sql);
}

// Dobavljanje stranica sa opcionim upitom za pretragu
function readWebsites($search_query = "")
{
  global $conn;
  $sql = "SELECT * FROM websites";
  // Ukoliko je prisutan upit za pretragu, dodaj WHERE klauzulu
  if (!empty($search_query)) {
    $search_query = $conn->real_escape_string($search_query); // Prevencija SQL Injection-a
    $sql .= " WHERE name LIKE '%$search_query%' OR address LIKE '%$search_query%'";
  }
  $result = $conn->query($sql);
  $websites = array();
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $websites[] = $row;
    }
  }
  return $websites;
}

// Funkcija za dobavljanje detalja o stranici pomoću id
function readWebsiteById($website_id)
{
  global $conn;

  // Primprema SQL upita
  $stmt = $conn->prepare("SELECT * FROM websites WHERE id = ?");

  // Dodjela parametara
  $stmt->bind_param("i", $website_id);

  // Izvršavanje
  $stmt->execute();

  // Dobavljanje rezultata
  $result = $stmt->get_result();

  // Ukoliko su vraćeni podaci...
  if ($result->num_rows > 0) {
    // ... dobavi detalje stranice
    $website = $result->fetch_assoc();
    return $website;
  } else {
    // Ako nije nađena stranica, vrati null
    return null;
  }

  // Zatvori upit
  $stmt->close();
}

// Ažuriranje stranice
function updateWebsite($id, $name, $address)
{
  global $conn;
  $sql = "UPDATE websites SET name='$name', address='$address' WHERE id=$id";
  return $conn->query($sql);
}

// Brisanje stranice
function deleteWebsite($website_id)
{
  global $conn;

  // Brisanje redova u tabeli password_history koji su povezani sa nalozima koji će se brisati
  $sql_delete_history = "DELETE ph FROM password_history ph
                        INNER JOIN accounts a ON ph.account_id = a.id
                        WHERE a.website_id = ?";
  $stmt_delete_history = $conn->prepare($sql_delete_history);
  $stmt_delete_history->bind_param("i", $website_id);
  $stmt_delete_history->execute();
  $stmt_delete_history->close();

  // Brisanje naloga vezanih za stranicu
  $sql_delete_accounts = "DELETE FROM accounts WHERE website_id = ?";
  $stmt_delete_accounts = $conn->prepare($sql_delete_accounts);
  $stmt_delete_accounts->bind_param("i", $website_id);
  $stmt_delete_accounts->execute();
  $stmt_delete_accounts->close();

  // Brisanje stranice
  $sql_delete_website = "DELETE FROM websites WHERE id = ?";
  $stmt_delete_website = $conn->prepare($sql_delete_website);
  $stmt_delete_website->bind_param("i", $website_id);
  $stmt_delete_website->execute();
  $stmt_delete_website->close();
}



// Dodavanje naloga
function createAccount($website_id, $username, $password)
{
  global $conn;
  $sql = "INSERT INTO accounts (website_id, username, password) VALUES ('$website_id', '$username', '$password')";

  if ($conn->query($sql)) {
    // Ako je upit bio uspješan, dobavi ID novo-dodanog naloga
    $account_id = $conn->insert_id;

    // Ako lozinka nije prazna, dodaj novi red u tabelu password_history
    if (!empty($password)) {
      $insert_history_sql = "INSERT INTO password_history (account_id, password, created_at) VALUES ('$account_id', '$password', NOW())";
      $conn->query($insert_history_sql);
    }

    return true;
  } else {
    return false;
  }
}

// Dobavljanje određenog naloga
function readAccountById($account_id)
{
  global $conn;
  $stmt = $conn->prepare("SELECT * FROM accounts WHERE id = ?");
  $stmt->bind_param("i", $account_id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $account = $result->fetch_assoc();
    return $account;
  } else {
    return null;
  }

  $stmt->close();
}

// Dobavljanje naloga određene stranice
function readAccountsByWebsiteId($website_id)
{
  global $conn;
  $website_id = intval($website_id); // Konverzija radi prevencije SQL injekcije
  $sql = "SELECT * FROM accounts WHERE website_id = $website_id";
  $result = $conn->query($sql);
  $accounts = array();
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $accounts[] = $row;
    }
  }
  return $accounts;
}

// Ažuriranje naloga
function updateAccount($id, $username, $password)
{
  global $conn;
  $sql = "UPDATE accounts SET username='$username', password='$password' WHERE id=$id";
  return $conn->query($sql);
}

// Brisanje naloga
function deleteAccount($id)
{
  global $conn;
  // Prvo izbriši istoriju iz tabele password_history
  $deleteHistorySql = "DELETE FROM password_history WHERE account_id=$id";
  $result = $conn->query($deleteHistorySql);
  if ($result) {
    // Brisanje naloga ako je brisanje istorije bilo uspješno
    $deleteAccountSql = "DELETE FROM accounts WHERE id=$id";
    $result = $conn->query($deleteAccountSql);
    return $result ? true : false;
  } else {
    return false;
  }
}

// Dobavljanje istorije određenog naloga
function readPasswordHistoryByAccountId($account_id)
{
  global $conn;
  $account_id = intval($account_id);
  $sql = "SELECT * FROM password_history WHERE account_id = $account_id ORDER BY created_at DESC";
  $result = $conn->query($sql);
  $passwordHistory = array();
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $passwordHistory[] = $row;
    }
  }
  return $passwordHistory;
}

// Novi unos u tabelu password_history
function createPasswordHistory($account_id, $password)
{
  global $conn;
  $created_at = date('Y-m-d H:i:s');
  $sql = "INSERT INTO password_history (account_id, password, created_at) VALUES ('$account_id', '$password', '$created_at')";
  return $conn->query($sql);
}

// Brisanje redova iz tabele password_history povezanih sa određenim nalogom
function deletePasswordHistoryByAccountId($account_id)
{
  global $conn;
  $sql = "DELETE FROM password_history WHERE account_id=$account_id";
  return $conn->query($sql);
}

// Zatvaranje konekcije
function closeConnection()
{
  global $conn;
  $conn->close();
}
