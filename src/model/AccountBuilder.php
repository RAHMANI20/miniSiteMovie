<?php


require_once("model/Account.php");

/**
 * cette classe represente un createur de comptes et elle permet de gérer les erreurs de création
 */

class AccountBuilder
{
  protected $data; // stocke les information saisies par l'utilisateur
  protected $error; // stocke les erreurs de saisies

  const NAME = "Name";
  const LOGIN = "Login";
  const PASSWORD = "Password";

  public function __construct($data=null)
  {
    if($data === null){
      $data = array(self::NAME =>"",self::LOGIN=>"",self::PASSWORD=>"");
    }
    $this->data = $data;
    $this->error = array();
  }
  // cette methode permet de créer un builderAccount à partir d'un compte
  public static function buildFromAccount(Account $a){
    return new AccountBuilder(array(self::NAME => $a->getName(),
                                   self::LOGIN => $a->getLogin(),
                                   self::PASSWORD => $a->getPassword()
                                 ));
  }

 // getters

  public function getData()
  {
    return $this->data;
  }

  public function getError()
  {
    return $this->error;
  }

  // cette methode permet de créer un compte : l'appel se fait aprés avoir vérifié les information entrés par l'utilisateur
  // le statut de l'utilisateur créer est 'user'
  // rq : on stocke le hash du mot de passe dans la base mysql
  public function createAccount()
  {
    return (new Account($this->data[self::NAME],$this->data[self::LOGIN],$this->data[self::PASSWORD],'user'));
  }

  // cette methode verifié les infos du compte à créer
  public function isValid(AccountStorage $accountdb)
  {

    $this->error = array();
    if($this->data[self::NAME] === "" )
         $this->error[self::NAME] = "you must enter your name";
    if($this->data[self::LOGIN] === "")
         $this->error[self::LOGIN] = "you must enter your login ";
    else if($accountdb->exists($this->data[self::LOGIN]))
         $this->error[self::LOGIN] = "login already exists, you should choose another ;)";
    if($this->data[self::PASSWORD] === "")
         $this->error[self::PASSWORD] = "you must enter your password";
    else if(!preg_match("/^[0-9a-zA-z]*$/i",$this->data[self::PASSWORD]))
         $this->error[self::PASSWORD] = "your password should contain only letter and number";

    return count($this->error) === 0;
  }

  // cette methode permet de mettre à jour un compte
  public function updateAccount(Account $account){

    $account->setName($this->data[self::NAME]);
    $account->setLogin($this->data[self::LOGIN]);
    $account->setPassword($this->data[self::PASSWORD]);

  }

}


 ?>
