<?php
class Employees
{
    private $data;
    private $whitelist = array('jpg','png','bmp','gif','wmf','jpeg','tif','tiff');
    private $id;
    private $path;
    private $namePhoto;
    function __construct($data) {
        $this->data = $data;
    }

    public function changeName()
    {
        global $db;
        if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
        if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
        if (!isset($this->data['id'])||
            !isset($this->data['name']))throw new ErrorAPI("Data wasn't sent", 400);
        
        $arrdata = array(
            'id' => $this->data['id'],
            'name' => $this->data['name']
        );
        $sql = "UPDATE `employees` SET name=:name WHERE id=:id";
        $db->doRequest($sql, $arrdata);
        return "Name was changed";
    }

    public function changeMiddleName()
    {
        global $db;
        if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
        if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
        if (!isset($this->data['id'])||
            !isset($this->data['middleName']))throw new ErrorAPI("Data wasn't sent", 400);
        
        $arrdata = array(
            'id' => $this->data['id'],
            'middleName' => $this->data['middleName']
        );
        $sql = "UPDATE `employees` SET middleName=:middleName WHERE id=:id";
        $db->doRequest($sql, $arrdata);
        return "Middle name was changed";
    }

    public function changeSurname()
    {
        global $db;
        if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
        if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
        if (!isset($this->data['id'])||
            !isset($this->data['surname']))throw new ErrorAPI("Data wasn't sent", 400);
        
        $arrdata = array(
            'id' => $this->data['id'],
            'surname' => $this->data['surname']
        );
        $sql = "UPDATE `employees` SET surname=:surname WHERE id=:id";
        $db->doRequest($sql, $arrdata);
        return "Surname was changed";
    }

    public function changePosition()
    {
        global $db;
        if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
        if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
        if (!isset($this->data['id'])||!isset($this->data['post']))throw new ErrorAPI("Data wasn't sent", 400);
        $arrdata = array(
            'id' => $this->data['id'],
            'post' => $this->data['post']
        );
        $sql = "UPDATE `employees` SET post=:post WHERE id=:id";
        $db->doRequest($sql, $arrdata);
        return "Position was changed";
    }

    public function changePhoto()
    {
        global $db;
        if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
        if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
        if (!isset($this->data["files"])) throw new ErrorAPI("Files were not sent", 400);
        if (!isset($this->data['files'][0]['filename']) || 
            !isset($this->data['files'][0]['tmp'])||
            !isset($this->data['id'])) throw new ErrorAPI("Data wasn't sent", 400);

        $path = 'images/employees';
        if(!is_dir($path)){
            mkdir($path,0777);
        }

        $tmp = explode(".", $this->data['files'][0]['filename']);
        $ext =  array_pop($tmp);
        if (!in_array($ext, $this->whitelist)) throw new ErrorAPI("The extension is not supported", 400);

        $this->id = $this->data['id'];
        $sql = "SELECT namePhoto FROM `employees`WHERE id=:id";
        $data = array(
            "id" => $this->data["id"]
        );
        $this->namePhoto = $db->doRequest($sql, $data)[0]["namePhoto"];

        $this->removePhotoOfEmployee();

        $fileName = time().".".$ext;
        $upload = 'images/employees/'.$fileName;
        move_uploaded_file($this->data['files'][0]['tmp'], $upload);
       
        $arrdata = array(
            'id' => $this->data['id'],
            'namePhoto' => $fileName
        );
        $sql = "UPDATE `employees` SET namePhoto=:namePhoto WHERE id=:id";
        $db->doRequest($sql, $arrdata);
        return "Photo was changed";
    }

    public function deleteEmployee()
    {
        global $db;
        if($this->data["method"] != "DELETE") throw new ErrorAPI("Method not allowed", 405);
        if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
        if (!isset($this->data["get"]["id"]))throw new ErrorAPI("Data wasn't sent", 400);

        $sql = "SELECT namePhoto FROM `employees`WHERE id=:id";
        $data = array(
            "id" => $this->data["get"]["id"]
        );
        $this->namePhoto = $db->doRequest($sql, $data)[0]["namePhoto"];

        $this->removePhotoOfEmployee();

        $arrdata = array(
            'id' => $this->data["get"]["id"]
        );
        $sql = 'DELETE FROM `employees` WHERE `id` = :id';
        $db->doRequest($sql, $arrdata);
        return "Employee was removed";
    }

    public function createEmployee()
    {
        global $db;
        if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
        if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
        $path = 'images/employees';
        if(!is_dir($path)){
            mkdir($path,0777);
                 }
        $arrdata = array(
            'name' => "name",
            'middleName' => "middleName",
            'surname' => 'surname',
            'post' => "post",
            'namePhoto' => "namePhoto",
            'characteristic' => "characteristic"
        );
        $sql = 'INSERT INTO employees(name,middleName,surname,post,namePhoto,characteristic) 
            VALUES (:name,:middleName,:surname,:post,:namePhoto,:characteristic)';
        $db->doRequest($sql, $arrdata);
        return "Employee was uploaded";
    }

    public function getEmployees()
    {
        global $db;
        if($this->data["method"] != "GET") throw new ErrorAPI("Method not allowed", 405);

        $sql = "SELECT `e`.*, COUNT(`i`.`id`) AS `workCount` FROM `employees` AS `e`
        LEFT JOIN `images` AS `i` ON `i`.`idEmployee` = `e`.`id` GROUP BY `e`.`id`";
        $arrdata = NULL;
        $employees = $db->doRequest($sql,$arrdata);
        $path = 'images/employees/';
        foreach($employees as $key => $employee) {
            $employees[$key]["pathPhotoEmployee"]= $path.$employee['namePhoto'];
        }
        return json_encode($employees);
    }

    public function getEmployee()
    {
        global $db;
        if($this->data["method"] != "GET") throw new ErrorAPI("Method not allowed", 405);
        if (!isset($this->data['id']))throw new ErrorAPI("Data wasn't sent", 400);

        $sql = "SELECT * FROM `employees` WHERE id=:id";
        $arrdata = array(
            'id' => $this->data['id']
        );
        $answer = $db->doRequest($sql,$arrdata)[0];
        $result = $answer;
        $path = 'images/employees/';
        $result["pathPhotoEmployee"] = $path.$answer['namePhoto'];

        $sql = "SELECT COUNT(*) AS count FROM `images` WHERE idEmployee=:id";
        $arrdata = array(
            'id' => $this->data['id']
        );
        $answer = $db->doRequest($sql,$arrdata);
        $result["countWorks"] = $answer[0]["count"];

        $sql = "SELECT id,nameImage FROM `images` WHERE idEmployee=:id";
        $arrdata = array(
            'id' => $this->data['id']
        );
        $result["works"] = $db->doRequest($sql,$arrdata);
        return json_encode($result);
    }

    public function changeCharacteristic()
    {
        global $db;
        if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
        if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
        if (!isset($this->data['id'])||
            !isset($this->data['characteristic']))throw new ErrorAPI("Data wasn't sent", 400);
        $arrdata = array(
            'id' => $this->data['id'],
            'characteristic' => $this->data['characteristic']
        );
        $sql = "UPDATE `employees` SET characteristic=:characteristic WHERE id=:id";
        $db->doRequest($sql, $arrdata);
        return "Characteristic was changed";
    }

    private function removePhotoOfEmployee()
    {
        if ($this->namePhoto != "namePhoto")
        {
            unlink('images/employees/'.$this->namePhoto);
        }    
    }

}

?>