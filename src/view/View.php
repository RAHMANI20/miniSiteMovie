<?php

require_once("Router.php");
require_once("model/Movie.php");
require_once("model/MovieBuilder.php");
require_once("model/AccountBuilder.php");

/**
 * la classe view represente une page : elle contient des methodes qui permettent de preparer les differents pages
 * rq : on a met le corps de la page a afficher dans une autre fichier tempalate.php
 */

class View
{
  protected $router; // le router de la page sert à a donner les url des differents page
  protected $title; // titre de la page : change en fonction de la methode appelé
  protected $feedback; // le feedback de la page à afficher
  protected $content; // le contenu de la page : change en focntion de la pethode appelé
  protected $style;

  public function __construct(Router $router,$feedback){
    $this->router = $router;
    $this->feedback = $feedback;
    $this->title = null;
    $this->content = null;
  }

  // cette methode permet d'afficher la page
  public function render(){

    if($this->title === null || $this->content === null){
      $this->makeUnexpectedErrorPage();
    }

    $title = $this->title;
    $feedback = $this->feedback;
    $content = $this->content;

    include("template.php");

  }

  // la page d'accueil
  public function makeHomePage(){

    $this->title = "Home";
    $this->content = "Site about movies";
    $this->style = "content{font_size:50px;}";

  }

  // la page d'un film
  public function makeMoviePage(Movie $movie,$id){

    $title = htmlspecialchars($movie->getTitle());
    $director = htmlspecialchars($movie->getDirector());
    $releaseYear = htmlspecialchars($movie->getReleaseYear());
    $genre = htmlspecialchars($movie->getGenre());
    $description = htmlspecialchars($movie->getDescription());


    $this->title = $title;
    $this->content .= "$title est un film de genre $genre sortie en $releaseYear réalisé par $director  est $age";
    $this->content .= "<p>$description</p>";

    $this->content .= "<a href='".$this->router->getMovieModifyPageURL($id)."' >modify</a>";
    $this->content .= "<a href='".$this->router->getMovieAskDeletionURL($id)."' >delete</a>";

  }

  // page qui liste les films
  public function makeListPage($listMovie){


    $this->title = "Movies";
    $this->style = ".content a{text-decoration:none;background:black;color:white;border-radius:10px;margin-top:2em;}
                    .content img{width:150px;height:130px;border-radius:10px}
                    .content ul{list-style:none;display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:1em;}
                    .content li{margin:0.5em;}";
    $this->content = "<div class='test'><ul>";
    foreach ($listMovie as $id => $movie) {
      $this->content .= "<li><img src='upload/".$movie->getImage()."'><br/>
                             <a href='".$this->router->getMovieURL($id)."'>".$movie->getTitle()." (".$movie->getReleaseYear().")<a>
                        </li>";
    }
    $this->content .= "</ul></div>";

  }



  // page de creation d'un  film : accessible seulement par les utilisateur connecté
  public function makeMovieCreationPage(MovieBuilder $mb){

    $this->title = "Add your movie";
    $this->style = ".content div{margin:0.5em}
                    .content label{margin-left:-14em}
                    .content input{margin-left:-5em;width:20em;height:2em;border-radius:10px;}
                    .content textarea{margin-left:-5em;border-radius:10px;}
                     button:hover{background:red;}
                    .content h4{font-size:18px}
                    .content button{margin-left:-5em;margin-bottom:1em;width:10em;height:3em;}";
    $this->content = '<form enctype="multipart/form-data" action='.$this->router->getMovieSaveURL().' method="post">';
    $this->content .= $this->makeForm($mb);
    $this->content .= '<div>
                         <label>'.MovieBuilder::IMAGE.' :</label><br/><input type="file" name="'.MovieBuilder::IMAGE.'">
                         <h4>'.(key_exists(MovieBuilder::IMAGE,$mb->getError())?$mb->getError()[MovieBuilder::IMAGE]:"").'</h4>
                      </div>';
    $this->content .= '<button type="submit">create</button>
                      </form>';
  }

  // page qui permet de faire une redirection vers la page du film qu'on vient de créer en affichant un feedback de success
  public function displayMovieCreationSuccess($id){

    $this->router->POSTredirect($this->router->getMovieURL($id),"succes creation");

  }

