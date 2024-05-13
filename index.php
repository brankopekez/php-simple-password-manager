<?php
// Uključi fajl za konekciju sa bazom podataka i funkcije za CRUD operacije
include 'db_connection.php';

// Dobavi sve stranice ili filtrirane stranice na osnovu pretrage
if (isset($_POST['search']) && !empty($_POST['search'])) {
  // Ako je zadat upit za pretragu, filtriraj stranice na osnovu naziva ili adrese
  $search = $_POST['search'];
  $websites = readWebsites($search);
} else {
  // Ako nije zadat upit za pretragu, dobavi sve stranice
  $websites = readWebsites();
}

// Dobavi naloge za svaki veb sajt
foreach ($websites as &$website) {
  $website['accounts'] = readAccountsByWebsiteId($website['id']);
}
unset($website);

// Zatvori konekciju
closeConnection();
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

  <title>Uređivač Lozinki</title>
</head>

<body>
  <div class="container my-5 px-5">
    <h1 class="mt-4 mb-4">Uređivač lozinki - Pregled stranica</h1>
    <!-- Polje za pretragu -->
    <form action="index.php" method="post" class="mt-3 mb-4">
      <div class="input-group">
        <input type="text" class="form-control" placeholder="Pretraga po nazivu ili adresi" name="search" value="<?php if (isset($search)) echo $search; ?>">
        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
        <?php if (isset($search) && !empty($search)) : ?>
          <!-- Dugme za poništavanje pretrage -->
          <a href="index.php" class="btn btn-secondary" role="button"><i class="fas fa-times"></i></a>
        <?php endif; ?>
      </div>
    </form>

    <div class="row row-cols-auto g-4">
      <?php foreach ($websites as $website) : ?>
        <div class="col">
          <div class="card h-100 border border-secondary" style="width: 18rem;">
            <div class="card-header py-3 pe-4">
              <div class="row">
                <div class="col-10 text-nowrap overflow-hidden">
                  <h5 class="card-title mt-1"><?php echo $website['name']; ?></h5>
                </div>
                <div class="col-2">
                  <a href="edit_website.php?id=<?php echo $website['id']; ?>" class="btn btn-sm btn-outline-secondary" role="button">
                    <i class="fas fa-edit"></i>
                  </a>
                </div>
              </div>
              <div class="row">
                <div class="col-10 text-nowrap overflow-hidden">
                  <a href="<?php echo $website['address']; ?>" class="link-secondary">
                    <h6 class="card-subtitle text-muted mt-1"><?php echo $website['address']; ?></h6>
                  </a>
                </div>
                <div class="col-2">
                  <a href="delete_website.php?id=<?php echo $website['id']; ?>" class="btn btn-sm btn-outline-danger" role="button">
                    <i class="fas fa-trash-alt"></i>
                  </a>
                </div>
              </div>
            </div>
            <div class="card-body p-4" style="min-height: 160px;">
              <?php if (empty($website['accounts'])) : ?>
                <p>Nema naloga.</p>
              <?php else : ?>
                <?php if (count($website['accounts']) > 1) : ?>
                  <div id="carouselAccountsIndicators_<?php echo $website['id']; ?>" class="carousel carousel-dark slide" data-bs-ride="carousel">
                    <div class="carousel-indicators">
                      <?php $first = true; ?>
                      <?php foreach ($website['accounts'] as $key => $account) : ?>
                        <button type="button" data-bs-target="#carouselAccountsIndicators_<?php echo $website['id']; ?>" data-bs-slide-to="<?php echo $key; ?>" <?php if ($first) { echo 'class="active"'; $first = false; } ?> aria-label="Slide <?php echo $key + 1; ?>"></button>
                      <?php endforeach; ?>
                    </div>
                    <div class="carousel-inner">
                      <?php $first = true; ?>
                      <?php foreach ($website['accounts'] as $account) : ?>
                        <div class="carousel-item <?php if ($first) { echo 'active'; $first = false; } ?>">
                          <input class="form-control rounded-0 border border-secondary" type="text" value="<?php echo $account['username']; ?>" readonly>
                          <div class="input-group">
                            <input class="form-control rounded-0 border-top-0 border-secondary" type="password" value="<?php echo $account['password']; ?>" id="password<?php echo $account['id']; ?>" readonly>
                            <button class="btn btn-outline-secondary btn-sm rounded-0 border-top-0" type="button" id="togglePassword<?php echo $account['id']; ?>" onclick="togglePasswordVisibility('<?php echo $account['id']; ?>')">
                              <i class="fas fa-eye"></i>
                            </button>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselAccountsIndicators_<?php echo $website['id']; ?>" data-bs-slide="prev">
                      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                      <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carouselAccountsIndicators_<?php echo $website['id']; ?>" data-bs-slide="next">
                      <span class="carousel-control-next-icon" aria-hidden="true"></span>
                      <span class="visually-hidden">Next</span>
                    </button>
                  </div>
                <?php else : ?>
                  <div class="container p-0">
                    <input class="form-control rounded-0 border border-secondary" type="text" value="<?php echo $website['accounts'][0]['username']; ?>" readonly>
                    <div class="input-group">
                      <input class="form-control rounded-0 border-top-0 border-secondary" type="password" value="<?php echo $website['accounts'][0]['password']; ?>" id="password<?php echo $website['accounts'][0]['id']; ?>" readonly>
                      <button class="btn btn-outline-secondary btn-sm rounded-0 border-top-0" type="button" id="togglePassword<?php echo $website['accounts'][0]['id']; ?>" onclick="togglePasswordVisibility('<?php echo $website['accounts'][0]['id']; ?>')">
                        <i class="fas fa-eye"></i>
                      </button>
                    </div>
                  </div>
                <?php endif; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
      <!-- Dugme za dodavanje novih stranica -->
      <div class="col">
        <div class="card text-center h-100" style="width: 18rem; min-height: 250px;">
          <div class="card-body d-flex justify-content-center align-items-center">
            <a href="add_website.php" class="btn btn-outline-primary btn-lg">
              <i class="fas fa-plus"></i>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <!-- <script src="js/bootstrap.bundle.min.js"></script> -->

  <!-- Font Awesome JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/js/all.min.js" integrity="sha512-u3fPA7V8qQmhBPNT5quvaXVa1mnnLSXUep5PS1qo5NRzHwG19aHmNJnj1Q8hpA/nBWZtZD4r4AX6YOt5ynLN2g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <!-- <script src="js/all.min.js"></script> -->
  
  <script>
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
