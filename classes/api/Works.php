<?php
class Works
{
    private $data;
    private $whitelist = array('jpg','png','bmp','gif','wmf','jpeg','tif','tiff');
    private $id;
    private $path;
    private $namePhoto;
    function __construct($data) {
        $this->data = $data;
    }

    public function createWork()
    {
        global $db;
        if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
        if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
        if (!isset($this->data['id'])) throw new ErrorAPI("Data wasn't sent", 400);

        $sql = "SELECT name FROM `courses` WHERE id=:id";
        $data = array(
            "id" => $this->data["id"]
        );
        $course = $db->doRequest($sql, $data)[0];
        $path = 'images/courses/'.$course['name'];
        if(!is_dir($path.'/works')){
            mkdir($path.'/works',0777);
                 }
        $arrdata = array(
            'idCourse' => $this->data['id'],
            'namePhoto' => 'namePhoto',
            'description' =>  'description',
            'nameLearner' =>  'nameLearner'
        );
        $sql = 'INSERT INTO works(idCourse,namePhoto,description, nameLearner) 
        VALUES (:idCourse,:namePhoto,:description, :nameLearner)';
        $db->doRequest($sql, $arrdata);
        return "Work was created";
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
        $tmp = explode(".", $this->data['files'][0]['filename']);
        $ext =  array_pop($tmp);
        if (!in_array($ext, $this->whitelist)) throw new ErrorAPI("The extension is not supported", 400);
        
        $this->id = $this->data['id'];
        $sql = "SELECT `c`.`name`, `w`.`namePhoto` FROM `courses` AS `c` INNER JOIN `works` AS `w` ON `c`.`id` = `w`.`idCourse` WHERE `w`.`idWork`=:id";
        $data = array(
            "id" => $this->data["id"]
        );
        $answer = $db->doRequest($sql, $data)[0];
        $course = $answer["name"];
        $this->namePhoto =$answer["namePhoto"];
        $this->path = 'images/courses/'.$course.'/works/';
        
        $this->removePhotoOfWork();

        $upload = $this->path.$this->data['files'][0]['filename'];
        move_uploaded_file($this->data['files'][0]['tmp'], $upload);
       
        $arrdata = array(
            'idWork' => $this->data['id'],
            'namePhoto' => $this->data['files'][0]['filename']
        );
        $sql = "UPDATE `works` SET namePhoto=:namePhoto WHERE idWork=:idWork";
        $db->doRequest($sql, $arrdata);
        return "Photo was changed";
    }

    public function changeIdCourse()
    {
    global $db;
    if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
    if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
    if (!isset($this->data['id'])||
        !isset($this->data['idCourse']))throw new ErrorAPI("Data wasn't sent", 400);
    $arrdata = array(
        'idWork' => $this->data['id'],
        'idCourse' => $this->data['idCourse']
    );
    $sql = "UPDATE `works` SET idCourse=:idCourse WHERE idWork=:idWork";
    $db->doRequest($sql, $arrdata);
    return "IdCourse was changed";
    }

    public function changeDescription()
    {
    global $db;
    if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
    if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
    if (!isset($this->data['id'])||
        !isset($this->data['description']))throw new ErrorAPI("Data wasn't sent", 400);
    $arrdata = array(
        'idWork' => $this->data['id'],
        'description' => $this->data['description']
    );
    $sql = "UPDATE `works` SET description=:description WHERE idWork=:idWork";
    $db->doRequest($sql, $arrdata);
    return "Description was changed";
    }

    public function changeNameLearner()
    {
    global $db;
    if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
    if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
    if (!isset($this->data['id'])||
        !isset($this->data['nameLearner']))throw new ErrorAPI("Data wasn't sent", 400);
    $arrdata = array(
        'idWork' => $this->data['id'],
        'nameLearner' => $this->data['nameLearner']
    );
    $sql = "UPDATE `works` SET nameLearner=:nameLearner WHERE idWork=:idWork";
    $db->doRequest($sql, $arrdata);
    return "Name of learner was changed";
    }

    public function deleteWork()
    {
        global $db;
        if($this->data["method"] != "DELETE") throw new ErrorAPI("Method not allowed", 405);
        if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
        if (!isset($this->data["get"]["id"]))throw new ErrorAPI("Data wasn't sent", 400);

        $this->id = $this->data["get"]["id"];

        $sql = "SELECT `c`.`name`, `w`.`namePhoto` FROM `courses` AS `c` INNER JOIN `works` AS `w` ON `c`.`id` = `w`.`idCourse` WHERE `w`.`idWork`=:id";
        $arrdata = array(
            'id' => $this->data["get"]["id"]
        );
        $answer = $db->doRequest($sql, $arrdata)[0];
        $course = $answer["name"];
        $this->namePhoto =$answer["namePhoto"];
        $this->path = 'images/courses/'.$course.'/works/';

        $this->removePhotoOfWork();

        $arrdata = array(
            'id' => $this->data["get"]["id"]
        );
        $sql = 'DELETE FROM `works` WHERE `idWork` = :id';
        $db->doRequest($sql, $arrdata);
        return "Work was removed";
    }

    private function removePhotoOfWork()
    {
        if ($this->namePhoto != "namePhoto")
        {
            unlink($this->path.$this->namePhoto);
        }
    }
}