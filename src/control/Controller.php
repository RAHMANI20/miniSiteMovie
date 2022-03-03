<?php

require_once("model/Movie.php");
require_once("view/View.php");
require_once("view/PrivateView.php");
require_once("model/AuthenticationManager.php");
require_once("model/MovieStorage.php");
require_once("model/MovieBuilder.php");
require_once("model/AccountStorage.php");
require_once("model/AccountBuilder.php");



/**
 * la classe controller permet de faire le lien entre le model et la vue : histoire d'interroger le model et demander de preparer la page à la vue
 * elle contient une reference sur la vue et sur le model, elle contient des methodes qui fonts des traitement (controle d'acces , interroger la bdd ...)  et passent le relais à la vue pour preparer la page
 */

class Controller
{
  protected $view; // la vue
  protected $moviedb; // bdd des films (ou bien l'acces a la table movies de notre bdd)
  protected $accountdb; // bdd des comptes (ou bien l'acces a la table accounts  de notre bdd)
  protected $currentNewMovie; // permet de stocker le builder de film non validé dans une variable de session : comme ça l'utilisateur retrouve les infos saisie quand il retourne a la page de creation
  protected $modifiedMovies; // table qui stocke tous les builder non validés lors d'une modification dans une variable de session
  protected $currentNewAccount; // permet de stocker le builder de compte non validé dans une variable de session : comme ça l'utilisateur retrouve les infos saisie quand il retourne a la page de creation
  protected $manager; // stocke gestionnaire de l'authentification passée par le router

  public function __construct(View $view,MovieStorage $moviedb,AccountStorage $accountdb,AuthenticationManager $manager)
  {
    $this->view = $view;
    $this->moviedb = $moviedb;
    $this->accountdb = $accountdb;
    $this->manager = $manager;
    $this->currentNewMovie = key_exists("currentNewMovie",$_SESSION)?$_SESSION["currentNewMovie"]:null;
    $this->modifiedMovies = key_exists("modifiedMovies",$_SESSION)?$_SESSION["modifiedMovies"]:array();
    $this->currentNewAccount = key_exists("currentNewAccount",$_SESSION)?$_SESSION["currentNewAccount"]:null;
  }
  public function __destruct(){
    // a la fin d'execution du script le destructeur est appelé et qui va mettre à jour les variable de session qui servent à sauvegarder les infos non valide (builders non valide)
    // ce qui permettra à l'utilisateur de retrouver ces infos quand il revient à la page / en cas de succes la mise à jour revient à mettre null/tableau vide
    $_SESSION["currentNewMovie"] = $this->currentNewMovie;
    $_SESSION["modifiedMovies"] = $this->modifiedMovies;
    $_SESSION["currentNewAccount"] = $this->currentNewAccount;
  }

  // permet de preparer la page du film
  public function showInformation($id) {

    $movie = $this->moviedb->read($id); // recuperer le film de la bdd
    if($movie !== null){ // le film existe
      $this->view->makeMoviePage($movie,$id); // preparer la page de film
    }else{ // le film n'existe pas
      $this->view->makeUnknownMoviePage(); // page qui dit que le film est inconnu
    }

  }

  // permet de preparer la page de liste des films
  public function showList(){
    // recuperer la listes des films de la bdd et preparer la page
    $movies = $this->moviedb->readAll();
    $this->view->makeListPage($movies);


  }

  // permet de preparer la page de creation du film
  public function newMovie(){

    if($this->currentNewMovie === null){ // si currentNewMovie === null cela veut dire que l'utilisateur soit il accede à cette page pour la premiere fois soit il a réussit la precedente création
      $this->currentNewMovie = new MovieBuilder();
    }
    $this->view->makeMovieCreationPage($this->currentNewMovie); // preparer la page de creation en passant le constructeur de film comme argument

  }

  // permet de suavegarder le film crée
  public function saveNewMovie(array $data){

    $this->currentNewMovie = new MovieBuilder($data); // crée un builder à partir des infos saisie et envoyé par l'utilisateur
    if($this->currentNewMovie->isValid() === false){ // les infos sont valide
      $this->view->displayMovieCreationFailure(); // rediriger l'utilisateur vers la page d'echec : ici c'est la page de creation avec un feedback affiché
    }else{
      // les infos sont valides
      $movie = $this->currentNewMovie->createMovie($this->manager->getAccountConnected()->getLogin()); // crée le film en passant comme argument le créateur étant l'utilisateur connecté
      $id = $this->moviedb->create($movie); // crée le film dans la bdd
      $this->currentNewMovie = null; // création réussite: pas besoin de stocké un builder non valide
      $this->view->displayMovieCreationSuccess($id); // rediriger l'utilisateur vers la page de succes : ici c'est la page de du film qu'on vient de créer avec un feedback affiché
    }


  }

