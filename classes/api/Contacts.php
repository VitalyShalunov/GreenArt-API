<?php
class Contacts{
private $data;

function __construct($data) {
    $this->data = $data;
}


public function getContact()
{
    global $db;
    if($this->data["method"] != "GET") throw new ErrorAPI("Method not allowed", 405);
    if (!isset($this->data['idPortal']))throw new ErrorAPI("Data wasn't sent", 400);

    $arrdata = array(
        'idPortal' => $this->data['idPortal']
    );
    $sql = "SELECT * FROM `contacts` WHERE idPortal=:idPortal";
    $answer = $db->doRequest($sql, $arrdata);
    return json_encode($answer);
}

public function changeTitle()
{
    global $db;
    if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
    if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
    if (!isset($this->data['title']) ||
        !isset($this->data['idPortal']))throw new ErrorAPI("Data wasn't sent", 400);

    $arrdata = array(
        'idPortal' => $this->data['idPortal'],
        'title' => $this->data['title']
    );
    $sql = "UPDATE `contacts` SET title=:title WHERE idPortal=:idPortal";
    $db->doRequest($sql, $arrdata);
    return "Title was changed";
}

public function changeAddress()
{
    global $db;
    if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
    if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
    if (!isset($this->data['nameAddress'])||
        !isset($this->data['idPortal']))throw new ErrorAPI("Data wasn't sent", 400);
    
    $arrdata = array(
        'idPortal' => $this->data['idPortal'],
        'nameAddress' => $this->data['nameAddress']
    );
    $sql = "UPDATE `contacts` SET nameAddress=:nameAddress WHERE idPortal=:idPortal";
    $db->doRequest($sql, $arrdata);
    return "Address was changed";
}

public function changeNumberPhone()
{
    global $db;
    if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
    if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
    if (!isset($this->data['numberPhone'])||
        !isset($this->data['idPortal']))throw new ErrorAPI("Data wasn't sent", 400);
    
    $arrdata = array(
        'idPortal' => $this->data['idPortal'],
        'numberPhone' => $this->data['numberPhone']
    );
    $sql = "UPDATE `contacts` SET numberPhone=:numberPhone WHERE idPortal=:idPortal";
    $db->doRequest($sql, $arrdata);
    return "Number phone was changed";
}

public function changeEmail()
{
    global $db;
    if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
    if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
    if (!isset($this->data['email'])||
        !isset($this->data['idPortal']))throw new ErrorAPI("Data wasn't sent", 400);
    
    $arrdata = array(
        'idPortal' => $this->data['idPortal'],
        'email' => $this->data['email']
    );
    $sql = "UPDATE `contacts` SET email=:email WHERE idPortal=:idPortal";
    $db->doRequest($sql, $arrdata);
    return "Email was changed";
}
}