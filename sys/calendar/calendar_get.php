<?php
include '../info_db.php';

try {
   $pdo = new PDO("mysql:host=$host_name; dbname=$database;", $user_name, $password);
   $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
} catch (PDOException $e) {
   echo "Connection échoué: " . $e->getMessage();
}

if (isset($_POST['date']) && isset($_POST['part'])) {
   $reqSelectCalendarNumberReservation = $pdo->prepare('SELECT number_reservation FROM calendar WHERE date_calendar = ? AND part_day = ?');
   $reqSelectCalendarNumberReservation->execute([$_POST['date'], $_POST['part']]);
   $selectNumberReservation = $reqSelectCalendarNumberReservation->fetch();
   echo $selectNumberReservation->number_reservation;
}
