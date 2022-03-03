<?php

require_once("model/Account.php");
/**
 * cette classe represente un gestionnaire de l'authentification
 * elle contient des methodes permettant de gérer la connexion des utilisateur en utilisant la variable de session 'user'
 * rq : l'dée est de créer une variable de session 'user' quand l'utilisateur est connecté et elle stocke son compte
 * quand l'utilisateur se déconnecte on supprime cette variable
 */

class AuthenticationManager
{

  // cette methode permet de connecter un utilisateur
  public function connectUser(Account $account){

          $_SESSION['user'] = $account;

  }

  // cette methode permet de verifier si un utilisateur est connecté
  public function isUserConnected(){
    return key_exists('user',$_SESSION);
  }

  // cette methode permet de verifier si l'utilisateur connecté est un admin
  public function isAdminConnected(){
    return $this->isUserConnected() and $_SESSION['user'].getStatut() === "admin";
  }

  // cette methode permet de récuperer le compte de l'utilisateur connecté
  public function getAccountConnected(){
    if($this->isUserConnected()){
      return $_SESSION['user'];
    }
    throw new Exception("not login");
  }

  // cette methode permet de renvoyer le login de l'utilisateur connecté
  public function getUserLogin(){
    if($this->isUserConnected()){
      return $_SESSION['user'].getLogin();
    }
    throw new Exception("not login");
  }

  // cette methode permet de déconnecter l'utilisateur
  public function disconnectUser(){
    unset($_SESSION['user']);
  }





















}






 ?>
