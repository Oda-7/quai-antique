<?php

require './sys/class/Recaptcha.php';
// include './sys/function.php';

if (isset($_SESSION['auth'])) {
   $urlLogin = "/";
   echo '<script type="text/javascript">window.location.href="' . $urlLogin . '";</script>';
   // header('Location: /');
   exit();
}
// time() + 365*24*3600 pour une validation de cookie d'un an
$errors = array();

if (!empty($_POST['email']) && empty($_POST['password']) || empty($_POST['email']) && !empty($_POST['password'])) {
   $errors['lost'] = "Un champ n'est pas rempli !";
}



if (!empty($_POST['email']) && !empty($_POST['password'])) {
   require_once './sys/db.php';

   if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
      $errors['bad_mail'] = "l'email n'est pas correct";
   }
   // $email = validateData($_POST['email']);

   $recaptcha = new Recaptcha('6Lc-oBsnAAAAAKFX4yyyh8UOY7GCrzQh3eSqhrj2');
   if ($recaptcha->checkCode($_POST['g-recaptcha-response']) == false) {
      $errors['captcha'] = "La valiadation du Captcha n'est pas correct";
   }

   try {
      $req = $pdo->prepare('SELECT * FROM users WHERE user_email = ? ');
      $req->execute([$_POST['email']]);
      // $req->execute([$email]);
      $user = $req->fetch();

      if ($user->user_confirmed_at == 'NULL') {
         $errors['confirm'] = "L'utilisateur n'a pas confirmer l'email";
      } else {
         if (password_verify($_POST['password'], $user->user_password) && empty($errors)) {
            $reqProfil = $pdo->prepare('SELECT * FROM profil WHERE profil_id = ? ');
            $reqProfil->execute([$user->user_id]);
            $userProfil = $reqProfil->fetch();

            if (isset($_POST['remember'])) {
               $remember_token = rand();
               $reqRemember = $pdo->prepare('UPDATE users SET user_remember_token = ? WHERE user_id = ?');
               $reqRemember->execute([$remember_token, $user->user_id]);
               setcookie('remember', $user->user_id . '//' . $remember_token . sha1($user->user_id . 'ratonlaveurs'), time() + 60 * 60 * 24 * 7, '/');
            }

            if (session_status() == PHP_SESSION_NONE) {
               session_start();
            }
            $_SESSION['auth'] = $user;
            // var_dump($_SESSION['auth']);
            $_SESSION['flash']['success'] = "Vous êtes maintenant connecté " . $userProfil->profil_firstname;
            // $urlLogin = 'panel_user.php';
            header('Location: panel_user.php');
            // echo '<script type="text/javascript">window.location.href="' . $urlLogin . '";</script>';
            exit();
         } else {
            $errors['Email'] = "Email ou mot de passe incorrect !";
         }
      }
   } catch (PDOException $e) {
      $errors['user'] = "L'utilisateur n'existe pas !";
   }
} elseif (empty($_POST['email']) && empty($_POST['password']) && isset($_POST['button_connect'])) {
   $errors['input_empty'] = 'les champs ne sont pas rempli';
}

$namePage = 'Quai Antique - Connexion';
include './templates/header.php';
// var_dump($_SESSION);
// print_r($_COOKIE);
?>

<?php if (!empty($errors)) : ?>
   <div class="alert alert-danger">
      <ul>
         <?php foreach ($errors as $error) : ?>
            <li><?= $error; ?></li>
         <?php endforeach; ?>
      </ul>
   </div>
<?php endif; ?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<div class="mx-4">
   <div class="d-flex flex-column align-items-center mt-5">
      <h1 class="mt-2">Connection:</h1>
      <form method="POST" class="d-flex flex-column">
         <label>Email:</label>
         <input class="form-control" type="text" name="email">
         <label for="">Mot de passe:</label>
         <input class="form-control" type="password" name="password">
         <div class="">
            <label class="py-2">
               <input class="form-check-input" type="checkbox" name="remember" value="">Remember me
            </label>

         </div>

         <div class="g-recaptcha" data-sitekey="6Lc-oBsnAAAAAN_wOiY7DZT-V1acZssIbhlIoVr2"></div>
         <a class="text-decoration-none btn my-2" style="color: #e8eddf; background-color: #242423;" href="./forget.php">Mot de passe oublié</a>
         <div class="d-flex flex-column align-items-center justify-content-center gap-2 mt-2">
            <a href="/">
               <button class="btn" style="color: #e8eddf; background-color: #242423 ;">Annuler</button>
            </a>
            <input class="btn" style="color: #e8eddf; background-color: #242423 ;" name="button_connect" type="submit" value="Connexion">
         </div>
      </form>
   </div>
</div>


<script>
   function onSubmit(token) {
      document.getElementById("demo-form").submit();
   }
</script>

<?php
include './templates/footer.php';
?>