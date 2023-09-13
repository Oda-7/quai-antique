<?php
require_once './Models/Categorie.php';

global $pdo;

class CategorieController
{
    public string $name;
    public int $id;
    private object $pdo;

    public function __construct($name, $id, $pdo)
    {
        $this->name = $name;
        $this->id = $id;
        $this->pdo = $pdo;
    }

    public function createCategorie()
    {
        // var_dump($this->name);
        if (empty($this->name)) {
            $errors['empty_categorie_name'] = "Vous n'avez pas donné de nom à la catégorie";
        } else {
            $categorie = new Categorie(ucfirst($this->name), $this->id);
            $reqVerifyCategorieInsert = $this->pdo->prepare('SELECT * FROM sub_categorie WHERE sub_categorie_name =  ?');
            $reqVerifyCategorieInsert->execute([$categorie->name]);
            $verifyCategorieInsert = $reqVerifyCategorieInsert->fetch();
            if (!$verifyCategorieInsert) {
                $reqInsertCategorie = $this->pdo->prepare('INSERT INTO sub_categorie SET sub_categorie_name = ?, categorie_id = ?');
                $reqInsertCategorie->execute([$categorie->name, $categorie->id]);
                $_SESSION['flash']['success'] = 'La categorie ' . $categorie->name . ' a  été ajoutée';
                header('refresh:3;');
            } else {
                $errors['add_categorie'] = "La catégorie existe déja";
            }
        }
    }
}
