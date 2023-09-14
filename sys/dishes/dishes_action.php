<?php
if (session_status() == PHP_SESSION_NONE) {
   session_start();
}
require_once './sys/dishes/db_dishes.php';
require_once './sys/allergic/db_allergic.php';
require_once './sys/db.php';

// if (session_status() == PHP_SESSION_NONE) {
// session_start();
// }
$reqSelectNameFood = $pdo->prepare('SELECT * FROM food WHERE food_id = ?');


// Ajout de plat et d'aliments
if (isset($_POST['validate_add_dishes'])) {
   $reqContainDishes = $pdo->prepare('SELECT * FROM dishes WHERE dishes_name = ?');
   $reqContainDishes->execute([ucfirst($_POST['dishes_name'])]);
   $containDishes = $reqContainDishes->fetch();
   if ($containDishes) {
      $errors['contain_dishes'] = "Le plat existe déja";
   } else {
      if (empty($_POST['dishes_name']) || empty($_POST['dishes_food']) || $_POST['select_categorie'] == 'null') {
         $errors['form_dishes'] = "Un des champs n'est pas remplie";
      } else {
         $formDishes = [
            ucfirst($_POST['dishes_name']),
            ucfirst($_POST['dishes_description']),
            $_POST['select_categorie']
         ];

         $arrayFood = explode(",", trim($_POST['dishes_food']));
         $newListFood = [];
         $arrayIdFood = [];

         // var_dump($formDishes);
         echo '<div><form method="post" class="changeHeightFromFood bg-secondary p-3 text-white ms-5 ms-sm-auto me-2 me-sm-5 me-md-auto d-flex flex-wrap flex-column align-items-center py-2 gap-2">';
         foreach ($arrayFood as $i => $food) {
            $reqFood = $pdo->prepare('SELECT * FROM food WHERE food_name = ? ');
            $food = trim(ucfirst($food));
            $reqFood->execute([$food]);
            $foodContain = $reqFood->fetch();

            if ($food) {
               array_push($newListFood, $food);
            }

            if (!$foodContain) {
               $reqInsertFood = $pdo->prepare('INSERT INTO food SET food_name = ?')->execute([ucfirst($food)]);
               array_push($arrayIdFood, $pdo->lastInsertId());
               echo '<input type="hidden" name="food_id[]" value="' . $arrayIdFood[$i] . '">
               <label>' . $food . '</label>
               <select class="form-select" name="allergic_select[]">
                  <option value="null">Allergènes</option>  ';
               foreach ($allergicListBd as $allergic) {
                  echo '<option value="' . $allergic->allergic_id . '">' . $allergic->allergic_name . '</option>';
               }
               echo '</select>
               <input class="form-control" type="text" name="add_food_origin[]" placeholder="Traçabilités des produits">
               <select class="form-select" name="add_food_breeding[]">
                  <option value="null" selected>Condition de vie</option>
                  <option value="1">Élevage</option>
                  <option value="2">Sauvage</option>
               </select>';
               $buttonAdd = true;
            } else {
               array_push($arrayIdFood, $foodContain->food_id);
            }
         }
         if (isset($buttonAdd) && $buttonAdd) {
            echo '<input class="btn" style="background-color: #242423;color: #e8eddf;" class="form-control" name="add_food_allergic" type="submit" value="Valider">';
         } else {
            // header('location: panel.php', true);
            $urlLogin = "panel.php";
            echo '<script type="text/javascript">window.location.href="' . $urlLogin . '";</script>';
         }
         echo '</form><div>';

         $implodeFood = implode(', ', $newListFood);

         if (!$formDishes[1]) {
            $reqInsertDishes = $pdo->prepare('INSERT INTO dishes SET dishes_name = ?, dishes_description = ?, dishes_food = ?, sub_categorie_id = ?');
            $reqInsertDishes->execute([ucfirst($formDishes[0]), $formDishes[1], ucfirst($implodeFood), $formDishes[2]]);
         } else {
            $reqInsertDishes = $pdo->prepare('INSERT INTO dishes SET dishes_name = ?, dishes_food = ?, sub_categorie_id = ?');
            $reqInsertDishes->execute([ucfirst($formDishes[0]), ucfirst($implodeFood), $formDishes[2]]);
         }
         $idDishes = $pdo->lastInsertId();
         echo 'la';
         foreach ($arrayIdFood as $idFood) {
            $reqInsertHaveFood = $pdo->prepare("INSERT INTO have_food SET food_id = ?, dishes_id = ?");
            $reqInsertHaveFood->execute([$idFood, $idDishes]);
         }
         $_SESSION['flash']['success'] .= "Vous avez ajouté Le plat " . ucfirst($_POST['dishes_name']) . "<br>";
      }
      // header('refresh: 3; url=panel.php');
   }
}