  // permet de preparer la page de confirmation de la suppression
  public function askMovieDeletion($id){

    if($this->moviedb->exists($id)){//si le film existe dans bd
    //on verifie si l'utilisateur est autorisé à faire l'action (l'utilisatuer peut nous tromper à traver l'url)
    // par exemple l'utilisateur peut modifier l'url en mettant action = askdelete et id = id_film_autre_utilisateur
       if($this->isAuthorized($this->moviedb->read($id))){ // si l'utilisateur est autorisé à faire l'action sur le film
         $this->view->makeMovieDeletionPage($id); // preparer la page de suppression
       }else{ // l'utilisateur non autorisé
         $this->view->makeNotAllowedAccessPage(); // acces à cette page non autorisé
       }
    }else{// le film n'existe pas
       $this->view->makeUnknownMoviePage();
    }

  }

  // permet de supprimer le film de la base
  public function deleteMovie($id){

    if($this->moviedb->exists($id)){// si le film existe dans bdd

      if($this->isAuthorized($this->moviedb->read($id))){ // action autorisé
        $this->moviedb->delete($id); // supprimer le film de la bdd
        $this->view->makeMovieDeletedPage($id); // rdirection vers la liste des films avec un feedback de succes
      }else{ // action non autorisé
        $this->view->makeNotAllowedAccessPage();
      }

    }else{//le film n'existe pas
      $this->view->makeUnknownMoviePage(); // page de film inconnu
    }

  }

  // permet de peparer la page de modification du film
  public function modifyMovie($id){

    if(key_exists($id,$this->modifiedMovies)){ // une tentative precedente de modifier ce film a echoué : on utiliste le builder non valide pour recuperer les infos non valide
      $this->view->makeMovieModifyPage($id,$this->modifiedMovies[$id]);
    }else{
        $movie = $this->moviedb->read($id); // recuperer le film de la bdd
        if($movie === null){ // le film n'existe pas
           $this->view->makeUnknownMoviePage();
        }else{
          if($this->isAuthorized($this->moviedb->read($id))){ // action autorisé
            $mb = MovieBuilder::buildFromMovie($movie); // créer un builderMovie a partir du film
            $this->view->makeMovieModifyPage($id,$mb); // preparer la page de modification
          }else{
            $this->view->makeNotAllowedAccessPage(); // access non autorisé
          }

        }
    }

  }

  // permet de sauvegarder la modification
  public function saveModification($id,array $data){

    $movie = $this->moviedb->read($id);

    if($movie === null){// si le film n'existe pas
      $this->view->makeInvalidMoviePage();
    }else{// le film existe
      if($this->isAuthorized($this->moviedb->read($id))){ // action autorisé
        $mb = new MovieBuilder($data); // crée un builder a partir des infos envoyées par un post
        if($mb->isValid()){// si les données sont valides
          $mb->updateMovie($movie); // modifier le film
          $this->moviedb->update($id,$movie);// mettre à jour le film dans la base de donnees
          unset($this->modifiedMovies[$id]); // enlever le builder non valide pour eviter d'afficher les infos non valide à la prochaine visite
          $this->view->makeMovieModifiedPage($id);// redirection vers la page du film avec un feedback de reussite
        }else{// les données ne sont pas valides
          $this->modifiedMovies[$id] = $mb; // ajouter le builder non valide a la liste pour l'afficher a la prochaine visite
          $this->view->makeMovieFailureModifiedPage($id); // redirection vers la page de modification avec un feedback d'echec
        }
      }else{
        $this->view->makeNotAllowedAccessPage(); // access non autorisé
      }


    }

  }

