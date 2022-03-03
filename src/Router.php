<?php
require_once("model/MovieStorage.php");
require_once("model/AccountStorage.php");
require_once("view/View.php");
require_once("view/PrivateView.php");
require_once("control/Controller.php");
require_once("model/MovieBuilder.php");
require_once("model/AuthenticationManager.php");
/**
 * la classe router permert d'analayser l'url (l'action de l'utilisateur) et puis afficher la page voulu par l'utilisateur
 * cette classe crée un controleur et une vue : le controleur permet d'interagir avec le model et demande de la vue de preparer la page correspond à l'action
 */
class Router
{
  // methode principale: qui permet de faire l'anlyse de l'url et affiche la page preparée
  public function main(MovieStorage $moviedb,AccountStorage $accountdb){

    session_start(); // initialiser la session
    // on passe le feedback de la requete précédente à la requete courante
    $feedback = key_exists("feedback",$_SESSION) ? $_SESSION["feedback"] : "";
    $_SESSION["feedback"] = "";

    // on crée un gestionnaire de l'authentification
    $manager = new AuthenticationManager();

    // on crée une vue en fonction de l'etat de l'internaute : connecte/deconnecte
     if($manager->isUserConnected()){
       $view = new PrivateView($this,$feedback,$manager->getAccountConnected());
     }else{
       $view = new view($this,$feedback);
     }

    // on crée le controleur en lui passant la vue crée et les bdd et le manager
    $controller = new Controller($view,$moviedb,$accountdb,$manager);

    // analyse de l'url

    $movieId = key_exists('id',$_GET) ? $_GET['id'] : null;
    $action = key_exists('action',$_GET) ? $_GET['action'] : null;

    if($action === null){
      $action = ($movieId === null) ? "home" : "show" ;
    }

    // lister les action d'un utilisateur connecté
    $authentification_action = ["show","new","saveNew","askdelete","delete","modify","saveModify","deconnexion","askDeleteAccount","deleteAccount"] ;
    // ici on controle les action de l'utilisateur non connecte : l'utilisateur non connecté peut par exemple essayer d'acceder à la page de création d'un film à travers l'url
    // et cette page n'est accessible que par l'utilisateur connecté, donc on controle ses action afin de l'empecher de faire ce genre de manipulation

    if(in_array($action,$authentification_action,true) and $manager->isUserConnected() === false){// si l'utilisateur n'est pas connecté et essaie de faire une action d'un utilisateur connecté
      $action = "connexion"; // on l'envoie directement vers la page de connexion
    }
    try {

      switch ($action) {
        // rq : l'appel aux methodes du controleur permet de faire des verifications sur le model avant de preparer la page voulu
        // et l'appel aux methodes de la vue permet de preparer la page : comme cela le router se contente de faire l'analyse url et l'affichage à la fin
        case 'home':
          $view->makeHomePage(); // preparer la page d'accueil
          break;
        case 'show':
          $controller->showInformation($movieId); // preparer la page du film
          break;
        case 'new':
          $controller->newMovie(); // preparer la page de creation d'un nouveau film
          break;
        case 'saveNew':
          $controller->saveNewMovie($_POST); // enregistrer le noveau film
          break;
        case 'askdelete':
          if($movieId === null){// action non connu : on doit préciser le film qu'on veut supprimer
            $view->makeUnknownActionPage();
          }else{// aller vers la page de confirmation de suppression
            $controller->askMovieDeletion($movieId);
          }
          break;
        case 'delete':
          if($movieId === null){// action non connu
            $view->makeUnknownActionPage();
          }else{// l'utilisateur confirme la suppression du film
            $controller->deleteMovie($movieId);
          }
          break;
        case 'modify':
          if($movieId === null){//action non connu : il faut préciser l'identifiant du film à modifier
            $view->makeUnknownActionPage();
          }else{
            $controller->modifyMovie($movieId); // preparer la page de modification (biensur: on verifie si le film existe dans la methode modifyMovie)
          }

          break;
        case 'saveModify':
          if($movieId === null){ // action non connu
            $view->makeUnknownActionPage();
          }else{
            $controller->saveModification($movieId,$_POST); // preparer la page de sauvegarde de la modification
          }
          break;

        case 'list':
            $controller->showList(); // preparer la page affichant la liste des films
            break;
        case 'connexion':
            $view->makeLoginFormPage(); // preparer la page de connexion
            break;
        case 'checkConnexion':
            $controller->makeConnexion($_POST); // faire la verification de la connexion
            break;
        case 'deconnexion':
            $controller->makeDeconnexion(); // faire la déconnexion
            break;
        case 'register':
            $controller->newAccount();// preparer la page de creation d'un compte
            break;
        case 'saveNewAccount':
            $controller->saveNewAccount($_POST); // création du compte
            break;
        case 'askDeleteAccount':
            $view->makeAccountDeletionPage(); // preparer la page de confirmation de la suppression du compte
            break;
        case 'deleteAccount':
            $controller->deleteAccount(); // suppression du compte
            break;
        case 'research':
            if(count($_POST) === 0 or $_POST['keyWord'] === "" ){ // access à la page via le menu ou bien aucun mot saisie
               $view->makeResearchPage();
            }else{ // l'internaute recherche un film
               $controller->makeResultResearch($_POST['keyWord']); // on  fait la recherche et preparer les resultats
            }
            break;
        case 'Apropos':
            $view->makeAproposPage(); // page d'information sur notre site
            break;    
            
        default:
          throw new Exception("page does not existe");
          break;
      }



    } catch (Exception $e) {
        $view->makeUnexpectedErrorPage(); // dans le cas d'erreur on prepare une page d'erreur: une erreur dans la bdd peut produire une erreur
    }


    $view->render(); // affichage de la page



  }

