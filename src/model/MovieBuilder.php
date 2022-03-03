<?php

require_once("model/Movie.php");

/**
 *  cette classe represente un créateur de film (on passe par MovieBuilder avant de créer un film)
 *  elle permet de gérer en particulier les erreurs que l'utilisateur peut comettre pendant la création d'un film ou la modification d'un film
 */

class MovieBuilder
{
  protected $data; // stocke les informations entrée
  protected $error; // stocke les erreurs

  const TITLE = "Title";
  const DIRECTOR = "Director";
  const RELEASE_YEAR = "Release_Year";
  const GENRE = "Genre";
  const DESCRIPTION = "Description";
  const IMAGE = "Image";

  public function __construct($data=null)
  {
    if($data === null){
      $data = array(self::TITLE =>"",self::DIRECTOR =>"",self::RELEASE_YEAR =>"",self::GENRE =>"",self::DESCRIPTION =>"");
    }
    $this->data = $data;
    $this->error = array();
  }

  // créer un builderfilm à partir d'un film
  public static function buildFromMovie(Movie $m){
    return new MovieBuilder(array(self::TITLE => $m->getTitle(),
                                   self::DIRECTOR => $m->getDirector(),
                                   self::RELEASE_YEAR => $m->getReleaseYear(),
                                   self::GENRE => $m->getGenre(),
                                   self::DESCRIPTION => $m->getDescription(),
                                 ));
  }


  /* getters */

  public function getData()
  {
    return $this->data;
  }

  public function getError()
  {
    return $this->error;
  }

  // cette methode permet de créer un film : on l'appelle après la verification des informations entrée par l'utilisateur
  public function createMovie($creator)
  {
    $newName= time()."".$_FILES[MovieBuilder::IMAGE]['name']; // definir le nouveau nom de l'image
    move_uploaded_file($_FILES[MovieBuilder::IMAGE]['tmp_name'],"upload/$newName"); // deplacer l'image vers le dossier upload avec un renommage
    return (new Movie($this->data[self::TITLE],$this->data[self::DIRECTOR],$this->data[self::RELEASE_YEAR],$this->data[self::GENRE],$this->data[self::DESCRIPTION],$newName,$creator));
  }

  // cette methode permet de verifier si les infos saisies par l'utilisateur sont correct
  public function isValid()
  {
    $this->error = array();
    if($this->data[self::TITLE] === "" )
       $this->error[self::TITLE] = "you must enter the title of the movie";
    if($this->data[self::DIRECTOR] === "")
       $this->error[self::DIRECTOR] = "you must enter the director of the movie";
    if($this->data[self::RELEASE_YEAR] === "" || ctype_digit($this->data[self::RELEASE_YEAR]) === false)
       $this->error[self::RELEASE_YEAR] = "you must enter the release year of the movie";
    if($this->data[self::GENRE] === "")
       $this->error[self::GENRE] = "you must enter the gender of the movie";
    if($this->data[self::DESCRIPTION] === "")
       $this->error[self::DESCRIPTION] = "you must enter the description of the movie";

    if(empty($_FILES) === false){

          if($_FILES[self::IMAGE]['error'] !== 0)
              $this->error[self::DESCRIPTION] = "upload failed ";
          else if(exif_imagetype($_FILES[self::IMAGE]['tmp_name']) === false){
              $this->error[self::DESCRIPTION] = "you need to upload an image file ";
              unlink($_FILES[self::IMAGE]['tmp_name']);
          }

    }

    return count($this->error) === 0;
  }

  // cette methode permet de mettre à jour un film selon les information saisies par l'utilisateur
  public function updateMovie(Movie $movie){

    $movie->setTitle($this->data[self::TITLE]);
    $movie->setDirector($this->data[self::DIRECTOR]);
    $movie->setReleaseYear($this->data[self::RELEASE_YEAR]);
    $movie->setGenre($this->data[self::GENRE]);
    $movie->setDescription($this->data[self::DESCRIPTION]);

  }

}











 ?>