   // permet de connecter l'utilisateur selon les infos saisie
  public function makeConnexion($data){

    $connexion = $this->accountdb->checkAuth($data['login'],$data['password']); // verifier les infos
    if($connexion !== null){ // les infos sont valide : il existe un compte correspond à ces infos
      $this->manager->connectUser($connexion); // connecter l'utilisateur
      $this->view->makeConnexionSuccessPage(); // redirection vers la page d'accueil en saluant l'utilisateur connecté
    }else{
      $this->view->makeConnexionFailurePage(); //redirection vers la page de connexion avec un feedback disant que les infos entrée ne sont pas valides
    }


  }

  // permet de deconnecter l'utilisateur
  public function makeDeconnexion(){

    $this->manager->disconnectUser(); // deconnecter l'utilisateur
    $this->view->makeDeconnexionPage(); // preparer la page de deconnexion : redirection vers la page d'acceuils avec un feedback
    $this->currentNewMovie = null;
    $this->modifiedMovies = null;
  }

  // permet de creer un nouveau compte
  public function newAccount(){
    if($this->currentNewAccount === null){ // pas de erreur de saisie precedement
      $this->currentNewAccount = new AccountBuilder();
    }
    $this->view->makeRegistrationPage($this->currentNewAccount); // preparer la page de creation de coopte
  }

  // permet de suavegarder le compte crée
  public function saveNewAccount(array $data){
    $this->currentNewAccount = new AccountBuilder($data); // crée un builder Account a partir du post
    if($this->currentNewAccount->isValid($this->accountdb) === false){ // les infos ne sont pas valide
      $this->view->displayAccountCreationFailure(); // redirection vers la page de creation avec feedback
    }else{
      // les infos saisie sont valide
      $account = $this->currentNewAccount->createAccount(); // cree le compte
      $this->accountdb->create($account); // crée le compte dans le tableau accounts de la bdd
      $this->currentNewAccount = null; // pas de fausse infos a afficher lors de la prochaien visite
      $this->view->displayAccountCreationSuccess(); // redirection vers la page de connexion avec feedback de succes
    }
  }

  // permet de supprimer le compte utilisateur si celui-ci souhaite quitter notre site définitivement
  public function deleteAccount(){

        $login = $this->manager->getAccountConnected()->getLogin(); // recuperer le login de l'utilisateur
        $this->moviedb->deleteByCreator($login); // supprimer tous ces films de la base
        $this->manager->disconnectUser(); // déconnecter l'utilisateur
        $this->accountdb->delete($login); // supprimer son compte
        $this->view->makeAccountDeletedPage(); // redirection vers la page de creation de compte en affichant un feedback



  }

  // cette methode permet de faire la recherche
  public function makeResultResearch($keyWord){

    $movies = $this->moviedb->readAll(); // récuperer tous les films
    $moviesFound = array(); // stocke les films trouvé pendant la recherche
    $keyWord = trim($keyWord); // supprimer quelques caractere speciaux au debut et la fin
    $keyWord = str_replace(array("\t","\n","$","^","'",";",":",",","?","!",".")," ",$keyWord);
    foreach ($movies as $id => $movie) { // pou chaque film
      // on fait la recherche parraport aux titre + realisteur + année de sortie + genre de film + description
      $movieInfos = $movie->getTitle()." ".$movie->getDirector()." ".$movie->getReleaseYear()." ".$movie->getGenre()." ".$movie->getDescription();

      if(stripos($movieInfos,$keyWord) !== false){// si on trouve le mot à chercher dans les infos du film alors on ajoute le film à la liste des films trouvés

        $moviesFound[$id] = $movie;

      }
    }
    $_SESSION['moviesFound'] = null; // on utilise la variable de session pour stocké les films trouvé
    if(count($moviesFound) === 0){ // aucun film trouvé, on fait une redirection vers la page de d'echec

      $this->view->makeResearchResultPage("No movie found");

    }else{ // au moins un film trouvé, on fait une redirection vers la page de success
      $_SESSION['moviesFound'] = $moviesFound;
      $this->view->makeResearchResultPage("Result :".count($moviesFound)." movie(s) found");
    }


  }

  // cette methode permet de verifie si l'utilisateur connecte est un admin ou bien celui qui a créer le film passer en argument
  // en gros : cette methode retourne true si l'utilisateur connecté est autorisé à editer ou supprimer le film passé en argument sinon false
  public function isAuthorized($movie){
    return $this->manager->getAccountConnected()->getStatut() === "admin" or $this->manager->getAccountConnected()->getLogin() === $movie->getCreator();
  }




}



 ?>