  // cette methode permet de faire une redirection vers la page passer en argument avec un feedback à afficher dans celle-ci
  // but : get => pour afficher / post => envoyer les donnees
  public function POSTredirect($url, $feedback){

    $_SESSION['feedback'] = $feedback;
    header("Location: ".$url,true,303); // pendant l'analyse de la reponse http le client va directement vers la page $url sans meme pas lire le corps de la reponse courante

  }

  // renvoyer l'url de la page d'accueil
  public function getHomeURL(){

    return "?";

  }

  // renvoyer l'url de la page du film
  public function getMovieURL($id){

    return "?id=$id";

  }

  // renvoyer l'url de la page de liste des films
  public function getMovieListURL(){

    return "?action=list";

  }

  // renvoyer l'url de la page de creation d'un film
  public function getMovieCreationURL()
  {

    return "?action=new";

  }

  // renvoyer l'url de la page de sauvegarde d'un film
  public function getMovieSaveURL()
  {

    return "?action=saveNew";

  }

  // renvoyer l'url de la page de confirmation de suppression
  public function getMovieAskDeletionURL($id)
  {

    return "?id=$id&action=askdelete";

  }

  // renvoyer l'url de la page de suppression
  public function getMovieDeletionURL($id)
  {

    return "?id=$id&action=delete";

  }

  // renvoyer l'url de la page de modification
  public function getMovieModifyPageURL($id){

    return "?id=$id&action=modify";

  }

  // renvoyer l'url de la page de sauvegarde de la modification
  public function getMovieSaveModificationURL($id)
  {

    return "?id=$id&action=saveModify";

  }

  // renvoyer l'url de la page de connexion
  public function getConnexionPageURL()
  {

    return "?action=connexion";

  }

  // renvoyer l'url de la page de verification de la connexion
  public function getCheckConnexionPageURL(){

    return "?action=checkConnexion";

  }

  // renvoyer l'url de la page de deconnexion
  public function getDeconnexionPageURL(){

    return "?action=deconnexion";

  }

  // renvoyer l'url de la page de registration
  public function getRegistrationPageURL(){

    return "?action=register";

  }

  // renvoyer l'url de la page de creation du compte
  public function getAccountSaveURL(){

    return "?action=saveNewAccount";

  }

  // renvoyer l'url de la page de confirmation de suppression du compte
  public function getAccountAskDeletionURL()
  {

    return "?action=askDeleteAccount";

  }

  // renvoyer l'url de la page de suppression du compte
  public function getAccountDeletionURL()
  {

    return "?action=deleteAccount";

  }

  // renvoyer l'url de la page de recherche
  public function getResearchPageURL(){

    return "?action=research";

  }

  public function getAproposPageURL(){

    return "?action=Apropos";

  }



}











 ?>