// Ajout d'aliments avec leurs spécificités
if (isset($_POST['add_food_allergic'])) {
   foreach ($_POST['food_id'] as $i => $idAddFood) {
      $reqSelectNameFood->execute([$idAddFood]);
      $selectNameFood = $reqSelectNameFood->fetch();
      $postOrigin = ucfirst($_POST["add_food_origin"][$i]);
      if (!empty($_POST["add_food_origin"][$i]) && $_POST["add_food_breeding"][$i] != 'null') {
         $reqUpdateFood = $pdo->prepare('UPDATE food SET food_origin = ?, food_breeding = ? WHERE food_id = ?');
         $reqUpdateFood->execute([$postOrigin, $_POST["add_food_breeding"][$i], $idAddFood]);
      } elseif (!empty($_POST["add_food_origin"][$i])) {
         $reqUpdateFood = $pdo->prepare('UPDATE food SET food_origin = ? WHERE food_id = ?');
         $reqUpdateFood->execute([$postOrigin, $idAddFood]);
      }

      $_SESSION['flash']['success'] .= "Vous avez ajouté l'aliment " . ucfirst($selectNameFood->food_name) . "<br>";
   }

   $reqErrorFood = $pdo->prepare('SELECT * FROM food_allergic WHERE food_id = ?');
   foreach ($_POST['allergic_select'] as $i => $idAllergic) {
      $reqErrorFood->execute([$_POST['food_id'][$i]]);
      $errorFood = $reqErrorFood->fetch();
      if (!$errorFood) {
         if ($idAllergic != 'null') {
            $reqInsertFoodAllergic = $pdo->prepare('INSERT INTO food_allergic SET food_id = ?, allergic_id = ?');
            $reqInsertFoodAllergic->execute([$_POST['food_id'][$i], $idAllergic]);
            // $_SESSION['flash']['success'] .= "Vous avez ajouté un allergène pour l'aliment " . $errorFood->food_name . '<br>';
         }
      }
   }
}

