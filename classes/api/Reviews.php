<?php
class Reviews{
private $data;
private $id;
private $whitelist = array('jpg','png','bmp','gif','wmf','jpeg','tif','tiff');

function __construct($data) {
    $this->data = $data;
}

    public function createReview()
    {
        global $db;
        if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
        if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
        if(!isset($this->data['name'])||
           !isset($this->data['surname'])||
           !isset($this->data['idPortal'])||
           !isset($this->data['review'])||
           !isset($this->data['addInformation'])) throw new ErrorAPI("Data wasn't sent", 400);

        $path = 'images/reviews';
        if(!is_dir($path)){
            mkdir($path,0777);
        }

        $arrdata = array(
            'name' => $this->data['name'],
            'surname' => $this->data['surname'],
            'review' => $this->data['review'],
            'addInformation' => $this->data['addInformation'],
            'idPortal' => $this->data['idPortal'],
            'namePhoto' => $this->data['namePhoto']
        );
        $sql = 'INSERT INTO `reviews`(`name`,`surname`,`review`,`addInformation`,`idPortal`,`namePhoto`) VALUES (:name,:surname,:review,:addInformation,:idPortal,:namePhoto)';
        $db->doRequest($sql, $arrdata);
        return "Review was uploaded";
    }

    public function getReviews()
    {
        global $db;
        if($this->data["method"] != "GET") throw new ErrorAPI("Method not allowed", 405);
        if(!isset($this->data['idPortal'])) throw new ErrorAPI("Data wasn't sent", 400);
        $sql = "SELECT * FROM `reviews` WHERE `idPortal` = :idPortal";
        $arrdata = array(
            "idPortal" => $this->data['idPortal']
        );
        $reviews = $db->doRequest($sql,$arrdata);
        $path = 'images/reviews/';
        foreach($reviews as $key => $review) {
            $reviews[$key]["pathPhotoReview"]= $path.$review['namePhoto'];
        }
        return json_encode($reviews);
    }

    public function deleteReview()
    {
        global $db;
        
        if($this->data["method"] != "DELETE") throw new ErrorAPI("Method not allowed", 405);
        if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
        if (!isset($this->data["get"]["id"]))throw new ErrorAPI("Data wasn't sent", 400);

        $sql = "SELECT namePhoto FROM `reviews` WHERE id=:id";
        $data = array(
            "id" => $this->data["get"]["id"]
        );
        $this->namePhoto = $db->doRequest($sql, $data)[0]["namePhoto"];

        $this->removePhotoOfReview();

        $arrdata = array(
            'id' => $this->data["get"]["id"]
        );
        $sql = 'DELETE FROM `reviews` WHERE `id` = :id';
        $db->doRequest($sql, $arrdata);
        return "Review was removed";
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
    $sql = "UPDATE `reviews` SET name=:name WHERE id=:id";
    $db->doRequest($sql, $arrdata);
    return "Name was changed";
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
    $sql = "UPDATE `reviews` SET surname=:surname WHERE id=:id";
    $db->doRequest($sql, $arrdata);
    return "Surname was changed";
    }

    public function changeReview()
    {
    global $db;
    if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
    if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
    if (!isset($this->data['id'])||
        !isset($this->data['review']))throw new ErrorAPI("Data wasn't sent", 400);
    
    $arrdata = array(
        'id' => $this->data['id'],
        'review' => $this->data['review']
    );
    $sql = "UPDATE `reviews` SET review=:review WHERE id=:id";
    $db->doRequest($sql, $arrdata);
    return "Review was changed";
    }

    public function changeAddInformation()
    {
    global $db;
    if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
    if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
    if (!isset($this->data['id'])||
        !isset($this->data['addInformation']))throw new ErrorAPI("Data wasn't sent", 400);
    
    $arrdata = array(
        'id' => $this->data['id'],
        'addInformation' => $this->data['addInformation']
    );
    $sql = "UPDATE `reviews` SET addInformation=:addInformation WHERE id=:id";
    $db->doRequest($sql, $arrdata);
    return "addInformation was changed";
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

        $path = 'images/reviews';
        if(!is_dir($path)){
            mkdir($path,0777);
         }

        $tmp = explode(".", $this->data['files'][0]['filename']);
        $ext =  array_pop($tmp);
        if (!in_array($ext, $this->whitelist)) throw new ErrorAPI("The extension is not supported", 400);
        
        $this->id = $this->data['id'];

        $sql = "SELECT namePhoto FROM `reviews`WHERE id=:id";
        $data = array(
            "id" => $this->data["id"]
        );
        $this->namePhoto = $db->doRequest($sql, $data)[0]["namePhoto"];

        $this->removePhotoOfReview();

        $upload = 'images/reviews/'.$this->data['files'][0]['filename'];
        move_uploaded_file($this->data['files'][0]['tmp'], $upload);
       
        $arrdata = array(
            'id' => $this->data['id'],
            'namePhoto' => $this->data['files'][0]['filename']
        );
        $sql = "UPDATE `reviews` SET namePhoto=:namePhoto WHERE id=:id";
        $db->doRequest($sql, $arrdata);
        return "Photo was changed";
    }

    private function removePhotoOfReview()
    {
        if ($this->namePhoto != "namePhoto")
        {
            unlink('images/reviews/'.$this->namePhoto);
        }  
    }
}
?>