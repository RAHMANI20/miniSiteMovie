<?php

require_once("view/View.php");
require_once("Router.php");
require_once("model/Account.php");
require_once("model/Movie.php");
;

/**
 * cette classe represente la page d'un utilisateur connecté , elle va avoir une reference sur le compte connecté
 */

class PrivateView extends View
{

  protected $account; // pour stocker le compte connecté

  function __construct(Router $router,$feedback,Account $account)
  {
    Parent::__construct($router,$feedback);
    $this->account = $account;
  }

  // page d'acueil d'un utilisateur connecté
  public function makeHomePage(){

    $this->title = "Home";
    $this->content = "<h2>Welcome ".$this->account->getName()." :) <h2>";
    $this->style = ".content{font-size:20px;}";

  }

  // la page de du film d'un utilisateur connecté : on affiche le delete et modify si le film est crée par l'utilisateur connecté
  public function makeMoviePage(Movie $movie,$id){

    $title = htmlspecialchars($movie->getTitle());
    $director = htmlspecialchars($movie->getDirector());
    $releaseYear = htmlspecialchars($movie->getReleaseYear());
    $genre = htmlspecialchars($movie->getGenre());
    $description = htmlspecialchars($movie->getDescription());
    $image = $movie->getImage();

    $this->title = "Movie";
    $this->style =".content div{font-size:20px;}
                   .content {display:grid; grid-template-columns: 2fr 1fr; align-items:center;justify-items:right; gap:0.2em; border:dashed black;border_radius:50%;}
                   .content img{width:15em;height:15em;border-radius:80px;margin:0.5em}
                   .content div{margin-left:20px;}
                   .content a{margin:1em;text-decoration:none;background:black;color:white;padding:0.3em;border-radius:10px;}
                   .content";
                   $this->content .= "<div>Title: $title<br/>Genre: $genre<br/>Relase year: $releaseYear<br/>
                       Directed by: $director";
    $this->content .= "<p>description:<br/><br/>$description</p>";


    if($this->account->getStatut() === "admin" or $this->account->getLogin() === $movie->getCreator()){
      // si l'utilisateur est un admin ou bien celui qui a crée le film qu'on veut afficher
      $this->content .= "<a href='".$this->router->getMovieModifyPageURL($id)."' >Modify</a>";
      $this->content .= "<a href='".$this->router->getMovieAskDeletionURL($id)."' >Delete</a></div>";

    }

    $this->content .= "<img src = 'upload/$image' />";

  }



  // redirection vers la page d'acceuil avec un feedback de succes quand l'utilisateur se deconnecte
  public function makeDeconnexionPage(){

    $this->router->POSTredirect($this->makeHomePage(),"deconnexion success");

  }


  // le menu d'un utilisateur connecte
  public function makeMenu(){

    return array(
      "Home" => $this->router->getHomeURL(),
      "Movies" => $this->router->getMovieListURL(),
      "New Movie" => $this->router->getMovieCreationURL(),
      "Sign out" => $this->router->getDeconnexionPageURL(),
      "Delete account" => $this->router->getAccountAskDeletionURL(),
      "Research" => $this->router->getResearchPageURL(),
      "À propos" => $this->router->getAproposPageURL(),
    );

  }




}





?>
