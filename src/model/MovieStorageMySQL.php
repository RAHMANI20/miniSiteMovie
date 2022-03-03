<?php

require_once("model/MovieStorage.php");
require_once("model/Movie.php");

/**
 * cette classe represente une base de donnees des films : on utilise l'outil PDO qui permet de gerer la base de donnees
 */
class MovieStorageMySQL implements MovieStorage
{
   private $db;

  function __construct($db)
  {
    $this->db = $db;
  }

  // cette methode  permet de verifier si un film existe dans la bdd
  public function exists($id){

    $rq = "select * from movies where id = :id";
    $stmt = $this->db->prepare($rq);
    $stmt->bindValue(":id",$id,PDO::PARAM_INT);
    $stmt->execute();
    $info = $stmt->fetch(PDO::FETCH_ASSOC);

    return $info !== false;
  }

  // cette methode permet de chercher un film dans la bdd dont l'idantifiant est passé en argumant et le renvoyer
  public function read($id){


    $rq = "select * from movies where id = :id";
    $stmt = $this->db->prepare($rq);
    $stmt->bindValue(":id",$id,PDO::PARAM_INT);
    $stmt->execute();
    $info = $stmt->fetch(PDO::FETCH_ASSOC);
    if($info !== false)
       return new Movie($info['title'],$info['director'],$info['release_year'],$info['genre'],$info['description'],$info['image'],$info['creator']);
    return null;

  }

  // cette methode permet de retourner un tableau qui contient toutes les films
  public function readAll(){

     $rq = "select * from movies order by release_year desc;";
     $stmt = $this->db->prepare($rq);
     $stmt->execute();
     $info = $stmt->fetchAll(PDO::FETCH_ASSOC);
     $movies = array();
     foreach ($info as $movie) {
       $movies[$movie['id']] = new Movie($movie['title'],$movie['director'],$movie['release_year'],$movie['genre'],$movie['description'],$movie['image'],$movie['creator']);
     }
     return $movies;

  }



  // cette methode permet de créer un film dans la bdd à partir d'un objet film et retourne l'identifiant du film
  public function create(Movie $m){
    $rq = "insert into movies(title,director,release_year,genre,description,image,creator) values(:title,:director,:release_year,:genre,:description,:image,:creator);";
    $stmt = $this->db->prepare($rq);
    $data = array(":title" => $m->getTitle(),
                  ":director" => $m->getDirector(),
                  ":release_year" => $m->getReleaseYear(),
                  ":genre" => $m->getGenre(),
                  ":description" => $m->getDescription(),
                  ":image" => $m->getImage(),
                  ":creator" => $m->getCreator(),
                );
    $stmt->execute($data);
    $rq = "select id from movies order by id desc limit 1";
    $stmt = $this->db->query($rq);

    return intVal($stmt->fetch()[0]);

  }

  // cette methode permet de supprimer un film de la bdd dont l'ideantifiant passé en argument
  public function delete($id){

    if($this->exists($id)){
      unlink("upload/".$this->read($id)->getImage()); // pour supprimer l'image du repertoire upload
      $rq = "delete from movies where id = :id";
      $stmt = $this->db->prepare($rq);
      $stmt->bindValue(":id",$id,PDO::PARAM_INT);
      $stmt->execute();
      return true;
    }
    return false;


  }

  // cette methode permet de mettre à jour un film
  public function update($id,Movie $m){

    $rq = "update movies set title = :title, director = :director, release_year = :release_year,genre = :genre,description = :description where id = :id";

    $stmt = $this->db->prepare($rq);
    $data = array(":title" => $m->getTitle(),
                  ":director" => $m->getDirector(),
                  ":release_year" => $m->getReleaseYear(),
                  ":genre" => $m->getGenre(),
                  ":description" => $m->getDescription(),
                  ":id" => $id,
                );
    $stmt->execute($data);

  }

  // cette methode permet de supprimer tous les films de la bdd dont le login du créateur est passé en argument
  public function deleteByCreator($login){

      $movies = $this->readAll();
      //recuperer les films créer par login
      foreach ($movies as $movie ) { // supprimmer les images de ses films
        if($movie->getCreator() === $login)
             unlink("upload/".$movie->getImage());
      }
      $rq = "delete from movies where creator = :login"; // supprimer ses film de la bdd
      $stmt = $this->db->prepare($rq);
      $data = array(":login" => $login);
      $stmt->execute($data);


  }




}



 ?>
