<form method="post" class="d-flex flex-wrap">
   <div class="d-flex flex-wrap flex-column align-items-center gap-2 p">
      <?php
      if ($dishesList) {
         foreach ($categorieList as $categorie => $value) {
            foreach ($listSubCategorie as $subCategorie) {
               if ($value->categorie_id == $subCategorie->categorie_id) {
                  echo '<div class="dropdown w-100 ">
                     <button class="btn dropdown-toggle w-100 button-validate" type="button" id="dropdownMenuButton2" data-bs-toggle="dropdown" aria-expanded="true">
                     ' . $subCategorie->sub_categorie_name . '</button>
                     <ul class="dropdown-menu border border-3 border-dark" aria-labelledby="dropdownMenuButton2" class="d-flex flex-wrap justify-content-start">';
                  $reqSelectDishesWhereSubCategorie = $pdo->prepare('SELECT * FROM dishes WHERE sub_categorie_id = ?');
                  $reqSelectDishesWhereSubCategorie->execute([$subCategorie->sub_categorie_id]);
                  $selectDishesWhereSubCategorie = $reqSelectDishesWhereSubCategorie->fetch();
                  if (!$selectDishesWhereSubCategorie) {
                     echo '<li class="dropdown-item ">Aucun plat</li>';
                  }
                  foreach ($dishesList as $dishes) {
                     if ($subCategorie->sub_categorie_id == $dishes->sub_categorie_id) {
                        echo '<li class="dropdown-item ">
                              <div><b>' . $dishes->dishes_name . '</b></div>
                              (' . $dishes->dishes_food . ') 
                              <p>' . $dishes->dishes_description . ' </p>
                              <div><a type="button" href="./panel.php?dishes=' . $dishes->dishes_id . '"><img src="./svg/sticky.svg" alt="Modifier"></a>
                              <input type="checkbox" name="checkbox_delete_dishes[]" value="' . $dishes->dishes_id . '">
                              </div>
                              </li>';
                     }
                  }
                  echo '</ul>
                  </div>';
               }
            }
         }
      } else {
         echo '<p class="d-flex align-self-center flex-wrap">Aucun plat</p>';
      }
      ?>
      <div class="d-flex flex-wrap flex-column align-items-center py-4 gap-2">
         <input class="btn button-validate" type="submit" id="add_dishes" name="add_dishes" value="Ajouter un plat">
         <input class="btn button-cancel" type="submit" name="delete_dishes" value="Supprimer">
      </div>
   </div>
</form>


<?php
$displayAddDishes = 'd-none';
if (isset($_POST['add_dishes'])) {
   $displayAddDishes = 'd-flex';
   if (isset($_GET)) {
      unset($_GET['categorie']);
      unset($_GET['dishes']);
      unset($_GET['food']);
      unset($_GET['menu']);
      unset($_GET['id']);
   }
}

?>
<form method="post" id="form_add_dishes" class="<?= $displayAddDishes ?> flex-column flex-wrap align-items-center justify-content-center gap-2">
   <input class="form-control" type="text" name="dishes_name" placeholder="Nom du plat">
   <textarea class="form-control" type="text" rows="4" name="dishes_description" placeholder="Description du plat"></textarea>
   <textarea class="form-control" type="text" rows="4" name="dishes_food" placeholder="Ingrédients (séparé les aliments par des virgules)"></textarea>
   <!-- Voir pour demander a l'utilisateur s'il souhaite choisir des aliments dans la liste d'aliments -->
   <select class="form-select" name="select_categorie">
      <option value="null"> Aucune catégorie </option>
      <?php
      foreach ($listSubCategorie as $categorie) {
         echo '<option value="' . $categorie->sub_categorie_id . '">' . $categorie->sub_categorie_name . "</option>";
      }
      ?>
   </select>
   <input class="btn button-validate" type="submit" name="validate_add_dishes" value="Ajouter">
   <input class="btn button-cancel" type="button" id="close_post_dishes" value="Annuler">
</form>


<?php
$displayUpdateDishes = 'd-none';
if (isset($_GET['dishes'])) {
   $displayUpdateDishes = 'd-flex';
}
?>

<form method="post" class="<?= $displayUpdateDishes ?> flex-wrap flex-column align-items-center gap-2" id="form_update_dishes">
   <?php if (isset($_GET['dishes'])) {
      $reqModifySelectDishes = $pdo->prepare('SELECT * FROM dishes WHERE dishes_id = ?');
      $reqModifySelectDishes->execute([$_GET["dishes"]]);
      $modifySelectDishes = $reqModifySelectDishes->fetch();

      echo '';
      echo '<input class="form-control" type="text" name="modify_dishes_name" placeholder="Nom du plat" value="' . $modifySelectDishes->dishes_name . '">
      <textarea class="form-control" name="modify_dishes_description" rows="4" placeholder="Description du plat">' . $modifySelectDishes->dishes_description . '</textarea>
      <textarea class="form-control" name="modify_dishes_food" rows="4" placeholder="Ingrédients (séparé les aliments par des virgules)">' . $modifySelectDishes->dishes_food . '</textarea>
      <select class="form-select" name="modify_sub_categorie_dishes">';

      foreach ($listSubCategorie as $categorie) {
         if ($categorie->sub_categorie_id == $modifySelectDishes->sub_categorie_id) {
            echo '<option value="' . $categorie->sub_categorie_id . '" selected>' . $categorie->sub_categorie_name . '</option>';
         } else {
            echo '<option value="' . $categorie->sub_categorie_id . '">' . $categorie->sub_categorie_name . "</option>";
         }
      }

      echo '</select>
   <script>
      
   </script>';
   }
   ?>
   <input class="btn button-validate" type="submit" value="Modifier" name="modify_dishes">
   <input class="btn button-cancel" type="button" value="Annuler" id="cancel_modify_dishes">
</form>

<script defer>
   const formUpdateDishes = document.getElementById('form_update_dishes')
   const addDishes = document.getElementById('add_dishes')
   const url = new URL(window.location).searchParams
   const cancelModifydishes = document.getElementById("cancel_modify_dishes")
   const modifyDishes = document.getElementById("modify_dishes");

   cancelModifydishes.addEventListener("click", (event) => {
      formUpdateDishes.classList.replace("d-flex", "d-none")
      window.location.href = "./panel.php"
   })

   // button dishes
   const closeAddDishes = document.getElementById('close_post_dishes')
   const formAddDishes = document.getElementById('form_add_dishes')
   console.log(closeAddDishes, formAddDishes)

   closeAddDishes.addEventListener('click', (event) => {
      formAddDishes.classList.replace('d-flex', 'd-none')
      window.location.href = './panel.php'
   })
</script>