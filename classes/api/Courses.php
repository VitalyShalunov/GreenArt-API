<?php
require_once 'Parts.php';
require_once 'Works.php';
require_once './classes/Engine.php';
class Courses
{
    private $data;
    private $whitelist = array('jpg','png','bmp','gif','wmf','jpeg','tif','tiff');
    private $id;
    function __construct($data) {
        $this->data = $data;
    }

    public function getAllCourses()
    {
        global $db;
        if($this->data["method"] != "GET") throw new ErrorAPI("Method not allowed", 405);

        $sql = "SELECT * FROM `courses`";
        $arrdata = NULL;
        $answer = $db->doRequest($sql,$arrdata);
        return json_encode($answer);
    }

    public function getCourse()
    {
        global $db;
        if($this->data["method"] != "GET") throw new ErrorAPI("Method not allowed", 405);
        if (!isset($this->data['id'])) throw new ErrorAPI("Data wasn't sent", 400);

        $sql = "SELECT `c`.`name`,  `c`.`description`, `c`.`shortDescription`, `c`.`costCourse`,  `c`.`costPart`, `c`.`costSection`,
         `c`.`dateStart`,  `c`.`dateEnd`,  `c`.`duration`,  `c`.`descriptionOfTraining`,  `c`.`namePhoto`,  `c`.`idTeacher`, `e`.`name` AS `nameTeacher`,
         `e`.`middleName`, `e`.`surname`, `e`.`post`, `e`.`namePhoto` AS `photoTeacher`,`e`.`characteristic`
          FROM `courses` AS `c` LEFT JOIN `employees` AS `e` ON(`c`.`idTeacher` = `e`.`id`) WHERE `c`.`id` = :id";
        $data = array("id" => $this->data['id']);
        $result = $db->doRequest($sql, $data)[0];
      
        $path = 'images/courses/';
        $result["pathPhotoCourse"] = $path.$result['name'].'/'.$result["namePhoto"];

        $result["teacher"] = [
            "name" => $result["nameTeacher"],
            "middleName" => $result["middleName"],
            "surname" => $result["surname"],
            "post" => $result["post"],
            "pathPhotoTeacher" => 'images/employees/'.$result["photoTeacher"],
            "characteristic" => $result["characteristic"]
            
       ];
       unset($result['nameTeacher'],$result['middleName'], $result['surname'],$result['post'],$result['photoTeacher'],$result['characteristic'] );
        $path = $path.$result['name'].'/';
        
        $sql = "SELECT * FROM `parts` WHERE idCourse = :id";
        $arrdata = array(
            'id' => $this->data['id']
        );
        $answer = $db->doRequest($sql,$arrdata);
        $result["parts"] = $answer;
        

        foreach($result["parts"] as $key => $part) {
            
            $result["parts"][$key]["pathPhotoPart"]= $path.$result["parts"][$key]["namePart"].'/'.$result["parts"][$key]['namePhoto'];
            $sql = "SELECT * FROM `sections` WHERE `idPart` = :id";
            $data = array("id" => $part["idPart"]);
            $result["parts"][$key]["sections"] = $db->doRequest($sql, $data);

            foreach($result["parts"][$key]["sections"] as $keysec => $section) {
                $result["parts"][$key]["sections"][$keysec]["pathPhotoSection"] =  $path.$result["parts"][$key]["namePart"].'/'.$result["parts"][$key]["sections"][$keysec]["nameSection"].'/'.$result["parts"][$key]["sections"][$keysec]["namePhoto"];
            }
            
        }

        $sql = "SELECT * FROM `works` WHERE idCourse=:id";
        $data = array(
            "id" => $this->data["id"]
        );
        $result["works"] = $db->doRequest($sql, $data);
        //$path='images/courses/'.$course.'/works/';
        foreach($result["works"] as $key => $work) {
            $result["works"][$key]["pathPhotoWork"]= $path.'works/'.$work['namePhoto'];
        }

        return json_encode($result);
    }
    public function createCourse()
    {
        global $db;
        if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
        if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
        if(!is_dir('images/courses')){
            mkdir('images/courses',0777);
            }
        $courseName = time();
        mkdir('images/courses/'.$courseName,0777);
        $arrdata = array(
            'name' => $courseName,
            'description' =>  'description',
            'shortDescription' => 'shortDescription',
            'costCourse' => 0,
            'costPart' => 0,
            'costSection' => 0,
            'dateStart' => 1,
            'dateEnd' => 1,
            'namePhoto' => 'namePhoto',
            'duration' => 0,
            'descriptionOfTraining' => 'descriptionOfTraining',
            'idTeacher' => 0,
        );
        $sql = 'INSERT INTO `courses`(`name`,`namePhoto`, `description`, `shortDescription`, `costCourse`, `costPart`, `costSection`, `dateStart`, `dateEnd`, `duration`, `descriptionOfTraining`, `idTeacher`) 
                            VALUES (:name, :namePhoto, :description, :shortDescription, :costCourse, :costPart, :costSection, :dateStart, :dateEnd, :duration, :descriptionOfTraining, :idTeacher)';
        $db->doRequest($sql, $arrdata);
        return "Course was created";
    }