  // page qui permet de faire une redirection vers la page de creation de film en affichant un feedback d'echec
  public function displayMovieCreationFailure(){

    $this->router->POSTredirect($this->router->getMovieCreationURL(),"failure creation");

  }

  // page de confirmation de suppression du film
  public function makeMovieDeletionPage($id){


    $this->title = "Delete movie";
    $this->style = "input:hover{background:red;}";
    $this->content = "<p>are you sure that you want to delete this movie ?</p>";
    $this->content .= '<form action="" method="post">
                        <input style="margin-bottom:1em;width:10em;height:3em;" type="submit" formaction= "'.$this->router->getMovieDeletionURL($id).'" name="confirmation" value="confirmation">
                     </form>';
  }

  // cette page permet de faire une redirection vers la page de liste des films avec un feedback de succes : appelé aprés avoir supprimé le film
  public function makeMovieDeletedPage(){

    $this->router->POSTredirect($this->router->getMovieListURL(),"you have successfully deleted the movie ");

  }

  // page de modification d'un film
  public function makeMovieModifyPage($id,MovieBuilder $mb){

    $this->title = "Modify movie";
    $this->style = ".content div{margin:0.5em}
                    .content label{margin-left:-14em}
                    .content input{margin-left:-5em;width:20em;height:2em;border-radius:10px;}
                    .content textarea{margin-left:-5em;border-radius:10px;}
                     button:hover{background:red;}
                    .content h4{font-size:18px}";

    $this->content = '<form  action='.$this->router->getMovieSaveModificationURL($id).' method="post">';
    $this->content .= $this->makeForm($mb);
    $this->content .= '<button style="margin-left:-5em;margin-bottom:1em;width:10em;height:3em;" type="submit">Modify</button>
                      </form>';

  }

  // page de redirection vers la page du film qu'on vient de modifier avec un message de succes (feedback)
  public function makeMovieModifiedPage($id){

    $this->router->POSTredirect($this->router->getMovieURL($id),"the page has been modified");

  }

  // page de redirection vers la page de modification avec un message d'erreur (feedback)
  public function makeMovieFailureModifiedPage($id){

    $this->router->POSTredirect($this->router->getMovieModifyPageURL($id),"error in the form");

  }

  // page de connexion
  public function makeLoginFormPage(){
    $this->title = "connexion";
    $this->style = ".content form{display:grid;grid-template-columns:1fr;gap:0.5em;justify-items:center;justify-items:center;}
                    .content input{width:25em;height:2em;border-radius:10px;}
                    .content label{margin-top:1em;}
                    button:hover{background:red;}
                    .content a{text-decoration:none;background:black;border-radius:10px}";

    $this->content = '<form class="connexion" action="'.$this->router->getCheckConnexionPageURL().'" method="post">
                       <label>Login :</label> <input type="text" name="login" value="">
                       <label>Password : </label> <input type="password" name="password" value="">
                      <button type="submit" style="margin-top:2em; width:10em;height:3em;">sign in</button>
                      <p style="font-size:23px">you are not registered on the website  <a  href="'.$this->router->getRegistrationPageURL().'" > > sign up  </a><p>
                     </form>';
  }

  // redirection vers la page d'accueil avec un feedback de succes
  public function makeConnexionSuccessPage(){

    $this->router->POSTredirect($this->router->getHomeURL(),"sucess connexion");

  }

  // redirection vers la page de connexion avec un feedback d'echec de connexion
  public function makeConnexionFailurePage(){

    $this->router->POSTredirect($this->router->getConnexionPageURL(),"invalid login or password");

  }

  // page de creation d'un compte
  public function makeRegistrationPage(AccountBuilder $ab){

    $this->title = "Registration";
    $this->style = ".content form{display:grid;grid-template-columns:1fr;gap:0.5em;justify-items:center;justify-items:center;}
                    .content input{width:25em;height:2em;border-radius:10px;}
                    .content label{margin-top:1em;}
                    button:hover{background:red;}
                    .content button{margin-top:2em;margin-bottom:2em; width:10em;height:3em; }
                    .content h4{font-size:18px;}";

    $this->content ='<form class="" action="'.$this->router->getAccountSaveURL().'" method="post">';
    $this->content .=   $this->makeFormRegistration($ab);
    $this->content .=  '<button type="submit" >sign up</button>
                    </form>';

  }

