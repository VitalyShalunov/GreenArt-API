<?php
class OurWorks
{
    private $data;
    private $whitelist = array('jpg','png','bmp','gif','wmf','jpeg','tif','tiff');

    function __construct($data) {
        $this->data = $data;
    }
    public function addOurWorks()
    {
        global $db;
        if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
        if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
        if (!isset($this->data['idCategory']))throw new ErrorAPI("Data wasn't sent", 400);
        if (!isset($this->data["files"])) throw new ErrorAPI("Files were not sent", 400);

        $path = 'images/ourworks';
        if(!is_dir($path)){
            mkdir($path,0777);
                 }

        $tmp = explode(".", $this->data['files'][0]['filename']);
        $ext =  array_pop($tmp);
        if (!in_array($ext, $this->whitelist)) throw new ErrorAPI("The extension is not supported", 400);

        $upload = 'images/ourworks/'.$this->data['files'][0]['filename'];
        move_uploaded_file($this->data['files'][0]['tmp'], $upload);

        $arrdata = array(
            'idCategory' => $this->data['idCategory'],
            'visible' => 0,
            'nameImage' => $this->data['files'][0]['filename']
        );
        $sql = 'INSERT INTO images(nameImage,idCategory,visible) 
            VALUES (:nameImage,:idCategory,:visible)';
        $db->doRequest($sql, $arrdata);
        return "Image was uploaded";
    }

    public function addPhoto()
    {
        global $db;
        if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
        if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
        if (!isset($this->data["files"])) throw new ErrorAPI("Files were not sent", 400);
        if (!isset($this->data['files'][0]['filename']) || 
            !isset($this->data['files'][0]['tmp'])||
            !isset($this->data['id'])) throw new ErrorAPI("Data wasn't sent", 400);

        $path = 'images/ourworks';
        if(!is_dir($path)){
            mkdir($path,0777);
        }

        $tmp = explode(".", $this->data['files'][0]['filename']);
        $ext =  array_pop($tmp);
        if (!in_array($ext, $this->whitelist)) throw new ErrorAPI("The extension is not supported", 400);

        $fileName = time().".".$ext;

        $upload = 'images/ourworks/'.$fileName;
        move_uploaded_file($this->data['files'][0]['tmp'], $upload);
       
        $arrdata = array(
            'id' => $this->data['id'],
            'nameImage' => $fileName
        );
        $sql = "UPDATE `images` SET nameImage=:nameImage WHERE id=:id";
        $db->doRequest($sql, $arrdata);
        return "Photo was added";
    }
        
    public function getWorksOfCategory()
    {
        global $db;
        if($this->data["method"] != "GET") throw new ErrorAPI("Method not allowed", 405);
        if(!isset($this->data['idPortal'])||
           !isset($this->data['idCategory'])||
           !isset($this->data['page'])) throw new ErrorAPI("Bad request", 400);
        $idPortal = $this->data['idPortal'];
        $idCategory = $this->data['idCategory'];
        $fromPage = $this->data['page'];

        if ($fromPage == 0)
        {
        $sql = "SELECT * FROM `images` WHERE `idCategory` = :idCategory";
        $arrdata = array(
            'idCategory' => $idCategory
        );
        }
        else
        {
            $sql = "SELECT * FROM `images` WHERE `idCategory` = :idCategory AND `visible` = :visible"; 
            $arrdata = array(
                'idCategory' => $idCategory,
                'visible' => $fromPage
            );
        }
        $worksOfCategory = $db->doRequest($sql,$arrdata);
        $path = 'images/ourworks/';
        foreach($worksOfCategory as $key => $image) {
            $worksOfCategory[$key]["pathPhotoWork"]= $path.$image['nameImage'];
        }
        $sql = "SELECT description,nameCategory FROM `categories` WHERE categories.id=:id";
        $arrdata = array(
            'id' => $idCategory
        );
        $answer = $db->doRequest($sql,$arrdata)[0];
        $answer["images"] = $worksOfCategory;
        return json_encode($answer);
    }

    public function getAllWorks()
    {
        global $db;
        if($this->data["method"] != "GET") throw new ErrorAPI("Method not allowed", 405);
        $sql = "SELECT * FROM `images`";
        $arrdata = NULL;
        $works = $db->doRequest($sql,$arrdata);
        $path = 'images/ourworks/';
        foreach($works as $key => $image) {
            $works[$key]["pathPhotoWork"]= $path.$image['nameImage'];
        }
        return json_encode($works);
    }

    public function addIdEmployeesInImages()
    {
        global $db;
        if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
        if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
        if(!isset($this->data['id'])||
           !isset($this->data['idEmployee'])) throw new ErrorAPI("Bad request", 400);
        if($this->data["idEmployee"] === "NULL") $this->data['idEmployee'] = NULL;
        $arrdata = array(
            'id' => $this->data['id'],
            'idEmployee' => $this->data['idEmployee']
        );
        $sql = "UPDATE `images` SET idEmployee=:idEmployee WHERE id=:id";
        $db->doRequest($sql, $arrdata);
        return "changing id was done";
    }

