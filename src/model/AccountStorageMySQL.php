<?php

require_once("model/AccountStorage.php");
require_once("model/Account.php");
/**
 * cette classe represente une base de donnees de comptes : dans notre cas on utilise une base mysql auquelle on accede à l'aide de l'outil PDO
 */
class AccountStorageMySQL implements AccountStorage
{

  protected $db;

  function __construct($db)  {
    $this->db = $db;
  }

  // cette methode permet de verifier s'il exixste un compte correspond aux login et mot de passe saisies
  public function checkAuth($login, $password){

    $rq = "select * from accounts where login = :login ;";
    $stmt = $this->db->prepare($rq);
    $data = array(":login" => $login);
    $stmt->execute($data);
    $info = $stmt->fetch(PDO::FETCH_ASSOC);

    if($info !== false and password_verify($password, $info['password'])){
       return new Account($info['name'],$info['login'],$info['password'],$info['statut']);
    }

    return null;


  }

  // cette methode permet de verifier s'il existe un compte correspond au login
  public function exists($login){

    $rq = "select * from accounts where login = :login;";
    $stmt = $this->db->prepare($rq);
    $data = array(":login" => $login);
    $stmt->execute($data);
    $info = $stmt->fetch(PDO::FETCH_ASSOC);
    return $info !== false;
  }

  // cette methode permet de créer un compte dans la base de donnees à partir d'un objet compte
  public function create(Account $a){
    $rq = "insert into accounts(name,login,password,statut) values(:name,:login,:password, :statut);";
    $stmt = $this->db->prepare($rq);
    $data = array(":name" => $a->getName(),
                  ":login" => $a->getLogin(),
                  ":password" => password_hash($a->getPassword(), PASSWORD_BCRYPT),
                  ":statut" => $a->getStatut(),
                );
    $stmt->execute($data);

  }

  // cette methode permet de supprimer un compte de la base de donnees dont le login est passé en argument
  public function delete($login){


      $rq = "delete from accounts where login = :login";
      $stmt = $this->db->prepare($rq);
      $data = array(":login" => $login);
      $stmt->execute($data);



  }



}







 ?>