  // redirection vers la page de connexion avec un feedback de succes de creation
  public function displayAccountCreationSuccess(){

    $this->router->POSTredirect($this->router->getConnexionPageURL(),"your account has been created successfully");

  }

  // redirection vers la page de creation du compte avec un feedback d'erreur de creation
  public function displayAccountCreationFailure(){

    $this->router->POSTredirect($this->router->getRegistrationPageURL(),"failure creation");

  }

  // page de confirmation de suppression du compte
  public function makeAccountDeletionPage(){

    $this->title = "Delete Account";
    $this->style = "input:hover{background:red}";
    $this->content = "<p>Once you delete your account, there is no going back. Please be certain.</p>";
    $this->content .= '<form action="" method="post">
                        <input style="margin-bottom:1em;width:10em;height:3em;" type="submit" formaction= "'.$this->router->getAccountDeletionURL().'" name="deleteAccount" value="Delete your account">
                      </form>';
  }

  // redirection vers la page de creation du compte avec un message de succes de suppression
  public function makeAccountDeletedPage(){

    $this->router->POSTredirect($this->router->getRegistrationPageURL(),"your account has been successfully deleted");
  }

  // la page qui permet de faire des recherche
  public function makeResearchPage(){

    $this->title = "Research movie";
    $this->style = ".content form{padding:5em;display:grid;gap:0.5em;justify-items:center;}
                    .content label{font-size:25px}
                    .content input{width:25em;height:2em;border-radius:10px}
                    .content button{width:8em;height:2em;}
                    .content button:hover{background:red}
                    .content a{text-decoration:none;background:black;border-radius:10px;}
                    .content img{width:150px;height:130px;border-radius:10px;}
                    .content ul{list-style:none;display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:1em;}
                    .content li{margin:0.5em;}";
    // barre de recherche
    $this->content = '<form action="?action=research" method="POST">
                       <label>Enter your search </label>
                       <input type="search" name="keyWord" value="">
                       <button type="submit">search</button>
                      </form>';
    // le corps de la condition n'est pas accédé si il s'agit de : la premiere visite à la page / rien n'est saisie sur la barre / aucun film trouvé
    if(key_exists('moviesFound',$_SESSION) and $_SESSION['moviesFound'] !== null){ // on affiche les films trouvées
      $this->content .= "<h3 style='margin-top:-2em;'>movies found :</h3>";

      $this->content .= "<ul>";
      foreach ($_SESSION['moviesFound'] as $id => $movie) {
        $this->content .= "<li><img src='upload/".$movie->getImage()."'><br/>
                           <a href='".$this->router->getMovieURL($id)."'>".$movie->getTitle()." (".$movie->getReleaseYear().")<a></li>";
      }
      $this->content .= "</ul>";

      $_SESSION['moviesFound'] = null ; // mettre la variable à null
    }


  }
  public function makeAproposPage(){

    $this->title = "A PROPOS";
    $this->style = ".content{font-size:20px;text-align:left;padding:1em}";
    $this->content = "<h1>AUTEURS-Groupe-35</h1> <ul><li>RAHMANI Faical Sid Ahmed 4A (22010400)</li><li>KRIMI Ibrahim 3A (22011592)</li></ul>";
    $this->content .= "<h1>Compléments Réalisés:</h1>
                      <ul>
                        <li>Une recherche d'objets</li>
                        <li>Tri de la liste des objets (par date)</li>
                        <li>Associer des images aux objets (non modifiable)</li>
                      </ul>";
    $this->content .= "<h1>répartition</h1>
                       <p>Afin de produire le site dans la limite du temps donné, nous avons dû élaborer un plan de
                        travail. Nous avons donc décidé dans un premier temps de terminer tous les tps.</br>
                        une fois on a terminé les tps, on a commencé a échanger les solution et les idées afin de les
                        appliquer sur notre sujet choisi (stockage des objets qui représentent des informations sur des films).<br/>
                        concernant le complément, faical a fait la recherche des objets + upload des images,tandis que ibrahim s'est occupé
                        d'implementer le tri des objets.</p>";
    $this->content .= "<h1>MODELISATION</h1>
                       <h2>Architecture du site :MVCR</h2>
                      <ul>
                        <li>Indexe : represente le poin d'arrivée de l'internaute.</li>
                        <li>Le routeur : analyse le contenu de la requête HTTP, méthode utilisée, URL, contenu des tableaux GET et POST</li>
                        <li>Le controleur : il fait le lien entre le modèle et la vue, il met à jour le modèle en fonction des choix de l'internaute.</li>
                        <li>La vue : Elle utilise l'état du modèle pour générer du HTML en fonctions des demandes du contrôleur.</li>
                      </ul>
                       <h2>Base de donnees:(PDO)</h2>
                        <p>on a utilisé l'objet PDO qui permet de se connecter à notre base de données mySQL, de communiquer avec elle :</p>";
    $this->content .=   "<p>dans notre base on a deux tables:</p>";
    $this->content .=  "<ul><li>accounts</br><img src='skin/images/accounts.png'/></li>
                            <li>movies</br><img src='skin/images/movies.png'/></li></ul>";

    $this->content .= "<h2>POST-redirect-GET:</h2>
                     <p>On a pu amélioré le confort de notre site en appliquant le principe 'Post-redirect-Get'</p>
                      <ul>
                        <li>Les pages destinées à être accédées en POST ne doivent pas être visibles directement par les internautes.</li>
                        <li>En redirigeant systématiquement après un POST, on donne à chaque méthode son rôle de base</li>
                        <li>POST modifie les données, GET affiche les données.</li>
                        <li>un feedback passée de la requete courante à la prochaine requete en utilisant une variable de session</li>
                      </ul>";
   $this->content .= "<h1>AUTRES</h1>
                      <h2>Sans authentification, un utilisateur a accès à :</h2>
                        <ul>
                         <li>la liste de tous les objets</li>
                         <li>la page de création de compte</li>
                         <li>la page de connexion</li>
                         <li>la liste de tous les objets (mais pas le détail)</li>
                        </ul>
                     <h2>Les utilisateurs authentifiés peuvent :</h2>
                       <ul>
                        <li>voir la page de détail de chaque objet</li>
                        <li>ajouter de nouveaux objets.</li>
                        <li>modifier/supprimer les objets qui lui appartiennent (mais pas ceux des autres)</li>
                        <li>quitter le site en supprimant son compte et tous les objets qu'il a crées</li>
                      </ul>";



  }