// Modification de plat et d'aliments
$inputDisplay = false;
if (isset($_POST['modify_dishes'])) {
   $reqVerifyDishes = $pdo->prepare('SELECT * FROM dishes WHERE dishes_id = ?');
   $reqVerifyWhereDishes = $pdo->prepare('SELECT food_id FROM have_food WHERE dishes_id = ?');
   $selectFoodDishesVerify = $pdo->prepare('SELECT food_name  FROM food WHERE food_id = ?');
   $reqCheckFood = $pdo->prepare('SELECT * FROM food WHERE food_name = ?');
   $reqVerifyHaveFood = $pdo->prepare('SELECT * FROM have_food WHERE food_id = ? AND dishes_id = ?;');
   $reqInsertFood = $pdo->prepare('INSERT INTO food SET food_name = ?');
   $reqInsertHaveFood = $pdo->prepare("INSERT INTO have_food SET food_id = ?, dishes_id = ?");
   $reqDeleteHaveFood = $pdo->prepare('DELETE FROM have_food WHERE food_id = ? AND dishes_id = ?');
   $reqUpdateDishes = $pdo->prepare('UPDATE dishes SET dishes_name = ?, dishes_description = ?, dishes_food = ?, sub_categorie_id = ? WHERE dishes_id = ?');

   $reqVerifyDishes->execute([$_GET['dishes']]);
   $verifyDishes = $reqVerifyDishes->fetch();
   $reqVerifyWhereDishes->execute([$_GET['dishes']]);
   $verifyWhereDishes = $reqVerifyWhereDishes->fetchAll();
   $listExplodeVerifyDishesFood = [];
   $listExplodePostDishesFood = [];
   $listForm = explode(',', trim($_POST['modify_dishes_food']));
   foreach ($listForm as $i => $form) {
      array_push($listExplodePostDishesFood, ucfirst(trim($form)));
   }

   $formDisplay = 'd-none';

   foreach ($verifyWhereDishes as $verifyWhereDishesFood) {
      //verifications des aliments du plat
      $selectFoodDishesVerify->execute([$verifyWhereDishesFood->food_id]);
      $foodDishesVerify = $selectFoodDishesVerify->fetch();
      array_push($listExplodeVerifyDishesFood, $foodDishesVerify->food_name);
   }
   // $_SESSION['flash']['success'] = "holla";

   if (
      $verifyDishes->dishes_name != $_POST['modify_dishes_name']
      || $verifyDishes->dishes_description != $_POST['modify_dishes_description']
      || $listExplodeVerifyDishesFood != $listExplodePostDishesFood
      || $verifyDishes->sub_categorie_id != $_POST['modify_sub_categorie_dishes']
   ) {
      if (count($listExplodePostDishesFood) > count($listExplodeVerifyDishesFood)) {
         // si il y a plus d'aliments dans le formulaire que dans le plats
         foreach ($listExplodePostDishesFood as $i => $postDishesFood) {
            $postDishesFood = ucfirst(trim($postDishesFood));
            $reqCheckFood->execute([$postDishesFood]);
            $checkFood = $reqCheckFood->fetch();
            if (!$checkFood) {
               $inputDisplay = true;
            }
         }

         if ($inputDisplay) {
            echo '<form method="post" class="changeHeightFromFood bg-secondary p-3 text-white ms-5 ms-sm-auto me-2 me-sm-5 me-md-auto d-flex flex-wrap flex-column align-items-center py-2 gap-2" id="form_modify">';
         }

         foreach ($listExplodePostDishesFood as $i => $postDishesFood) {
            $postDishesFood = ucfirst(trim($postDishesFood));
            $reqCheckFood->execute([$postDishesFood]);
            $checkFood = $reqCheckFood->fetch();
            $reqVerifyHaveFood->execute([$checkFood->food_id, $_GET['dishes']]);
            $verifyFoodDishes = $reqVerifyHaveFood->fetch();

            // insertion d'aliment s'il n'existe pas
            if (!$checkFood) {
               $reqInsertFood->execute([ucfirst($postDishesFood)]);
               $foodId = $pdo->lastInsertId();
               $reqInsertHaveFood->execute([$foodId, $_GET['dishes']]);

               echo '<input type="hidden" name="food_id_add_modify[]" value="' . $foodId . '">
                   <label>' . ucfirst(trim($postDishesFood)) . '</label>
                   <select class="form-select" name="allergic_select_modify[]">
                       <option value="null">Allergènes</option>  ';
               foreach ($allergicListBd as $allergic) {
                  echo '<option value="' . $allergic->allergic_id . '">' . $allergic->allergic_name . '</option>';
               }
               echo '</select>
                   <input class="form-control" type="text" name="modify_add_food_origin[]" placeholder="Traçabilités des produits">
                   <select class="form-select" name="modify_add_food_breeding[]">
                       <option value="null" selected>Condition de vie</option>
                       <option value="1">Élevage</option>
                       <option value="2">Sauvage</option>
                   </select>';
            } else {
               if (!$verifyFoodDishes) {
                  $reqInsertHaveFood->execute([$checkFood->food_id, $_GET['dishes']]);
               }
            }
         }

         foreach ($listExplodeVerifyDishesFood as $i => $verifyDishesFood) {
            $reqCheckFood->execute([$verifyDishesFood]);
            $checkFood = $reqCheckFood->fetch();
            if (in_array($verifyDishesFood, $listExplodePostDishesFood) === false) {
               $reqDeleteHaveFood->execute([$checkFood->food_id, $_GET['dishes']]);
            }
         }

         if ($inputDisplay) {
            echo '<input class="btn button-validate" type="submit" name="validate_add_food_allergic" value="Valider">
           </form>';
         } else {
            $_SESSION['flash']['success'] = "Vous avez modifié le plat " . ucfirst($_POST['modify_dishes_name']);
            header('refresh: 3; url=panel.php');
         }
      } elseif (count($listExplodePostDishesFood) < count($listExplodeVerifyDishesFood)) {
         foreach ($listExplodeVerifyDishesFood as $i => $verifyDishesFood) {
            $postDishesFood = ucfirst(trim($listExplodePostDishesFood[$i]));
            if (!empty($postDishesFood)) {
               $reqCheckFood->execute([$postDishesFood]);
               $checkFood = $reqCheckFood->fetch();
               if (!$checkFood) {
                  $inputDisplay = true;
               }
            }
         }

         if ($inputDisplay) {
            echo '<form method="post" class="changeHeightFromFood bg-secondary p-3 text-white ms-5 ms-sm-auto me-2 me-sm-5 me-md-auto  d-flex flex-wrap flex-column align-items-center py-2 gap-2" id="form_modify">';
         }

         foreach ($listExplodeVerifyDishesFood as $i => $explodeVerifyFood) {
            $postDishesFood = ucfirst(trim($listExplodePostDishesFood[$i]));
            $reqCheckFood->execute([$postDishesFood]);
            $checkFood = $reqCheckFood->fetch();
            $reqVerifyHaveFood->execute([$checkFood->food_id, $_GET['dishes']]);
            $verifyFoodDishes = $reqVerifyHaveFood->fetch();

            if (!empty($postDishesFood) && array_search(trim(ucfirst($postDishesFood)), $listExplodeVerifyDishesFood) == false) {
               // si l'aliment du formulaire n'existe pas dans le plat
               if (!$checkFood) {
                  $reqInsertFood->execute([ucfirst($postDishesFood)]);
                  $foodId = $pdo->lastInsertId();
                  $reqInsertHaveFood->execute([$foodId, $_GET['dishes']]);

                  echo '<input type="hidden" name="food_id_add_modify[]" value="' . $foodId . '">
                       <label>' . ucfirst(trim($postDishesFood)) . '</label>
                       <select class="form-select" name="allergic_select_modify[]">
                           <option value="null">Allergènes</option>  ';
                  foreach ($allergicListBd as $allergic) {
                     echo '<option value="' . $allergic->allergic_id . '">' . $allergic->allergic_name . '</option>';
                  }
                  echo '</select>
                       <input class="form-control" type="text" name="modify_add_food_origin[]" placeholder="Traçabilités des produits">
                       <select class="form-select" name="modify_add_food_breeding[]">
                           <option value="null" selected>Condition de vie</option>
                           <option value="1">Élevage</option>
                           <option value="2">Sauvage</option>
                       </select>';
               } else {
                  if (!$verifyFoodDishes) {
                     $reqInsertHaveFood->execute([$checkFood->food_id, $_GET['dishes']]);
                  }
               }
            }
         }

         foreach ($listExplodeVerifyDishesFood as $i => $verifyDishesFood) {
            $reqCheckFood->execute([$verifyDishesFood]);
            $checkFood = $reqCheckFood->fetch();
            if (in_array($verifyDishesFood, $listExplodePostDishesFood) === false) {
               $reqDeleteHaveFood->execute([$checkFood->food_id, $_GET['dishes']]);
            }
         }

         if ($inputDisplay) {
            echo '<input class="btn button-validate" type="submit" name="validate_add_food_allergic" value="Valider">
           </form>';
         } else {
            $_SESSION['flash']['success'] = "Vous avez modifié le plat " . ucfirst($_POST['modify_dishes_name']);
            header('refresh: 3; url=panel.php');
         }
      } else {

         foreach ($listExplodePostDishesFood as $i => $postDishesFood) {
            $postDishesFood = ucfirst(trim($postDishesFood));
            $reqCheckFood->execute([$postDishesFood]);
            $checkFood = $reqCheckFood->fetch();
            if (!$checkFood) {
               $inputDisplay = true;
            }
         }

         if ($inputDisplay) {
            echo '<form method="post" class="changeHeightFromFood bg-secondary p-3 text-white ms-5 ms-sm-auto me-2 me-sm-5 me-md-auto d-flex flex-wrap flex-column align-items-center py-2 gap-2" id="form_modify">';
         }

         foreach ($listExplodePostDishesFood as $i => $postDishesFood) {
            $postDishesFood = ucfirst(trim($postDishesFood));
            $reqCheckFood->execute([$postDishesFood]);
            $checkFood = $reqCheckFood->fetch();
            $reqVerifyHaveFood->execute([$checkFood->food_id, $_GET['dishes']]);
            $verifyFoodDishes = $reqVerifyHaveFood->fetch();
            if (!$checkFood) {
               $reqInsertFood->execute([ucfirst($postDishesFood)]);
               $foodId = $pdo->lastInsertId();
               $reqInsertHaveFood->execute([$foodId, $_GET['dishes']]);

               echo '<input type="hidden" name="food_id_add_modify[]" value="' . $foodId . '">
                   <label>' . ucfirst(trim($postDishesFood)) . '</label>
                   <select class="form-select" name="allergic_select_modify[]">
                       <option value="null">Allergènes</option>  ';
               foreach ($allergicListBd as $allergic) {
                  echo '<option value="' . $allergic->allergic_id . '">' . $allergic->allergic_name . '</option>';
               }
               echo '</select>
                   <input class="form-control" type="text" name="modify_add_food_origin[]" placeholder="Traçabilités des produits">
                   <select class="form-select" name="modify_add_food_breeding[]">
                       <option value="null" style="word-wrap: break-word; white-space: normal;" selected>Condition de vie</option>
                       <option value="1">Élevage</option>
                       <option value="2">Sauvage</option>
                   </select>';
            } else {
               if (!$verifyFoodDishes) {
                  $reqInsertHaveFood->execute([$checkFood->food_id, $_GET['dishes']]);
               }
            }
         }
         if ($inputDisplay) {
            echo '<input class="btn button-validate" type="submit" name="validate_add_food_allergic" value="Valider">
           </form>';
         } else {
            $_SESSION['flash']['success'] = "Vous avez modifié le plat " . ucfirst($_POST['modify_dishes_name']);
            header('refresh: 3; url=panel.php');
         }

         foreach ($listExplodeVerifyDishesFood as $i => $verifyDishesFood) {
            $reqCheckFood->execute([$verifyDishesFood]);
            $checkFood = $reqCheckFood->fetch();
            if (in_array($verifyDishesFood, $listExplodePostDishesFood) === false) {
               $reqDeleteHaveFood->execute([$checkFood->food_id, $_GET['dishes']]);
            }
         }
      }

      $reqUpdateDishes->execute([ucfirst($_POST['modify_dishes_name']), $_POST['modify_dishes_description'], $_POST['modify_dishes_food'], $_POST['modify_sub_categorie_dishes'], $_GET['dishes']]);
   } else {
      $_SESSION['flash']['danger'] = "Vous n'avez pas modifier le plat";
   }
}
// testé si on ajoute pas d'allergènes a l'aliment $errors

