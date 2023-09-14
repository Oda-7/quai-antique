<?php
require_once './sys/db.php';
require_once './Controllers/CategorieController.php';
$reqVerifyCategorie = $pdo->prepare('SELECT COUNT(categorie_name) AS numberCategorie FROM categorie');
$reqVerifyCategorie->execute();
$verifyCategorie = $reqVerifyCategorie->fetch();

$insertCategorie = [
   "Amuses Bouches",
   "Entrées",
   "Plats",
   "Fromages",
   "Desserts",
   "Vins",
];

if ($verifyCategorie->numberCategorie < 1) {
   foreach ($insertCategorie as $categorie) {
      $reqInsertCategories = $pdo->prepare("INSERT INTO categorie SET categorie_name = ?");
      $reqInsertCategories->execute([$categorie]);
   }
}

// Sub Categorie

if (isset($_POST['categorie_add_validate'])) {
   $categorieAdd = new CategorieController($_POST['categorie_name'], $_POST['categorie_select'], $pdo);
   $categorieAdd->createCategorie();
}

if (isset($_POST['modify_categorie'])) {
   $categoreModify = new CategorieController($_POST['categorie_name'], $_GET['categorie'], $pdo);
   $categoreModify->modifyCategorie($_POST['modify_categorie_id']);
   // $reqSelectCategorie = $pdo->prepare('SELECT * FROM sub_categorie WHERE sub_categorie_id = ?')->execute([$_GET['categorie']]);
   // $reqModifyCategorie = $pdo->prepare('UPDATE sub_categorie SET sub_categorie_name = ?, categorie_id = ? WHERE sub_categorie_id = ?');

   // if (!empty($_POST['categorie_name']) || $_POST['modify_categorie_id'] != $_GET['categorie']) {
   //    $reqModifyCategorie->execute([ucfirst($_POST['categorie_name']), $_POST['modify_categorie_id'], $_GET['categorie']]);
   //    $_SESSION['flash']['success'] = 'La catégorie ' . ucfirst($_POST['categorie_name']) . ' a été modifié';
   //    header('refresh:3;url=panel.php');
   // } else {
   //    $errors['modify_categorie'] = "Vous n'avez pas modifié la catégorie";
   //    header('refresh:3;url=panel.php');
   // }

   // $urlLogin = "panel.php";
   // echo '<script type="text/javascript"> window.location.href="' . $urlLogin . '";</script>';
   // unset($_GET['categorie']);

}


if (!empty($_POST['checkbox_categorie_delete'])) {
   $listNameCategorie = array();
   foreach ($_POST['checkbox_categorie_delete'] as $categorieDelete) {
      $reqNameCategorie = $pdo->prepare('SELECT * FROM sub_categorie WHERE sub_categorie_id = ?');
      $reqNameCategorie->execute([$categorieDelete]);
      $nameCategorie = $reqNameCategorie->fetch();
      array_push($listNameCategorie, $nameCategorie->sub_categorie_name);
   }
}

if (isset($_POST['delete_categorie'])) {
   if (empty($_POST['checkbox_categorie_delete'])) {
      $errors['checkbox_categorie'] = "Vous n'avez pas choisit de catégorie(s) a supprimer";
   } else {
      $i = 0;
      foreach ($_POST['checkbox_categorie_delete'] as $categorieDelete) {
         $reqVerifyDishesCategorie = $pdo->prepare('SELECT * FROM dishes WHERE sub_categorie_id = ?');
         $reqVerifyDishesCategorie->execute([$categorieDelete]);
         $verifyDishesCategorie = $reqVerifyDishesCategorie->fetch();
         if (!$verifyDishesCategorie) {
            $reqDeleteCategorie = $pdo->prepare('DELETE FROM sub_categorie WHERE sub_categorie_id = ?');
            $reqDeleteCategorie->execute([$categorieDelete]);
            $_SESSION['flash']['danger'] = 'Catégorie supprimé : ' . $listNameCategorie[$i] . '<br>';
         } else {
            $errors['no_delete_categorie'] = "Vous ne pouvez pas supprimer la catégorie " . $listNameCategorie[$i] . " car elle contient des plats";
         }

         $i++;
      }
   }
}
