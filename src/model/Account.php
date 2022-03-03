<?php


/**
 *cette classe represente un compte utilisateur
 */

class Account
{

  protected $name;
  protected $login;
  protected $password;
  protected $statut;


  function __construct($name,$login,$password,$statut)
  {
    $this->name = $name;
    $this->login = $login;
    $this->password = $password;
    $this->statut = $statut;
  }

/* getters and setters */

  public function getName(){
    return $this->name;
  }

  public function getLogin(){
    return $this->login;
  }

  public function getPassword(){
    return $this->password;
  }

  public function getStatut(){
    return $this->statut;
  }

  public function setName($name){
    $this->nom = $nom;
  }

  public function setLogin($login){
    $this->login = $login;
  }

  public function setPassword($password){
    $this->password = $password;
  }

  public function setStatut($statut){
    $this->statut = $statut;
  }


}




 ?>