if (isset($_POST['validate_add_food_allergic'])) {
   foreach ($_POST['food_id_add_modify'] as $i => $idModifyFood) {
      $reqSelectNameFood->execute([$idModifyFood]);
      $selectNameFood = $reqSelectNameFood->fetch();
      if (!empty($_POST['modify_add_food_origin'][$i]) && $_POST['modify_add_food_breeding'][$i] != 'null') {
         $reqUpdateFoodDishes = $pdo->prepare('UPDATE food SET food_origin = ?, food_breeding = ? WHERE food_id = ?');
         $reqUpdateFoodDishes->execute([ucfirst($_POST["modify_add_food_origin"][$i]), $_POST["modify_add_food_breeding"][$i], $idModifyFood]);
      } elseif (!empty($_POST['modify_add_food_origin'][$i])) {
         $reqUpdateFoodDishes = $pdo->prepare('UPDATE food SET food_origin = ? WHERE food_id = ?');
         $reqUpdateFoodDishes->execute([ucfirst($_POST["modify_add_food_origin"][$i]), $idModifyFood]);
      }

      if ($_POST['allergic_select_modify'][$i] != 'null') {
         $reqInsertHaveFoodModify = $pdo->prepare('INSERT INTO food_allergic SET food_id = ?, allergic_id = ?');
         $reqInsertHaveFoodModify->execute([$idModifyFood, $_POST["allergic_select_modify"][$i]]);
      }
      $_SESSION['flash']['success'] = "L'aliments \"" . ucfirst($selectNameFood->food_name) . "\" a été ajouté";
   }
   header('refresh: 3; url=panel.php');
}

