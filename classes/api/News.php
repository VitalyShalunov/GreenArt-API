<?php
class News{
private $data;
private $id;
private $whitelist = array('jpg','png','bmp','gif','wmf','jpeg','tif','tiff');

function __construct($data) {
    $this->data = $data;
}

    public function createNews()
    {
        global $db;
        if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
        if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
        if(!isset($this->data['title'])||
           !isset($this->data['text'])||
           !isset($this->data['shortText'])) throw new ErrorAPI("Data wasn't sent", 400);

        $path = 'images/news';
        if(!is_dir($path)){
            mkdir($path, 0777);
        }

        $createDate = date("d.m.Y");

        $arrdata = array(
            'title' => $this->data['title'],
            'text' => $this->data['text'],
            'createTime' => $createDate,
            'shortText' => $this->data['shortText'],
            'namePhoto' => "namePhoto"
        );
        $sql = 'INSERT INTO news(title, text, createTime, shortText, namePhoto) VALUES (:title, :text, :createTime, :shortText, :namePhoto)';
        $db->doRequest($sql, $arrdata);
        return "News was uploaded";
    }

    public function getNews()
    {
        global $db;
        if($this->data["method"] != "GET") throw new ErrorAPI("Method not allowed", 405);
        $sql = "SELECT * FROM `news`";
        $arrdata = NULL;
        $news = $db->doRequest($sql,$arrdata);
        $path = 'images/news/';
        foreach($news as $key => $item) {
            $news[$key]["pathPhotoNews"]= $path.$item['namePhoto'];
            unset($news[$key]['text'], $news[$key]['namePhoto']);
        }
        return json_encode($news);
    }

    public function getOneNews()
    {
        global $db;
        if($this->data["method"] != "GET") throw new ErrorAPI("Method not allowed", 405);
        if (!isset($this->data['id']))throw new ErrorAPI("Data wasn't sent", 400);
        $sql = "SELECT * FROM `news` WHERE id=:id";
        $arrdata = [
            'id' => $this->data['id']
        ];
        $news = $db->doRequest($sql,$arrdata);
        $path = 'images/news/';
        foreach($news as $key => $item) {
            $news[$key]["pathPhotoNews"]= $path.$item['namePhoto'];
            unset($news[$key]['shortText'], $news[$key]['namePhoto']);
        }
        return json_encode($news);
    }

    public function deleteNews()
    {
        global $db;
        
        if($this->data["method"] != "DELETE") throw new ErrorAPI("Method not allowed", 405);
        if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
        if (!isset($this->data["get"]["id"]))throw new ErrorAPI("Data wasn't sent", 400);

        $sql = "SELECT namePhoto FROM `news`WHERE id=:id";
        $data = array(
            "id" => $this->data["get"]["id"]
        );
        $this->namePhoto = $db->doRequest($sql, $data)[0]["namePhoto"];

        $this->removePhotoOfNews();

        $arrdata = array(
            'id' => $this->data["get"]["id"]
        );
        $sql = 'DELETE FROM `news` WHERE `id` = :id';
        $db->doRequest($sql, $arrdata);
        return "News was removed";
    }

    public function changeTitle()
    {
    global $db;
    if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
    if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
    if (!isset($this->data['id'])||
        !isset($this->data['title']))throw new ErrorAPI("Data wasn't sent", 400);
    
    $arrdata = array(
        'id' => $this->data['id'],
        'title' => $this->data['title']
    );
    $sql = "UPDATE `news` SET title=:title WHERE id=:id";
    $db->doRequest($sql, $arrdata);
    return "Title was changed";
    }

    public function changeText()
    {
    global $db;
    if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
    if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
    if (!isset($this->data['id'])||
        !isset($this->data['text']))throw new ErrorAPI("Data wasn't sent", 400);
    
    $arrdata = array(
        'id' => $this->data['id'],
        'text' => $this->data['text']
    );
    $sql = "UPDATE `news` SET text=:text WHERE id=:id";
    $db->doRequest($sql, $arrdata);
    return "Text was changed";
    }

    public function changeShortText()
    {
    global $db;
    if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
    if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
    if (!isset($this->data['id'])||
        !isset($this->data['shortText']))throw new ErrorAPI("Data wasn't sent", 400);
    
    $arrdata = array(
        'id' => $this->data['id'],
        'shortText' => $this->data['shortText']
    );
    $sql = "UPDATE `news` SET shortText=:shortText WHERE id=:id";
    $db->doRequest($sql, $arrdata);
    return "shortText was changed";
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

        $path = 'images/news';
        if(!is_dir($path)){
            mkdir($path,0777);
         }

        $tmp = explode(".", $this->data['files'][0]['filename']);
        $ext =  array_pop($tmp);
        if (!in_array($ext, $this->whitelist)) throw new ErrorAPI("The extension is not supported", 400);
        
        $this->id = $this->data['id'];

        $sql = "SELECT namePhoto FROM `news`WHERE id=:id";
        $data = array(
            "id" => $this->data["id"]
        );
        $this->namePhoto = $db->doRequest($sql, $data)[0]["namePhoto"];

        $this->removePhotoOfNews();

        $upload = 'images/news/'.$this->data['files'][0]['filename'];
        move_uploaded_file($this->data['files'][0]['tmp'], $upload);
       
        $arrdata = array(
            'id' => $this->data['id'],
            'namePhoto' => $this->data['files'][0]['filename']
        );
        $sql = "UPDATE `news` SET namePhoto=:namePhoto WHERE id=:id";
        $db->doRequest($sql, $arrdata);
        return "Photo was changed";
    }

    private function removePhotoOfNews()
    {
        if ($this->namePhoto != "namePhoto")
        {
            unlink('images/news/'.$this->namePhoto);
        }  
    }
}
?>