<?php
class DbOPerations{

  private $con;

  function __construct(){
      require_once dirname(__FILE__).'/DbConnect.php';
      $db=new DbConnect;
      $this->con=$db->connect();
    }

    public function createUser($email,$password,$name,$school){
    if(!$this->emailExists($email)){

      $statement=$this->con->prepare("INSERT INTO users (email,password,name,school) VALUES (?, ?, ?, ?)");
      //bind the parameters we pass to this function
      $statement->bind_param("ssss",$email,$password,$name,$school);/*Define the MissingTypes*/
      if($statement->execute()){
        return USER_CREATED;
      }
      else{
        return USER_FAILURE;
    }
    }
    return USER_EXISTS;
}


    public function userLogin($email,$password){
      if($this->emailExists($email)){
          $hashed_password=$this->getUserPasswordByEmail($email);
          if(password_verify($password,$hashed_password))return USER_AUTHENTICATED;
          else return USER_PASSWORDS_DO_NOT_MATCH;
      }
      else return USER_NOT_FOUND;
    }


    private function getUserPasswordByEmail($email){
        $statement=$this->con->prepare("SELECT password FROM users WHERE email= ?");
        $statement->bind_param("s",$email);
        $statement->execute();
        $statement->bind_result($password);
        $statement->fetch();
        return $password;   }

        //Once user is authenticated we will read the user details
    public function getUserByEmail($email){
      $statement=$this->con->prepare("SELECT id,email,name,school FROM users WHERE email= ?");
      $statement->bind_param("s",$email);
      $statement->execute();
      $statement->bind_result($id,$email,$name,$school);
      $statement->fetch();
      $user=array();
      $user['id']=$id;
      $user['email']=$email;
      $user['name']=$name;
      $user['school']=$school;

      return $user;
    }


    public function updateUser($email,$name,$school,$id){

      $statement=$this->con->prepare("update users set email=?,name=?,school=? where id=?");
      $statement->bind_param("sssi",$email,$name,$school,$id);

      if($statement->execute())return true;

      return false;

    }

    public function updatePassword($currentPAssword,$newPAssword,$email){
        $hashed_password=$this->getUserPasswordByEmail($email);
        if(password_verify($currentPAssword,$hashed_password)) {
            $hash_password=password_hash($newPAssword,PASSWORD_DEFAULT);
            $statement=$this->con->prepare("update users set password=? where email=?");
            $statement->bind_param("ss",$hash_password,$email);

            if($statement->execute())return PASSWORD_CHANGED;

            return  PASSWORD_NOT_CHANGED;

        }
        else return PASSWORDS_DO_NOT_MATCH;


    }


    public function getAllUsers(){
        $statement=$this->con->prepare("SELECT id,email,name,school FROM users;");
        $statement->execute();
        $statement->bind_result($id,$email,$name,$school);
        //wrap fetch in a loop as we do not have a single result
        $all_users=array();
        while ($statement->fetch()){
            $user=array();
            $user['id']=$id;
            $user['email']=$email;
            $user['name']=$name;
            $user['school']=$school;
            array_push($all_users,$user);

    }
    return $all_users;}


    public function deleteUser($id){
      $statement= $this->con->prepare("delete from users where id=?");
      $statement->bind_param("i",$id);

      if($statement->execute())return true;

      return false;
    }


    private function emailExists($email){
            $stmt = $this->con->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            return $stmt->num_rows > 0;
        }}
 ?>
