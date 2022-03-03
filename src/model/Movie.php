<?php

/**
 * cette classe represente un film
 */

class Movie
{
  private $title; // titre du film
  private $director; // réalisateur
  private $release_year; // l'anne de sortie
  private $genre; // genre de film
  private $description; // description du film
  private $image; // nom de l'image dans le serveur
  private $creator; // l'utilisateur créateur du film

  function __construct($title,$director,$release_year,$genre,$description,$image,$creator)
  {
    $this->title = $title;
    $this->director = $director;
    $this->release_year = $release_year;
    $this->genre = $genre;
    $this->description = $description;
    $this->image = $image;
    $this->creator = $creator;
  }

  /* getters and setters */

  public function getTitle(){
    return $this->title;
  }

  public function getDirector(){
    return $this->director;
  }

  public function getReleaseYear(){
    return $this->release_year;
  }

  public function getGenre(){
    return $this->genre;
  }

  public function getDescription(){
    return $this->description;
  }

  public function getCreator(){
    return $this->creator;
  }

  public function getImage(){
    return $this->image;
  }



  public function setTitle($title){
    $this->title = $title;
  }

  public function setDirector($director){
    $this->director = $director;
  }

  public function setReleaseYear($release_year){
    $this->release_year = $release_year;
  }

  public function setGenre($genre){
    $this->genre = $genre;
  }

  public function setDescription($description){
    $this->description = $description;
  }

  public function setCreator($creator){
    $this->creator = $creator;
  }

  public function setImage($image){
    $this->image = $image;
  }


}




 ?>
