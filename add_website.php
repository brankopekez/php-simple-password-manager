<?php
// Uključi fajl za konekciju sa bazom podataka i funkcije za CRUD operacije
include 'db_connection.php';

// Provjeri da li je forma poslata za dodavanje nove stranice
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Dobavi podatke iz forme
  $name = $_POST['name'];
  $address = $_POST['address'];

  // Ubaci novu stranicu u bazu podataka
  createWebsite($name, $address);

  // Preusmjeri na stranicu indeksa da bi se prikazala ažurirana lista stranica
  header("Location: index.php");
  exit;
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

  <title>Nova Stranica</title>
</head>

<body>
  <div class="container mt-5 w-50">
    <h2>Nova stranica</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
      <div class="row mb-2 mt-4">
        <label for="name" class="col-sm-2 col-form-label">Naziv stranice</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" id="name" name="name" required>
        </div>
      </div>
      <div class="row mb-4">
        <label for="address" class="col-sm-2 col-form-label">Adresa stranice</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" id="address" name="address" required>
        </div>
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

</body>

</html>