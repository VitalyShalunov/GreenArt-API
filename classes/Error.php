<?php
class ErrorAPI extends Exception{
    public $stat, $eMessage;

    public function __construct($massage, $st){
        $this->stat = $st;
        $this->eMessage = $massage;
    }
}

?>