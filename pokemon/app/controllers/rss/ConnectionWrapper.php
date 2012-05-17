<?php
require_once 'lib/Config.php';
require_once 'lib/DatabaseConnectionFactory.php';

class ConnectionWrapper {
  private $connection;
  const sel = 'qsdfhsdherh';
  
  public function __construct() {
    $this->connection = DatabaseConnectionFactory::get(Config::getConfigParam('DatabaseConnection', 'default_profile'));
  }
  
  public function signIn($login, $pwd) {
    $statement = $this->connection->prepare('SELECT user_id FROM user WHERE user_login = :login AND user_password = :pwd;');
    $statement->bindParam(':login', $login);
    $saltedPwd=hash('sha256', $pwd.self::sel);
    $statement->bindParam(':pwd', $saltedPwd);
    if ($statement->execute() === FALSE) {
      return FALSE;
    }
    
    if ($user = $statement->fetch()){
      return $user["user_id"];
    }
    return FALSE;
  }
  
  public function getTags() { 
    if (($result = $this->connection->query('SELECT * FROM tag')) === FALSE) {
      return array();
    }
    
    $tags = array();
    while ($row = $result->fetch()) {
        array_push($tags, array("titre" => $row["tag_nom"], "id" => $row["tag_id"]));
    }
    return $tags;
  }
  
  public function signUp($login, $pwd, $email) {
    $insertStatement = $this->connection->prepare('INSERT INTO user(user_login, user_password, user_email) VALUES(:login, :pwd, :email);');
    $insertStatement->bindParam(':login', $login);
    $saltedPwd=hash('sha256', $pwd.self::sel);
    $insertStatement->bindParam(':pwd', $saltedPwd);
    $insertStatement->bindParam(':email', $email);
    
    if($insertStatement->execute() === FALSE) {
      return FALSE;
    }
    return $this->signIn($login, $pwd);
  }    
}
?>