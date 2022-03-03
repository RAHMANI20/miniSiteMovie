<?php

require_once("model/Account.php");

/**
 * interface represente base de donnees de comptes
 */

interface AccountStorage
{
  public function checkAuth($login, $password);

  public function exists($login);

  public function create(Account $a);

  public function delete($login);



}






 ?>