if (isset($_POST['delete_dishes'])) {
   if (!empty($_POST['checkbox_delete_dishes'])) {
      foreach ($_POST['checkbox_delete_dishes'] as $deleteDishesId) {
         $reqVerifyHaveMenu = $pdo->prepare('SELECT * FROM have_menu WHERE dishes_id = ?');
         $reqVerifyHaveMenu->execute([$deleteDishesId]);
         $verifyHaveMenu = $reqVerifyHaveMenu->fetch();

         $reqSelectNameMenu = $pdo->prepare('SELECT * FROM menu WHERE menu_id = ?');
         $reqSelectNameMenu->execute([$verifyHaveMenu->menu_id]);
         $selectNameMenu = $reqSelectNameMenu->fetch();

         $reqNameDeleteDishes = $pdo->prepare('SELECT * FROM dishes WHERE dishes_id = ?');
         $reqNameDeleteDishes->execute([$deleteDishesId]);
         $nameDeleteDishes = $reqNameDeleteDishes->fetch();

         if (!$verifyHaveMenu) {
            $reqDeleteHaveFood = $pdo->prepare("DELETE FROM have_food WHERE dishes_id = ?");
            $reqDeleteHaveFood->execute([$deleteDishesId]);

            $reqDeleteDishes = $pdo->prepare('DELETE FROM dishes WHERE dishes_id = ?')->execute([$deleteDishesId]);
            // $errors['delete_dishes'] .= 'Le plat "' . ucfirst($nameDeleteDishes->dishes_name) . '" est supprimé<br>';

            $_SESSION['flash']['danger'] .= "Le plat " . ucfirst($nameDeleteDishes->dishes_name) . ' est supprimé<br>';
            header('refresh: 3; url=panel.php');
         } else {
            $errors['no_delete_have_menu'] = 'Le plat "' . ucfirst($nameDeleteDishes->dishes_name) . '" appartient au menu "' . ucfirst($selectNameMenu->menu_title) . '"';
         }
      }
   } else {
      $errors['dishes_delete'] = "Vous n'avez pas selectionné de plat a supprimé";
   }
}
