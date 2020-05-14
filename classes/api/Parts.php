<?php
require_once 'Sections.php';
require_once './classes/Engine.php';
class Parts
{
    private $data;
    private $whitelist = array('jpg','png','bmp','gif','wmf','jpeg','tif','tiff');
    private $id;
    private $path;
    private $namePhoto;
    function __construct($data) {
        $this->data = $data;
    }

    public function createPart()
    {
        global $db;
        if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
        if (!isset($this->data['id'])) throw new ErrorAPI("Data wasn't sent", 400);

        $sql = "SELECT name FROM `courses` WHERE id=:id";
        $data = array(
            "id" => $this->data["id"]
        );
        $answer = $db->doRequest($sql, $data);
        $path = 'images/courses/'.$answer[0]["name"];
        if(is_dir($path)){
           mkdir('images/courses/'.$answer[0]["name"].'/namePart',0777);
                }
            $arrdata = array(
            'idCourse' => $this->data['id'],
            'namePart' => 'namePart',
            'namePhoto' =>  'namePhoto'
        );
        $sql = 'INSERT INTO parts(namePart,idCourse,namePhoto) 
        VALUES (:namePart, :idCourse,:namePhoto)';
        $db->doRequest($sql, $arrdata);
        return "Part was created";
    }

    public function changeName() {
        global $db;
        if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
        if (!isset($this->data['id'])||
            !isset($this->data['namePart']))throw new ErrorAPI("Data wasn't sent", 400);

        $sql = "SELECT `c`.`name`, `p`.`namePart` FROM `courses` AS `c` INNER JOIN `parts` AS `p` ON `c`.`id` = `p`.`idCourse` WHERE `p`.`idPart`=:id";
        $data = array(
            "id" => $this->data["id"]
        );
        $answer = $db->doRequest($sql, $data);
        $course = $answer[0]["name"];
        $part = $answer[0]["namePart"];
        $path = 'images/courses/'.$course.'/'.$part;
        
        if(!is_dir($path)){
            mkdir('images/courses/'.$course.'/'.$this->data['namePart'],0777);
            }
            else {
                rename($path, 'images/courses/'.$course.'/'.$this->data['namePart']);
            }

        $arrdata = array(
            'idPart' => $this->data['id'],
            "namePart" => $this->data['namePart']
        );
        $sql = "UPDATE `parts` SET namePart=:namePart WHERE idPart=:idPart";
        $db->doRequest($sql, $arrdata);
        return "NamePart was changed";
    }

    public function changeIdCourse()
    {
    global $db;
    if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
    if (!isset($this->data['id'])||
        !isset($this->data['idCourse']))throw new ErrorAPI("Data wasn't sent", 400);
    $arrdata = array(
        'idPart' => $this->data['id'],
        'idCourse' => $this->data['idCourse']
    );
    $sql = "UPDATE `parts` SET idCourse=:idCourse WHERE idPart=:idPart";
    $db->doRequest($sql, $arrdata);
    return "IdCourse was changed";
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
        $sql = "SELECT `c`.`name`, `p`.`namePart`,`p`.`namePhoto` FROM `courses` AS `c` INNER JOIN `parts` AS `p` ON `c`.`id` = `p`.`idCourse` WHERE `p`.`idPart`=:id";
        $data = array(
            "id" => $this->data["id"]
        );
        $answer = $db->doRequest($sql, $data);
        $course = $answer[0]["name"];
        $part = $answer[0]["namePart"];
        $this->path = 'images/courses/'.$course.'/'.$part;
        $this->namePhoto =$answer[0]["namePhoto"];
        $this->removePhotoOfPart();

        $upload = $this->path.'/'.$this->data['files'][0]['filename'];
        move_uploaded_file($this->data['files'][0]['tmp'], $upload);
       
        $arrdata = array(
            'idPart' => $this->data['id'],
            'namePhoto' => $this->data['files'][0]['filename']
        );
        $sql = "UPDATE `parts` SET namePhoto=:namePhoto WHERE idPart=:idPart";
        $db->doRequest($sql, $arrdata);
        return "Photo was changed";
    }

    public function deletePart()
    {
        global $db;
        if($this->data["method"] != "DELETE") throw new ErrorAPI("Method not allowed", 405);
        if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
        if (!isset($this->data["get"]["id"]))throw new ErrorAPI("Data wasn't sent", 400);

        $this->id = $this->data["get"]["id"];

        $sql = "SELECT `c`.`name`, `p`.`namePart`,`p`.`namePhoto` FROM `courses` AS `c` INNER JOIN `parts` AS `p` ON `c`.`id` = `p`.`idCourse` WHERE `p`.`idPart`=:id";
        $arrdata = array(
            'id' => $this->data["get"]["id"]
        );
        $answer = $db->doRequest($sql, $arrdata);
        $course = $answer[0]["name"];
        $part = $answer[0]["namePart"];
        
        $this->path = 'images/courses/'.$course.'/'.$part;

        if($this->data["get"]["action"] == "deletePart")
        {
            $this->namePhoto = $answer[0]["namePhoto"];
            $this->removePhotoOfPart();
        }
        $sql = 'SELECT * FROM `sections` WHERE `idPart` = :id';
        $answer = $db->doRequest($sql, $arrdata);
        for ($i=0; $i < count($answer); $i++) { 
        
        $data = array(
            "files"=> "",
            "method"=> "DELETE",
            "get"=> array( 
            "action"=> "deletePart",
            "id"=> $answer[$i]["idSection"]
            )
        );
        $deleteSection = new Sections($data);
        $deleteSection->deleteSection();
        }

        $sql = 'DELETE FROM `parts` WHERE `idPart` = :id';
        $db->doRequest($sql, $arrdata);

        if($this->data["get"]["action"] == "deletePart")
        {
        Engine::removeDirectory($this->path);
        }
        return "Part was removed";
    }

    private function removePhotoOfPart()
    {
        if ($this->namePhoto != "namePhoto")
        {
            unlink($this->path.'/'.$this->namePhoto);
        }
    }
}
?>