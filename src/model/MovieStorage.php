<?php

require_once ("model/Movie.php");
/**
 * interface represente une base de données des films
 */
interface MovieStorage
{

  public function exists($id);

  public function read($id);

  public function readAll();

  public function create(Movie $a);

  public function delete($id);

  public function update($id,Movie $a);

  public function deleteByCreator($login);

}



 ?>
