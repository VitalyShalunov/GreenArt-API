<?php
class Events{
private $data;
private $id;
private $path;
private $namePhoto;
private $whitelist = array('jpg','png','bmp','gif','wmf','jpeg','tif','tiff');

function __construct($data) {
    $this->data = $data;
}

public function changeName()
{
    global $db;
    if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
    if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
    if (!isset($this->data['id'])||!isset($this->data['nameEvent']))throw new ErrorAPI("Data wasn't sent", 400);
    
    $arrdata = array(
        'id' => $this->data['id'],
        'nameEvent' => $this->data['nameEvent']
    );
    $sql = "UPDATE `events` SET nameEvent=:nameEvent WHERE id=:id";
    $db->doRequest($sql, $arrdata);
    return "Name was changed";
}

public function changeText()
{
    global $db;
    if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
    if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
    if (!isset($this->data['id'])||!isset($this->data['textEvent']))throw new ErrorAPI("Data wasn't sent", 400);
    $arrdata = array(
        'id' => $this->data['id'],
        'textEvent' => $this->data['textEvent']
    );
    $sql = "UPDATE `events` SET textEvent=:textEvent WHERE id=:id";
    $db->doRequest($sql, $arrdata);
    return "Text Event was changed";
}

public function changeAdditionalText()
{
    global $db;
    if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
    if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
    if (!isset($this->data['id'])||!isset($this->data['additionalText']))throw new ErrorAPI("Data wasn't sent", 400);
    $arrdata = array(
        'id' => $this->data['id'],
        'additionalText' => $this->data['additionalText']
    );
    $sql = "UPDATE `events` SET additionalText=:additionalText WHERE id=:id";
    $db->doRequest($sql, $arrdata);
    return "Additional text was changed";
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

        $path = 'images/events';
        if(!is_dir($path)){
            mkdir($path,0777);
         }

        $tmp = explode(".", $this->data['files'][0]['filename']);
        $ext =  array_pop($tmp);
        if (!in_array($ext, $this->whitelist)) throw new ErrorAPI("The extension is not supported", 400);
        
        $this->id = $this->data['id'];

        $sql = "SELECT nameImage FROM `events`WHERE id=:id";
        $data = array(
            "id" => $this->data["id"]
        );
        $this->namePhoto = $db->doRequest($sql, $data)[0]["nameImage"];

        $this->removePhotoOfEvent();

        $upload = 'images/events/'.$this->data['files'][0]['filename'];
        move_uploaded_file($this->data['files'][0]['tmp'], $upload);
       
        $arrdata = array(
            'id' => $this->data['id'],
            'nameImage' => $this->data['files'][0]['filename']
        );
        $sql = "UPDATE `events` SET nameImage=:nameImage WHERE id=:id";
        $db->doRequest($sql, $arrdata);
        return "Image was changed";
    }

    public function deleteEvent()
    {
        global $db;
        
        if($this->data["method"] != "DELETE") throw new ErrorAPI("Method not allowed", 405);
        if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
        if (!isset($this->data["get"]["id"]))throw new ErrorAPI("Data wasn't sent", 400);

        $sql = "SELECT nameImage FROM `events`WHERE id=:id";
        $data = array(
            "id" => $this->data["get"]["id"]
        );
        $this->namePhoto = $db->doRequest($sql, $data)[0]["nameImage"];

        $this->removePhotoOfEvent();

        $arrdata = array(
            'id' => $this->data["get"]["id"]
        );
        $sql = 'DELETE FROM `events` WHERE `id` = :id';
        $db->doRequest($sql, $arrdata);
        return "Event was removed";
    }

    public function createEvent()
    {
        global $db;
        if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
        if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
        if(!isset($this->data['idPortal'])) throw new ErrorAPI("Data wasn't sent", 400);

        $path = 'images/events';
        if(!is_dir($path)){
            mkdir($path,0777);
                 }

        $arrdata = array(
            'nameEvent' => "nameEvent",
            'textEvent' => "textEvent",
            'additionalText' => "additionalText",
            'nameImage' => "nameImage",
            "idPortal" => $this->data['idPortal']
        );
        $sql = 'INSERT INTO events(nameEvent,textEvent,additionalText,nameImage,idPortal) VALUES (:nameEvent, :textEvent, :additionalText, :nameImage, :idPortal)';
        $db->doRequest($sql, $arrdata);
        return "Event was uploaded";
    }

    public function getEvents()
    {
        global $db;
        if($this->data["method"] != "GET") throw new ErrorAPI("Method not allowed", 405);
        if(!isset($this->data['idPortal'])) throw new ErrorAPI("Data wasn't sent", 400);
        $sql = "SELECT * FROM `events` WHERE `idPortal` = :idPortal";
        $arrdata = array(
            "idPortal" => $this->data['idPortal']
        );
        $events = $db->doRequest($sql,$arrdata);
        $path = 'images/events/';
        foreach($events as $key => $event) {
            $events[$key]["pathPhotoEvent"]= $path.$event['nameImage'];
        }
        return json_encode($events);
    }

    private function removePhotoOfEvent()
    {
        if ($this->namePhoto != "nameImage")
        {
            unlink('images/events/'.$this->namePhoto);
        }  
    }
}
?>