<?php
class Sections
{
    private $data;
    private $whitelist = array('jpg','png','bmp','gif','wmf','jpeg','tif','tiff');
    private $id;
    private $path;
    private $namePhoto;
    function __construct($data) {
        $this->data = $data;
    }

    public function createSection()
    {
        global $db;
        if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
        if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
        if (!isset($this->data['id'])) throw new ErrorAPI("Data wasn't sent", 400);

        $sql = "SELECT `c`.`name`, `p`.`namePart` FROM `courses` AS `c` INNER JOIN `parts` AS `p` ON `c`.`id` = `p`.`idCourse` WHERE `p`.`idPart`=:id";
        $data = array(
            "id" => $this->data["id"]
        );
        $answer = $db->doRequest($sql, $data);
        $path = 'images/courses/'.$answer[0]["name"].'/'.$answer[0]["namePart"];
        if(is_dir($path)){
           mkdir($path.'/nameSection',0777);
                }
        $arrdata = array(
            'idPart' => $this->data['id'],
            'discount' => 0,
            'nameSection' =>  'nameSection',
            'namePhoto' =>  'namePhoto'
        );
        $sql = 'INSERT INTO sections(idPart,discount,nameSection,namePhoto) 
        VALUES (:idPart,:discount,:nameSection,:namePhoto)';
        $db->doRequest($sql, $arrdata);
        return "Section was created";
    }

    public function changeIdPart()
    {
    global $db;
    if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
    if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
    if (!isset($this->data['id'])||
        !isset($this->data['idPart']))throw new ErrorAPI("Data wasn't sent", 400);
    $arrdata = array(
        'idSection' => $this->data['id'],
        'idPart' => $this->data['idPart']
    );
    $sql = "UPDATE `sections` SET idPart=:idPart WHERE idSection=:idSection";
    $db->doRequest($sql, $arrdata);
    return "IdPart was changed";
    }

    public function changeName() {
        global $db;
        if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
        if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
        if (!isset($this->data['id'])||
            !isset($this->data['name']))throw new ErrorAPI("Data wasn't sent", 400);

        $sql = "SELECT `c`.`name`, `p`.`namePart`, `s`.`nameSection` FROM `courses` AS `c` INNER JOIN `parts` AS `p` ON `c`.`id` = `p`.`idCourse` 
        INNER JOIN `sections` AS `s` ON `p`.`idPart` = `s`.`idPart` 
        WHERE `s`.`idSection`=:id";
        $data = array(
            "id" => $this->data["id"]
        );
        $answer = $db->doRequest($sql, $data);
        $course = $answer[0]["name"];
        $part = $answer[0]["namePart"];
        $section = $answer[0]["nameSection"];
        $path = 'images/courses/'.$course.'/'.$part.'/'.$section;
        
        if(!is_dir($path)){
            mkdir('images/courses/'.$course.'/'.$part.'/'.$this->data['name'],0777);
            }
            else {
                rename($path, 'images/courses/'.$course.'/'.$part.'/'.$this->data['name']);
            }

        $arrdata = array(
            'idSection' => $this->data['id'],
            'name' => $this->data['name']
        );
        $sql = "UPDATE `sections` SET nameSection=:name WHERE idSection=:idSection";
        $db->doRequest($sql, $arrdata);
        return "nameSection was changed";
    }

    public function changeDiscount()
    {
    global $db;
    if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
    if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
    if (!isset($this->data['id'])||
        !isset($this->data['discount']))throw new ErrorAPI("Data wasn't sent", 400);
    $arrdata = array(
        'idSection' => $this->data['id'],
        'discount' => $this->data['discount']
    );
    $sql = "UPDATE `sections` SET discount=:discount WHERE idSection=:idSection";
    $db->doRequest($sql, $arrdata);
    return "Discount was changed";
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
        
        $sql = "SELECT `c`.`name`, `p`.`namePart`, `s`.`nameSection`, `s`.`namePhoto` FROM `courses` AS `c` INNER JOIN `parts` AS `p` ON `c`.`id` = `p`.`idCourse` 
        INNER JOIN `sections` AS `s` ON `p`.`idPart` = `s`.`idPart` 
        WHERE `s`.`idSection`=:id";
        $data = array(
            "id" => $this->data["id"]
        );
        $answer = $db->doRequest($sql, $data);
        $course = $answer[0]["name"];
        $part = $answer[0]["namePart"];
        $section = $answer[0]["nameSection"];
        $this->path = 'images/courses/'.$course.'/'.$part.'/'.$section;
        $this->namePhoto =$answer[0]["namePhoto"];
        $this->removePhotoOfSection();

        $upload = $this->path.'/'.$this->data['files'][0]['filename'];
        move_uploaded_file($this->data['files'][0]['tmp'], $upload);
       
        $arrdata = array(
            'idSection' => $this->data['id'],
            'namePhoto' => $this->data['files'][0]['filename']
        );
        $sql = "UPDATE `sections` SET namePhoto=:namePhoto WHERE idSection=:idSection";
        $db->doRequest($sql, $arrdata);
        return "Photo was changed";
    }

    public function deleteSection()
    {
        global $db;

        if($this->data["method"] != "DELETE") throw new ErrorAPI("Method not allowed", 405);
        if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
        if (!isset($this->data["get"]["id"]))throw new ErrorAPI("Data wasn't sent", 400);

        $this->id = $this->data["get"]["id"];
        $sql = "SELECT `c`.`name`, `p`.`namePart`, `s`.`nameSection`, `s`.`namePhoto` FROM `courses` AS `c` INNER JOIN `parts` AS `p` ON `c`.`id` = `p`.`idCourse` 
        INNER JOIN `sections` AS `s` ON `p`.`idPart` = `s`.`idPart` 
        WHERE `s`.`idSection`=:id";

        $arrdata = array(
            'id' => $this->data["get"]["id"]
        );
        $answer = $db->doRequest($sql, $arrdata);
        $course = $answer[0]["name"];
        $part = $answer[0]["namePart"];
        $section = $answer[0]["nameSection"];
        $this->path = 'images/courses/'.$course.'/'.$part.'/'.$section;
        

        if($this->data["get"]["action"] == "deleteSection")
        {   $this->namePhoto =$answer[0]["namePhoto"];
            $this->removePhotoOfSection();
        }
        
        $arrdata = array(
            'id' => $this->data["get"]["id"]
        );
        $sql = 'DELETE FROM `sections` WHERE `idSection` = :id';
        $db->doRequest($sql, $arrdata);

        if($this->data["get"]["action"] == "deleteSection")
        {    
            if(is_dir($this->path))
            rmdir($this->path);
        }
        
        return "Section was removed";
    }

    private function removePhotoOfSection()
    {
        if ($this->namePhoto != "namePhoto")
        {
            unlink($this->path.'/'.$this->namePhoto);
        }
    }
}
?>