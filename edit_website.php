<?php
// Uključi fajl za konekciju sa bazom podataka i funkcije za CRUD operacije
include 'db_connection.php';

// Provjeri da li je dostavljen ID stranice
if (!isset($_GET['id'])) {
  // Preusmjeri nazad na pregled stranica
  header("Location: index.php");
  exit;
}

// Dobavi ID stranice iz URL-a
$website_id = $_GET['id'];

// Dobavi detalje stranice na osnovu ID-ja
$website = readWebsiteById($website_id);

// Dobavi naloge za veb sajt
$accounts = readAccountsByWebsiteId($website_id);

// Provjeri da li veb sajt postoji
if (!$website) {
  // Preusmjeri nazad na pregled stranica
  header("Location: index.php");
  exit;
}

// Provjeri da li je forma poslata za ažuriranje stranice
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Dobavi podatke iz forme
  $name = $_POST['name'];
  $address = $_POST['address'];

  // Ažuriraj detalje stranice u bazi podataka
  updateWebsite($website_id, $name, $address);

  // Ažuriraj naloge
  foreach ($_POST['accounts'] as $account_id => $account_data) {
    if (isset($account_data['delete'])) {
      // Obriši nalog i njegovu povezanu istoriju
      obrisiNalogIstoriju($account_id);
    } else {
      $username = trim($account_data['username']);
      $password = trim($account_data['password']);

      // Provjeri da li korisničko ime i lozinka nisu prazni
      if (!empty($username) && !empty($password)) {
        // Provjeri da li postoji ID naloga, ako da, ažuriraj nalog, u suprotnom dodaj novi nalog
        if (substr($account_id, 0, 3) !== 'new') {
          // Provjeri da li nalog postoji
          $existing_account = readAccountById($account_id);
          if ($existing_account && $existing_account['password'] !== $password) {
            // Lozinka je promenjena, ažuriraj nalog i ubaci u istoriju
            updateAccount($account_id, $username, $password);
            // Ubaci novi unos u tabelu za istoriju lozinki
            createPasswordHistory($account_id, $password);
          } else {
            // Lozinka ostaje ista, samo ažuriraj nalog
            updateAccount($account_id, $username, $password);
          }
        } else {
          createAccount($website_id, $username, $password);
        }
      }
    }
  }

  // Preusmjeri na trenutnu stranicu da osveži podatke
  header("Location: edit_website.php?id=" . $website_id);
  exit;
}

// Funkcija za brisanje naloga i njegove povezane istorije
function obrisiNalogIstoriju($account_id)
{
  // Obriši istoriju lozinki
  deletePasswordHistoryByAccountId($account_id);
  // Obriši nalog
  deleteAccount($account_id);
}
?>

<!doctype html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <!-- <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css"> -->

  <!-- Font Awesome CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <!-- <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css"> -->

  <!-- Custom CSS -->
  <link rel="stylesheet" type="text/css" href="css/custom.css">

  <title>Uređivanje Stranice</title>
</head>