  // redirection vers la page de recherche en donnant le resultat de la recherche
  public function makeResearchResultPage($feedback){
    $this->router->POSTredirect($this->router->getResearchPageURL(),$feedback);
  }



  // page qui s'affiche quand l'utilisateur essaie de faire une action non atorisée
  public function makeNotAllowedAccessPage(){

    $this->title = "Denied access";
    $this->content = "you cannot access this page";

  }

  // page qui s'affiche si l'utilisateur essaie d'acceder à un film qui n'existe pas
  public function makeInvalidMoviePage(){

    $this->title = "Error";
    $this->content = "invalid Movie :(";

  }

  // page qui s'affiche si le film auquel on veut acceder n'existe pas
  public function makeUnknownMoviePage(){

    $this->title = "Error";
    $this->content = "Unknown movie :(";

  }

  // page qui s'affiche si l'utilisateur fait une action non connu
  public function makeUnknownActionPage(){

    $this->title = "Error";
    $this->content = "Unknown action !!";

  }

  // page qui s'affiche au cas d'une ereur non prévu comme une erreur au niveau de la base de donnees
  public function makeUnexpectedErrorPage(){

    $this->title = "Error";
    $this->content = "An unexpected error has occurred . ";

  }

  // pour faire un affichage qui permet de corriger les erreurs
  public function makeDebugPage($variable) {
	  $this->title = 'Debug';
	  $this->content = '<pre>'.htmlspecialchars(var_export($variable, true)).'</pre>';
  }



