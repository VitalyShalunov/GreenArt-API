<?php
use \Firebase\JWT\JWT;
    class User {

        private $data;

        function __construct($data) {
            $this->data = $data;
        }

        //Аунтефикация
        public function auth(){
            global $db;
            if($this->data["method"] === "POST"){
                $email = md5($this->data["login"]);
                $password = $this->data["password"];
                mb_internal_encoding("UTF-8");
    
                $sql = "SELECT `Id`, `Login`, `Password`, `Hash` FROM `Users` WHERE `Login` = :login";
                        
                $data = array("login" => $email);
    
                $user_info = $db->doRequest($sql, $data);
                // print_r($user_info);
                if(!$user_info) throw new ErrorAPI("Unauthorized", 401);
    
                $decrypted_password = Engine::decrypt($user_info[0]["Password"], Engine::$ck, $user_info[0]["Hash"]);
    
                if($decrypted_password == $password) {
                    $token = $this->createToken($user_info[0]["Id"]);

                    return json_encode($token);
                }
                else throw new ErrorAPI("Unauthorized", 401);
            }
            else{
                return 'Wrong request type';
            }
        }

        private function createToken($uId) {
            $token = array(
                "userId" => $uId,
                "exp" => time() + 3600 * 24,
                "createTime" => time(),
                "type" => "access"
            );
            $jwt = JWT::encode($token, Engine::$ck);

            return $jwt;
        }   

        public function reg() {
            global $db;
            if($this->data["method"] === "POST"){
                $email = $this->data["login"];
                $password = $this->data["password"];
    
                $sql = "INSERT INTO `Users`(`Email`, `Login`, `Password`, `Hash`) VALUES (:email, :login, :password, :salt)";
    
                $email = md5($email);
                $salt = Engine::generate_code(8);
                $password = Engine::encrypt($password, Engine::$ck, $salt);
    
                $data = array(
                    "email" => $email,
                    "login" => $email,
                    "password" => $password,
                    "salt" => $salt
                );
                $db->doRequest($sql, $data);
    
                //print_r($data);
    
                return array('json'=> true, 'status'=>201);
            }
            else{
                return array('json' => 'Wrong request type', 'status' => 405);
            }
        }

        public function logout() {
            global $db;
            $sql = "UPDATE `Users` SET `RefreshToken` = :rt WHERE `Id` = :id";
            $data = array("rt" => "", "id" => $this->data["userId"]);
            $db->doRequest($sql, $data);
        }

    }

?>