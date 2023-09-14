<?php
require_once './sys/db.php';
require_once './sys/food/db_categorie.php';
require_once './sys/food/db_sub_categorie.php';

?>
<div class="d-flex flex-wrap gap-2">
   <form class="d-flex flex-column flex-wrap gap-2 align-items-start" method="post" id="fist-form">
      <?php
      if (empty($categorieList)) {
         echo 'Aucune Catégorie';
      } else {
         // var_dump($categorieList);
         foreach ($categorieList as $a => $categorie) {
            echo '<div class="dropdown w-100">
               <button  class="btn dropdown-toggle w-100 button-validate" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
               ' . $categorie->categorie_name . '
               </button>
               <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">';
            $reqSelectSubCategorieByCategorie = $pdo->prepare('SELECT * FROM sub_categorie WHERE categorie_id = ?');
            $reqSelectSubCategorieByCategorie->execute([$categorie->categorie_id]);
            $selectSubCategorieByCategorie = $reqSelectSubCategorieByCategorie->fetch();
            if (!$selectSubCategorieByCategorie) {
               echo '<li class="dropdown-item">Aucune sous catégorie</li>';
            } else {
               foreach ($listSubCategorie as $i => $subCategorie) {
                  // var_dump($categorie->categorie_id == $subCategorie->categorie_id && empty($subCategorie->sub_categorie_name));
                  // var_dump($selectSubCategorieByCategorie);

                  if ($categorie->categorie_id == $subCategorie->categorie_id) {

                     echo '
                        <li class="dropdown-item">' . $subCategorie->sub_categorie_name . '
                        <a type="button" href="./panel.php?categorie=' . $subCategorie->sub_categorie_id . '"><img  src="./svg/sticky.svg" alt="Modifier"></a>
                        <input type="checkbox" name="checkbox_categorie_delete[]" value="' . $subCategorie->sub_categorie_id . '">
                        </li>';
                  }
               }
            }
            echo '</ul>
            </div>';
         }
      }
      ?>
      <div class="d-flex flex-column flex-wrap gap-2 align-items-center py-4">
         <input class="btn button-validate" id="add_categorie" name="add_categorie" type="submit" value="Ajouter une categorie">
         <input class="btn button-cancel" name="delete_categorie" type="submit" value="Supprimer">
      </div>
   </form>
</div>


<?php
$displayCategorieAdd = 'd-none';
if (isset($_POST['add_categorie'])) {
   if (isset($_GET)) {
      unset($_GET['categorie']);
      unset($_GET['dishes']);
      unset($_GET['food']);
      unset($_GET['menu']);
      unset($_GET['id']);
   }
   $displayCategorieAdd = 'd-flex';
}
?>
<form method="post" class="<?= $displayCategorieAdd ?> flex-column flex-wrap gap-2 align-items-center" id="form_add_category">
   <input class="form-control" type="text" name="categorie_name" placeholder="Catégorie à ajouter">
   <select class="form-select" name="categorie_select">
      <?php
      foreach ($categorieList as $categorie) {
         echo '<option value="' . $categorie->categorie_id . '">' . $categorie->categorie_name . '</option>';
      }
      ?>
   </select>

   <input class="btn button-validate" type="submit" name="categorie_add_validate" value="Ajouter">
   <input class="btn button-cancel" type="button" id="cancel_add_categorie" value="Annuler">
</form>


<?php
$displayCategorieModify = 'd-none';
if (isset($_GET['categorie'])) {
   $displayCategorieModify = 'd-flex';
}
?>

<form id="form_update_categorie" method="post" class="<?= $displayCategorieModify ?> flex-column flex-wrap gap-2">
   <?php
   // modify Categorie
   if (isset($_GET['categorie'])) {
      $reqModifyCategorieRead = $pdo->prepare('SELECT * FROM sub_categorie WHERE sub_categorie_id = ?');
      $reqModifyCategorieRead->execute([$_GET['categorie']]);
      $modifyCategorieRead = $reqModifyCategorieRead->fetch();

      echo '
      <input class="form-control" type="text" name="categorie_name" value="' . $modifyCategorieRead->sub_categorie_name . '">
      <select class="form-select" name="modify_categorie_id">';
      foreach ($categorieList as $categorie) {
         if ($categorie->categorie_id == $modifyCategorieRead->categorie_id) {
            echo '<option value="' . $modifyCategorieRead->categorie_id . '" selected>' . $categorie->categorie_name . '</option>';
         }
         echo '<option value="' . $categorie->categorie_id . '">' . $categorie->categorie_name . '</option>';
      }
      echo '</select>';
   }
   ?>
   <input class="btn button-validate" name="modify_categorie" type="submit" value="Modifier">
   <input class="btn button-cancel" id="cancel_modify_categorie" value="Annuler">
</form>


<script>
   const formAddCategory = document.getElementById('form_add_category');
   const cancelAddCategorie = document.getElementById('cancel_add_categorie');

   cancelAddCategorie.addEventListener('click', (event) => {
      formAddCategory.classList.replace('d-flex', 'd-none')
      window.location.href = './panel.php';
   })

   const formUpdateCategorie = document.getElementById('form_update_categorie');
   const cancelModifyCategorie = document.getElementById('cancel_modify_categorie');

   cancelModifyCategorie.addEventListener('click', (event) => {
      formUpdateCategorie.classList.replace('d-flex', 'd-none')
      window.location.href = './panel.php';
   })
</script>