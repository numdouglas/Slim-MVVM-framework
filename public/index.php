<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require dirname(__DIR__,1) . '/vendor/autoload.php';
/*Our connection to SQLiteDatabase*/
require dirname(__DIR__,1) . '/includes/DbOperations.php';

//require dirname(__DIR__,1) . '/includes/DbConnect.php';


//Slim needs to know which part of the URL should be considered as the base URL
//of your App so that everything coming after that will be treated as a route.
$basePath = str_replace('/' . basename(__FILE__), '', $_SERVER['SCRIPT_NAME']);

$app = AppFactory::create();
$app = $app->setBasePath($basePath);


$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);


/*
 $app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
     $name = $args['name'];
     $response->getBody()->write("Hello, $name");

     $db=new DbConnect;

     if($db->connect() == 1){echo "Connection Successful";}

     return $response;
 });
*/

//endpoint create user
$app-> post('/createuser',function(Request $request,Response $response){

if(!hasEmptyParameters(array('email','password','name','school'),$request,$response)){

//suppossed to be getParsedBody as getQueryParams is for GET
  $request_data=$request->getQueryParams();

  $email=$request_data['email'];
  $password=$request_data['password'];
  $name=$request_data['name'];
  $school=$request_data['school'];

//inbuilt function to encrypt password
  $hashed_password=password_hash($password,PASSWORD_DEFAULT);
  $db=new DbOPerations;

  $result = $db->createUser($email,$hashed_password,$name,$school);


  if($result==USER_CREATED){
    $message=array();
    $message['error']=false;
    $message['message']='User created successfully';

    $response ->getBody()-> write(json_encode($message));

    return $response
            ->withHeader('Content-type','application/json')
            /*HTTP status code to know that resource is created*/
            ->withStatus(201);
  }

    elseif ($result==USER_FAILURE) {
      $message=array();
      $message['error']=true;
      $message['message']='Some error occurred';

      $response ->getBody()-> write(json_encode($message));

      return $response
              ->withHeader('Content-type','application/json')
              /*HTTP status code to know that resource is created*/
              ->withStatus(422);
      }

    elseif($result==USER_EXISTS){
            $message=array();
            $message['error']=true;
            $message['message']='User already exists';

        //As of psr7 one must call getBody before write
            $response ->getBody()-> write(json_encode($message));


            return $response
                    ->withHeader('Content-type','application/json')
                    /*HTTP status code to know that resource is created*/
                    ->withStatus(422);
    }
}
});



$app->post("/userlogin",function (Request $request,Response $response){
    if(!hasEmptyParameters(array('email','password'),$request,$response)){
        //suppossed to be getParsedBody as getQueryParams is for GET
        $request_data=$request->getQueryParams();

        $email=$request_data['email'];
        $password=$request_data['password'];

        $db=new DbOPerations();
        $result=$db->userLogin($email,$password);

        if($result==USER_AUTHENTICATED){
            $user=$db->getUserByEmail($email);
            $response_data=array();

            $response_data['error']=false;
            $response_data['message']='Login Successful';
            $response_data['user']=$user;

            $response->getBody()->write(json_encode($response_data));

            return $response
                ->withHeader('Content-type','application/json')
                ->withStatus(200);
        }


        elseif ($result==USER_NOT_FOUND){
            $user=$db->getUserByEmail($email);
            $response_data=array();

            $response_data['error']=true;
            $response_data['message']='User Does Not Exist';

            $response->getBody()->write(json_encode($response_data));

            return $response
                ->withHeader('Content-type','application/json')
                ->withStatus(200);
        }
        elseif ($result==USER_PASSWORDS_DO_NOT_MATCH){
            $user=$db->getUserByEmail($email);
            $response_data=array();

            $response_data['error']=true;
            $response_data['message']='Invalid Credentials';
//            $response_data['user']=$user;

            $response->getBody()->write(json_encode($response_data));

            return $response
                ->withHeader('Content-type','application/json')
                ->withStatus(200);
        }}

    return $response
        ->withHeader('Content-type','application/json')
        ->withStatus(422);
});



$app->get('/allusers',function (Request $request,Response $response){
    $db=new DbOPerations();

    $all_users=$db->getAllUsers();

    $response_data=array();

    $response_data['error']=false;
    $response_data['users']=$all_users;

    $response->getBody()->write(json_encode($response_data));

    return $response
        ->withHeader('content-type','application/json')
        ->withStatus(200);
});


$app->put("/updateuser/{id}",function (Request $request,Response $response,array $args){
    $id=$args['id'];

    if(!hasEmptyParameters(array('email','name','school','id'),$request,$response)){
        $request_data=$request->getQueryParams();

        $email=$request_data['email'];
        $name=$request_data['name'];
        $school=$request_data['school'];

        $db=new DbOPerations();

        if($db->updateUser($email,$name,$school,$id)){
            $response_data=array();
            $response_data['error']=false;
            $response_data['message']='user updated successfully';
            $user=$db->getUserByEmail($email);
            $response_data['user']=$user;

            $response->getBody()->write(json_encode($response_data));

            return $response
                ->withHeader('Content-type','application/json')
                ->withStatus(200);
        }

        else{
            $response_data=array();
            $response_data['error']=true;
            $response_data['message']='PLease try again later';
            $user=$db->getUserByEmail($email);
            $response_data['user']=$user;

            $response->getBody()->write(json_encode($response_data));

            return $response
                ->withHeader('Content-type','application/json')
                ->withStatus(200);
        }

    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
});

$app->put('/updatepassword/{id}',function (Request $request,Response $response){

    if(!hasEmptyParameters(array('currentpassword','newpassword','email'),$request,$response)){
        $request_data=$request->getQueryParams();

        $currentpassword=$request_data['currentpassword'];
        $newpassword=$request_data['newpassword'];
        $email=$request_data['email'];

        $db=new DbOPerations();

        $result=$db->updatePassword($currentpassword,$newpassword,$email);

        if($result==PASSWORD_CHANGED){
            $response_data=array();
            $response_data['error']=false;
            $response_data['message']='Password Successfully Changed';
            $response->getBody()->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }
        elseif ($result== PASSWORDS_DO_NOT_MATCH){
            $response_data=array();
            $response_data['error']=true;
            $response_data['message']='You have given the wrong password';
            $response->getBody()->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }
        elseif ($result== PASSWORD_NOT_CHANGED){
            $response_data=array();
            $response_data['error']=true;
            $response_data['message']='Some Error has Occurred';
            $response->getBody()->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);
        }

    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
});

$app->delete("/deleteuser/{id}",function (Request $request,Response $response, array $args){
   $id=$args['id'];

   $db=new DbOPerations();
   $response_data=array();

   if($db->deleteUser($id)){
       $response_data['error']=false;
       $response_data['message']='User has been deleted';
   }
   else{
       $response_data['error']=true;
       $response_data['message']='Please Try Again Later';
   }

   $response->getBody()->write(json_encode($response_data));
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
});



//valiadate that all the parameters are available
function hasEmptyParameters($required_params,$request,$response){
  $error=false;
  $error_params='';
  //$request_params=$_REQUEST;
    $request_params=$request->getQueryParams();

  foreach($required_params as $param){
    if(!isset($request_params[$param]) || strlen($request_params[$param])<=0){
      $error=true;
      $error_params .= $param . ", ";
    }
  }

  if($error){
    $error_detail=array();
    $error_detail['error']=true;
    $error_detail['message']='Required parameters'.substr($error_params,0,-2). ' are missing or empty.';
    $response->getBody()-> write(json_encode($error_detail));
  }
  return $error;
}



$app->run();
//?>