    public function changeVisible()
    {
        global $db;
        if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
        if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
        if (!isset($this->data['id'])||
            !isset($this->data['visible'])) throw new ErrorAPI("Data wasn't sent", 400);
        $arrdata = array(
            'id' => $this->data['id'],
            'visible' => $this->data['visible']
        );
        $sql = "UPDATE `images` SET visible=:visible WHERE id=:id";
        $db->doRequest($sql, $arrdata);
        return "Visible was changed";
    }

    public function changeIdCategory()
    {
        global $db;
        if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
        if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
        if (!isset($this->data['id'])||
            !isset($this->data['idCategory'])) throw new ErrorAPI("Data wasn't sent", 400);
        $arrdata = array(
            'id' => $this->data['id'],
            'idCategory' => $this->data['idCategory']
        );
        $sql = "UPDATE `images` SET idCategory=:idCategory WHERE id=:id";
        $db->doRequest($sql, $arrdata);
        return "idCategory was changed";
    }


    public function removePhoto()
    {
        global $db;
        if($this->data["method"] != "DELETE") throw new ErrorAPI("Method not allowed", 405);
        if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
        if (!isset($this->data["get"]["id"]))throw new ErrorAPI("Data wasn't sent", 400);
        $arrdata = array(
            'id' => $this->data["get"]["id"]
        );
        $sql = "SELECT * FROM `images` WHERE `id` = :id";
        $dataOfPhoto = $db->doRequest($sql, $arrdata);
        if ($dataOfPhoto[0]['nameImage'] != "nameImage")
        {
            unlink('images/ourworks/'.$dataOfPhoto[0]['nameImage']);
        }

        $sql = 'DELETE FROM `images` WHERE `id` = :id';
        $db->doRequest($sql, $arrdata);
        return "Photo was removed";
    }

    public function getCategories() {
        global $db;
        if($this->data["method"] != "GET") throw new ErrorAPI("Method not allowed", 405);
        if(!isset($this->data['idPortal'])) throw new ErrorAPI("Data wasn't sent", 400);

        $sql = "SELECT `c`.*, COUNT(`i`.`id`) AS `photoCount` FROM `categories` AS `c` LEFT JOIN `images` AS `i` ON `c`.`id` = `i`.`idCategory` WHERE `c`.`idPortal` = :id GROUP BY `c`.`id`";
        $data = array(
            "id" => $this->data['idPortal']
        );

        $categories = $db->doRequest($sql, $data);

        return json_encode($categories);
    }

    public function createCategory()
    {
        global $db;
        if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
        if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
        if(!isset($this->data['idPortal'])) throw new ErrorAPI("Data wasn't sent", 400);
        $arrdata = array(
            'nameCategory' => "nameCategory",
            'idPortal' => $this->data['idPortal'],
            'description' => "description",
        );
        $sql = 'INSERT INTO categories(nameCategory, idPortal, description) VALUES (:nameCategory, :idPortal, :description)';
        $db->doRequest($sql, $arrdata);
        return "Category was uploaded";
    }

    public function deleteCategory()
    {
        global $db;
        if($this->data["method"] != "DELETE") throw new ErrorAPI("Method not allowed", 405);
        if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
        if (!isset($this->data["get"]["id"]))throw new ErrorAPI("Data wasn't sent", 400);

        $arrdata = array(
            'id' => $this->data["get"]["id"]
        );

        $sql = 'DELETE FROM `images` WHERE `idCategory` = :id';
        $answer = $db->doRequest($sql, $arrdata);
        
        $sql = 'DELETE FROM `categories` WHERE `id` = :id';
        $db->doRequest($sql, $arrdata);
        return "Category was removed";
    }

    public function changeCategory()
    {
        global $db;
        if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
        if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
        if (!isset($this->data['id'])||
            !isset($this->data['nameCategory']))throw new ErrorAPI("Data wasn't sent", 400);
        $arrdata = array(
            'id' => $this->data['id'],
            'nameCategory' => $this->data['nameCategory']
        );
        $sql = "UPDATE `categories` SET nameCategory=:nameCategory WHERE id=:id";
        $db->doRequest($sql, $arrdata);
        return "Category was changed";
    }

    public function changeDescriptionCategory()
    {
        global $db;
        if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
        if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
        if (!isset($this->data['id'])||
            !isset($this->data['description']))throw new ErrorAPI("Data wasn't sent", 400);
        $arrdata = array(
            'id' => $this->data['id'],
            'description' => $this->data['description']
        );
        $sql = "UPDATE `categories` SET description=:description WHERE id=:id";
        $db->doRequest($sql, $arrdata);
        return "description was changed";
    }
}
?>