<body>
  <div class="container my-5 px-5" style="max-width: 900px;">
    <h2>Uređivanje stranice</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $website_id; ?>">
      <div class="row mb-2 mt-4">
        <label for="name" class="col-sm-2 col-form-label">Naziv stranice</label>
        <div class="col-sm-10">
          <input type="text" class="form-control border-secondary" id="name" name="name" value="<?php echo $website['name']; ?>">
        </div>
      </div>
      <div class="row mb-4">
        <label for="address" class="col-sm-2 col-form-label">Adresa stranice</label>
        <div class="col-sm-10">
          <input type="text" class="form-control border-secondary" id="address" name="address" value="<?php echo $website['address']; ?>">
        </div>
      </div>

      <!-- Prikaz naloga -->
      <div class="container border border-secondary rounded p-0" id="accounts-container">
        <div class="row p-4">
          <div class="col-sm-11">
            <h3>Nalozi</h3>
          </div>
          <div class="col-sm-1">
            <!-- Dugme za dodavanje naloga -->
            <button type="button" class="btn btn-success" id="add-account-btn"><i class="fas fa-plus"></i></button>
          </div>
        </div>
        <?php if (!empty($accounts)) : ?>
          <?php foreach ($accounts as $account) : ?>
            <div class="container border-top border-secondary p-4" id="account_<?php echo $account['id']; ?>">
              <input type="hidden" name="accounts[<?php echo $account['id']; ?>][id]" value="<?php echo $account['id']; ?>">
              <div class="row mb-1">
                <label for="username<?php echo $account['id']; ?>" class="col-sm-2 col-form-label">Korisničko ime</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control border border-secondary" id="username<?php echo $account['id']; ?>" name="accounts[<?php echo $account['id']; ?>][username]" value="<?php echo $account['username']; ?>">
                </div>
                <div class="col-sm-1">
                  <!-- Dugme za brisanje naloga -->
                  <input type="checkbox" class="btn-check delete-account-btn" id="btn-check-<?php echo $account['id']; ?>" autocomplete="off" name="accounts[<?php echo $account['id']; ?>][delete]" value="<?php echo $account['id']; ?>">
                  <label class="btn btn-outline-danger" for="btn-check-<?php echo $account['id']; ?>"><i class="fas fa-trash-alt"></i></label>
                </div>
              </div>
              <div class="row">
                <label for="password<?php echo $account['id']; ?>" class="col-sm-2 col-form-label">Lozinka</label>
                <div class="col-sm-9">
                  <div class="input-group">
                    <input type="password" class="form-control border border-secondary" id="password<?php echo $account['id']; ?>" name="accounts[<?php echo $account['id']; ?>][password]" value="<?php echo $account['password']; ?>">
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword<?php echo $account['id']; ?>" onclick="togglePasswordVisibility('<?php echo $account['id']; ?>')">
                      <i class="fas fa-eye"></i>
                    </button>
                  </div>
                </div>
                <div class="col-sm-1">
                  <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo $account['id']; ?>" aria-expanded="false" aria-controls="collapse-collapse<?php echo $account['id']; ?>">
                    <i class="fas fa-history"></i>
                  </button>
                </div>
              </div>
              <div class="collapse" id="collapse-<?php echo $account['id']; ?>">
                <div class="mt-3">
                  <?php $passwordHistory = readPasswordHistoryByAccountId($account['id']); ?>
                  <?php if (!empty($passwordHistory)) : ?>
                    <div class="row">
                      <div class="col-sm-9 offset-sm-2">
                        <h5>Istorija lozinki</h5>
                      </div>
                    </div>
                    <?php foreach ($passwordHistory as $history) : ?>
                      <div class="row">
                        <div class="col-sm-9 offset-sm-2">
                          <div class="input-group mb-1">
                            <input type="password" class="form-control border border-secondary" id="password-h<?php echo $history['id']; ?>" value="<?php echo $history['password']; ?>" disabled>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword-h<?php echo $history['id']; ?>" onclick="togglePasswordVisibility('-h<?php echo $history['id']; ?>')">
                              <i class="fas fa-eye"></i>
                            </button>
                            <input type="text" class="form-control border border-secondary text-center" value="<?php echo date('d.m.Y H:i', strtotime($history['created_at'])); ?>" style="max-width: 150px;" disabled>
                          </div>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  <?php else : ?>
                    <p>Nema istorije.</p>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else : ?>
          <p class="m-3">Nema naloga.</p>
        <?php endif; ?>
      </div>

      <div class="p-3">
        <button type="submit" class="btn btn-primary">Potvrdi</button>
        <a href="index.php" class="btn btn-secondary">Otkaži</a>
      </div>
    </form>
  </div>

  <!-- Bootstrap Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <!-- <script src="js/bootstrap.bundle.min.js"></script> -->

  <!-- Font Awesome JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/js/all.min.js" integrity="sha512-u3fPA7V8qQmhBPNT5quvaXVa1mnnLSXUep5PS1qo5NRzHwG19aHmNJnj1Q8hpA/nBWZtZD4r4AX6YOt5ynLN2g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <!-- <script src="js/all.min.js"></script> -->

  <script>
    // Funkcija za sakrivanje kontejnera
    function hideAccountContainer(accountId) {
      var accountContainer = document.getElementById("account_" + accountId);
      if (accountContainer) {
        accountContainer.hidden = true;
      }
    }

    // Funkcija za brisanje kontejnera
    function removeAccountContainer(accountId) {
      var accountContainer = document.getElementById("account_" + accountId);
      if (accountContainer) {
        accountContainer.remove();
      }
    }

    // Dodjeli prisluškivače za dugmad za brisanje
    var deleteButtons = document.querySelectorAll(".delete-account-btn");
    deleteButtons.forEach(function(button) {
      button.addEventListener("click", function() {
        var accountId = this.value;
        hideAccountContainer(accountId);
      });
    });

    // Funkcija za dodavanje kontejnera
    function addNewAccountContainer() {
      var accountsContainer = document.getElementById("accounts-container");
      var newAccountId = "new" + Date.now(); // Generiši jedinstveni id za novi nalog
      var newAccountContainer = document.createElement("div");
      newAccountContainer.className = "container border-top p-4";
      newAccountContainer.id = "account_" + newAccountId;
      newAccountContainer.innerHTML = `
      <input type="hidden" name="accounts[${newAccountId}][id]" value="${newAccountId}">
      <div class="row mb-1">
        <label for="username${newAccountId}" class="col-sm-2 col-form-label">Korisničko ime</label>
        <div class="col-sm-9">
          <input type="text" class="form-control" id="username${newAccountId}" name="accounts[${newAccountId}][username]" value="">
        </div>
        <div class="col-sm-1">
          <!-- Delete button for the new account -->
          <input type="checkbox" class="btn-check delete-account-btn" id="btn-check-${newAccountId}" autocomplete="off">
          <label class="btn btn-outline-danger" for="btn-check-${newAccountId}"><i class="fas fa-trash-alt"></i></label>
        </div>
      </div>
      <div class="row">
        <label for="password${newAccountId}" class="col-sm-2 col-form-label">Lozinka</label>
        <div class="col-sm-9">
          <div class="input-group">
            <input type="password" class="form-control" id="password${newAccountId}" name="accounts[${newAccountId}][password]" value="">
            <button class="btn btn-outline-secondary" type="button" id="togglePassword${newAccountId}" onclick="togglePasswordVisibility('${newAccountId}')">
              <i class="fas fa-eye"></i>
            </button>
          </div>
        </div>
      </div>
    `;
      accountsContainer.appendChild(newAccountContainer);

      // Dodjeli prisluškivače za dugmad za brisanje novo-dodanih naloga
      var newDeleteButton = newAccountContainer.querySelector(".delete-account-btn");
      newDeleteButton.addEventListener("click", function() {
        removeAccountContainer(newAccountId);
      });
    }

    // Dodjeli prisluškivače za dugmad za dodavanje naloga
    var addAccountBtn = document.getElementById("add-account-btn");
    addAccountBtn.addEventListener("click", function() {
      addNewAccountContainer();
    });

    function togglePasswordVisibility(accountId) {
      var passwordField = document.getElementById("password" + accountId);
      var toggleButton = document.getElementById("togglePassword" + accountId);
      if (passwordField.type === "password") {
        passwordField.type = "text";
        toggleButton.innerHTML = '<i class="fas fa-eye-slash"></i>';
      } else {
        passwordField.type = "password";
        toggleButton.innerHTML = '<i class="fas fa-eye"></i>';
      }
    }
  </script>
</body>

</html>