  // methode qui permet de créer un menu
  public function makeMenu(){

    return array(
      "Home" => $this->router->getHomeURL(),
      "Movies" => $this->router->getMovieListURL(),
      "Sign in" => $this->router->getConnexionPageURL(),
      "Sign up" => $this->router->getRegistrationPageURL(),
      "Research" => $this->router->getResearchPageURL(),
      "À propos" => $this->router->getAproposPageURL(),
    );

  }

  // cette methode permet d'ajouter un formulaire pour la creation d'un film
  public function makeForm(MovieBuilder $mb){
              return  '<div>
                         <label>'.MovieBuilder::TITLE.' :<br/> <input type="text" name="'.MovieBuilder::TITLE.'" value="'.$mb->getData()[MovieBuilder::TITLE].'"></label>
                         <h4>'.(key_exists(MovieBuilder::TITLE,$mb->getError())?"*".$mb->getError()[MovieBuilder::TITLE]:"").'</h4>
                       </div>
                       <div>
                         <label>'.MovieBuilder::DIRECTOR.' :</label><br/> <input type="text" name="'.MovieBuilder::DIRECTOR.'" value="'.$mb->getData()[MovieBuilder::DIRECTOR].'">
                         <h4>'.(key_exists(MovieBuilder::DIRECTOR,$mb->getError())?"*".$mb->getError()[MovieBuilder::DIRECTOR]:"").'</h4>
                       </div>
                       <div>
                         <label>'.MovieBuilder::RELEASE_YEAR.' :</label><br/> <input type="text" name="'.MovieBuilder::RELEASE_YEAR.'" value="'.$mb->getData()[MovieBuilder::RELEASE_YEAR].'">
                         <h4>'.(key_exists(MovieBuilder::RELEASE_YEAR,$mb->getError())?"*".$mb->getError()[MovieBuilder::RELEASE_YEAR]:"").'</h4>
                       </div>
                       <div>
                         <label>'.MovieBuilder::GENRE.' :</label><br/> <input type="text" name="'.MovieBuilder::GENRE.'" value="'.$mb->getData()[MovieBuilder::GENRE].'">
                         <h4>'.(key_exists(MovieBuilder::GENRE,$mb->getError())?"*".$mb->getError()[MovieBuilder::GENRE]:"").'</h4>
                       </div>
                       <div>
                         <label>'.MovieBuilder::DESCRIPTION.':</label> <br/> <textarea name="'.MovieBuilder::DESCRIPTION.'" rows="10" cols="30">'.$mb->getData()[MovieBuilder::DESCRIPTION].'</textarea>
                         <h4>'.(key_exists(MovieBuilder::DESCRIPTION,$mb->getError())?"*".$mb->getError()[MovieBuilder::DESCRIPTION]:"").'</h4>
                       </div>';
  }

  // cette methode permet d'ajouter un formulaire pour la creation d'un compte
  public function makeFormRegistration(AccountBuilder $ab){
              return  '
                         <label>'.AccountBuilder::NAME.' :</label> <input type="text" name="'.AccountBuilder::NAME.'" value="'.$ab->getData()[AccountBuilder::NAME].'">
                         <h4>'.(key_exists(AccountBuilder::NAME,$ab->getError())?"*".$ab->getError()[AccountBuilder::NAME]:"").'</h4>

                       <div>
                         <label>'.AccountBuilder::LOGIN.' :</label><br/> <input type="text" name="'.AccountBuilder::LOGIN.'" value="'.$ab->getData()[AccountBuilder::LOGIN].'">
                         <h4>'.(key_exists(AccountBuilder::LOGIN,$ab->getError())?"*".$ab->getError()[AccountBuilder::LOGIN]:"").'</h4>
                       </div>
                       <div>
                         <label>'.AccountBuilder::PASSWORD.' :</label><br/> <input type="password" name="'.AccountBuilder::PASSWORD.'" value="'.$ab->getData()[AccountBuilder::PASSWORD].'">
                         <h4>'.(key_exists(AccountBuilder::PASSWORD,$ab->getError())?"*".$ab->getError()[AccountBuilder::PASSWORD]:"").'</h4>
                       </div>';
  }





}






 ?>
