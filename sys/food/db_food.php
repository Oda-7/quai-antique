<?php
// Fetch All food

$reqFoodList = $pdo->prepare('SELECT * FROM food ORDER BY food.food_name');

// $reqFoodList = $pdo->prepare('SELECT * FROM food ORDER BY food_name');
$reqFoodList->execute();
$foodList = $reqFoodList->fetchAll();
