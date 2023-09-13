<?php
if (session_status() == PHP_SESSION_NONE) {
   session_start();
}

$user_id = $_GET['id'];
$token = $_GET['token'];
$reset_token = $_GET['reset_token'];
var_dump($token, $reset_token);

require './sys/db.php';

$req = $pdo->prepare('SELECT * FROM users WHERE user_id = ?');
$req->execute([$user_id]);
$user = $req->fetch();

if ($token != NULL) {
   if ($user && $user->user_confirmation_token == $token) {
      $pdo->prepare('UPDATE users SET user_confirmation_token = NULL, user_confirmed_at = NOW() WHERE user_id = ?')->execute([$user_id]);
      $_SESSION['flash']['success'] = "Votre compte est bien validé";
      header('Location: login.php');
      exit();
   } else {
      $_SESSION['flash']['danger'] = "Le jeton n'est pas valide";
      header('Location: /');
      exit();
   }
}

if ($reset_token != NULL) {

   if ($user && $user->user_reset_token == $reset_token) {
      // echo 'la';
      $pdo->prepare('UPDATE users SET user_reset_token = NULL, user_reset_at = NULL WHERE user_id = ?')->execute([$user_id]);
      $_SESSION['flash']['success'] = 'Votre mot de passe a bien était réinitialisé';
      header('Location: /login.php');
      exit();
   } elseif ($user && $user->user_reset_token == null) {
      $_SESSION['flash']['danger'] = "Le jeton n'est pas valide, recommencé l'opération si nécessaire";
      header('Location: /login.php');
      exit();
   }
}
