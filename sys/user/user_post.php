<?php
include '../info_db.php';

try {
   $pdo = new PDO("mysql:host=$host_name; dbname=$database;", $user_name, $password);
   $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
} catch (PDOException $e) {
   echo "Connection échoué: " . $e->getMessage();
}

if (isset($_POST['number_reservation_default'])) {
   $reqInsertNumberReservation = $pdo->prepare('UPDATE profil SET profil_default_reservation = ? WHERE profil_id = ?');
   $reqInsertNumberReservation->execute([$_POST['number_reservation_default'], $_POST['id_profil']]);

   $reqSelectNumberReservation = $pdo->prepare('SELECT * FROM profil WHERE profil_id = ?');
   $reqSelectNumberReservation->execute([$_POST['id_profil']]);
   $selectNumberReservation = $reqSelectNumberReservation->fetch();
   echo $selectNumberReservation->profil_default_reservation;
}