    public function changeInfoOfCourse() {
        global $db;
        if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
        if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
        if(!isset($this->data["courseName"]) || 
           !isset($this->data["description"]) || 
           !isset($this->data["shortDescription"])  || 
           !isset($this->data["descriptionOfTraining"]) || 
           !isset($this->data["costCourse"]) ||
           !isset($this->data["costPart"]) || 
           !isset($this->data["costSection"]) || 
           !isset($this->data["dateStart"])  || 
           !isset($this->data["dateEnd"]) || 
           !isset($this->data["duration"]) || 
           !isset($this->data["id"]) ||
           !isset($this->data["idTeacher"])) throw new ErrorAPI("Data wasn't sent", 400);
        
        if(!is_dir('images/courses')){
            mkdir('images/courses',0777);
            }
            $sql = "SELECT name FROM `courses` WHERE id=:id";
            $data = array(
                "id" => $this->data["id"]
            );
            $answer = $db->doRequest($sql, $data);
            $path = 'images/courses/'.$answer[0]["name"];
            if(!is_dir($path)){
                mkdir('images/courses/'.$this->data["courseName"],0777);
            }
            else {
                rename($path, 'images/courses/'.$this->data["courseName"]);
            }

        $sql = "UPDATE `courses` SET `name`=:courseName,`description`=:description,`shortDescription`=:shortDescription,
                `costCourse`=:costCourse,`costPart`=:costPart,`costSection`=:costSection,`dateStart`=:dateStart,
                `dateEnd`=:dateEnd,`duration`=:duration,`descriptionOfTraining`=:descriptionOfTraining, `idTeacher`=:idTeacher 
                WHERE `id` = :id";
        $data = array(
            "courseName" => $this->data["courseName"],
            "description" => $this->data["description"],
            "shortDescription" =>$this->data["shortDescription"],
            "costCourse" => $this->data["costCourse"],
            "costPart" => $this->data["costPart"],
            "costSection" => $this->data["costSection"],
            "dateStart" => $this->data["dateStart"],    
            "dateEnd" => $this->data["dateEnd"],
            "duration" => $this->data["duration"],
            "descriptionOfTraining" => $this->data["descriptionOfTraining"],
            "id" => $this->data["id"],
            "idTeacher" => $this->data["idTeacher"]
        );

        $db->doRequest($sql, $data);
        return "Course was update";
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
        $this->removePhotoOfCourse();

        $sql = "SELECT name FROM `courses` WHERE id=:id";
        $data = array(
            "id" => $this->data["id"]
        );
        $answer = $db->doRequest($sql, $data);
        $fileName = time().".".$ext;
        $upload = 'images/courses/'.$answer[0]["name"].'/'.$fileName;
        move_uploaded_file($this->data['files'][0]['tmp'], $upload);
       
        $arrdata = array(
            'id' => $this->data['id'],
            'namePhoto' => $fileName
        );
        $sql = "UPDATE `courses` SET namePhoto=:namePhoto WHERE id=:id";
        $db->doRequest($sql, $arrdata);
        return "Photo was changed";
    }

    public function deleteCourse()
    {
        global $db;
        if($this->data["method"] != "DELETE") throw new ErrorAPI("Method not allowed", 405);
        if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
        if (!isset($this->data["get"]["id"]))throw new ErrorAPI("Data wasn't sent", 400);

        $this->id = $this->data["get"]["id"];
        $this->removePhotoOfCourse();
        $sql = "SELECT name FROM `courses` WHERE id=:id";
        $data = array(
            "id" => $this->id
        );
        $answer = $db->doRequest($sql, $data);
        $path = 'images/courses/'.$answer[0]["name"];

        $arrdata = array(
            'id' => $this->data["get"]["id"]
        );

        $sql = 'SELECT * FROM `works` WHERE `idCourse` = :id';
        $answer = $db->doRequest($sql, $arrdata);
        
        for ($i=0; $i < count($answer); $i++) { 
            $data = array(
                "files"=> "",
                "method"=> "DELETE",
                "get"=> array( 
                "action"=> "deleteCourse",
                "id"=> $answer[$i]["idWork"]
                )
            );
            $deleteSection = new Works($data);
            $deleteSection->deleteWork();
        }

        $sql = 'SELECT * FROM `parts` WHERE `idCourse` = :id';
        $answer = $db->doRequest($sql, $arrdata);

        for ($i=0; $i < count($answer); $i++) { 
            $data = array(
                "files"=> "",
                "method"=> "DELETE",
                "get"=> array( 
                "action"=> "deletePart",
                "id"=> $answer[$i]["idPart"]
                )
            );
            $deleteSection = new Parts($data);
            $deleteSection->deletePart();
        }
        $arrdata = array(
            'id' => $this->data["get"]["id"]
        );
        $sql = 'DELETE FROM `courses` WHERE `id` = :id';
            $db->doRequest($sql, $arrdata);
        
            Engine::removeDirectory($path);
            return "Course was removed";
    }

    private function removePhotoOfCourse()
    {
        global $db;
        $arrdata = array(
            'id' => $this->id
        );
        
        $sql = "SELECT * FROM `courses` WHERE `id` = :id";
        $dataCourse = $db->doRequest($sql, $arrdata);
        if ($dataCourse[0]['namePhoto'] != "namePhoto")
        {
            unlink('images/courses/'.$dataCourse[0]['name'].'/'.$dataCourse[0]['namePhoto']);
        }
    }
}
?>