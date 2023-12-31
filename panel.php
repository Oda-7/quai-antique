<?php
$namePage = "Quai Antique - Gestion";

if (session_status() == PHP_SESSION_NONE) {
   session_start();
}
// header('refresh:3');


// categorie
require_once './sys/food/categorie_action.php';
//food
include './sys/food/food_action.php';
// allergic
include './sys/allergic/allergic_action.php';
// dishes
require_once './sys/dishes/dishes_action.php';
// var_dump($_SESSION['flash']);
require_once './templates/header.php';
// planning
include './sys/planning/planning_action.php';
// role
include './sys/role/role_action.php';

//menu
include './sys/menu/menu_action.php';

?>

<div class="container-fluid ">
   <nav class="col-1 col-md-2  d-sm-block sidebar">
      <div id="side_bar" class="sidebar-sticky col-1 col-md-2">
         <section class="nav flex-column flex-nowrap gap-3">
            <li class="nav-item d-flex justify-content-center align-items-center">
               <a class="d-flex flex-row justify-content-center text-decoration-none flex-wrap gap-2 mx-2" style="color: #e8eddf;" type="button" id="home_button">
                  <img src="./svg/home_white.svg">
                  <p class="d-none d-md-block my-md-auto">Accueil</p>
               </a>
            </li>
            <li class="nav-item">
               <a class="d-flex flex-row justify-content-center flex-wrap gap-2 text-decoration-none mx-2" style="color: #e8eddf;" type="button" id="menu_button">
                  <img src="./svg/restaurant_menu_white.svg">
                  <p class="d-none d-md-block my-md-auto">Menus et cartes</p>
               </a>
            </li>
            <li class="nav-item">
               <a class="d-flex flex-row justify-content-center flex-wrap gap-2 text-decoration-none mx-2" style="color: #e8eddf;" type="button" id="image_manager">
                  <img src="./svg/collections_white.svg">
                  <p class="d-none d-md-block my-md-auto">Gestionnaire</p>
                  <p class="d-none d-md-block my-md-auto"> d'images</p>
               </a>
            </li>
         </section>
      </div>
   </nav>

   <main id="main" class="col-11 sm-col-10 ms-auto ms-sm-auto mt-5 pt-2 pb-3 px-3 d-flex flex-wrap flex-column align-items-center">
      <?php
      if (isset($_SESSION['flash'])) :
         foreach ($_SESSION['flash'] as $type => $message) : ?>
            <div class="alert alert-<?= $type; ?> text-center" id="flash_div">
               <?= $message; ?>
            </div>
      <?php endforeach;
         unset($_SESSION['flash']);

      endif; ?>

      <?php if (!empty($errors)) : ?>
         <div class="alert alert-danger " id="errors_div">
            <ul>
               <?php foreach ($errors as $error) : ?>
                  <li style="list-style:  none;" class="text-center"><?= $error; ?></li>
               <?php endforeach; ?>
            </ul>
         </div>
      <?php endif; ?>

      <section id="food" class="d-none flex-column flex-wrap gap-2 justify-content-center ms-md-5 ps-md-5">

         <h4 class="ms-lg-2"><b>Pour une bonne utilisation du gestionnaire de menus / plats</b></h4>
         <ol>
            <li>Commencé par ajouter vos catégories de plats</li>
            <li>Pour les menu simple, ajouté vos plats en premier</li>
            <li>Cliquer sur les icones <img src="./svg/sticky.svg" alt="Modifier"> pour modifier</li>
            <li>Cliquer sur les <input type="checkbox" disabled> pour supprimer </li>
         </ol>

         <div class="d-flex flex-wrap flex-column flex-md-row gap-3 justify-content-center">
            <div class=" d-flex flex-column align-items-center ">
               <h3 class="py-3">Catégories</h3>
               <?php include './sys/food/categorie_food.php'; ?>
            </div>

            <div class="  d-flex flex-column align-items-center ">
               <h3 class="py-3">Plats</h3>
               <?php include './sys/dishes/dishes.php';
               ?>
            </div>

            <div class="col-md-6  d-flex flex-column align-items-center flex-wrap">
               <h3 class="py-3">Aliments</h3>
               <?php include './sys/food/food.php'
               ?>
            </div>

            <div class=" d-flex flex-column  align-items-center flex-wrap">
               <h3 class="py-3">Menu</h3>
               <div class="d-flex gap-2 flex-wrap justify-content-center ">
                  <?php include './sys/menu/menu.php'; ?>
               </div>
            </div>

            <div class=" d-flex flex-column  align-items-center flex-wrap">
               <h2>Allèrgènes</h2>
               <div class="d-flex gap-2 flex-wrap justify-content-center ms-lg-3">
                  <?php include './sys/allergic/allergic.php'; ?>
               </div>
            </div>
         </div>

      </section>


      <section id="home" class="d-flex flex-wrap gap-2 justify-content-center">
         <!-- <h3>Calendrier</h3>
         <?php // include './sys/planning/calendar.php' 
         ?>

         <h3>Role</h3>
         <?php // include './sys/role/role.php'; 
         ?>

         <h3>Membre</h3> -->
         <div class="d-flex flex-column">
            <h2 class="my-2">Horaire d'ouverture</h2>
            <?php include './sys/planning/planning.php'; ?>
         </div>
         <!-- <h3 class="text-center">Réservation de la semaine</h3> -->
      </section>


      <section id="image" class="d-none ms-auto flex-wrap justify-content-center ">
         <div class="d-flex flex-nowrap ms-auto flex-column  justify-content-center align-items-center ">
            <h3 class="text-center pb-4">Gestionnaire d'images</h3>
            <?php include './sys/images_manager/image_manager.php'; ?>
         </div>
      </section>
   </main>
</div>
<script src="./sys/panel.js">
   //script pannel admin
</script>

<script>
   const spanBurger = document.getElementById('button_span')
   const sideBar = document.getElementById('side_bar')
   const main = document.getElementById('main')
   let count = 0

   spanBurger.addEventListener('click', (event) => {
      if (count == 1) {
         sideBar.style.top = "56px"
         sideBar.style.height = "calc(100vh - 56 px)"
         count--
      } else {
         sideBar.style.top = "294px"
         sideBar.style.height = "calc(100vh - 294 px)"
         count++
      }
   })
</script>



<?php include './templates/footer.php'; ?>