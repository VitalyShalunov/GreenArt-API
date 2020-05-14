<?php
/**
 * 
 */
class AboutUs 
{
	private $data;		
	function __construct($data)
	{
		$this->data = $data;
	}
	
	public function updateAboutus(){
		global $db;
		if(!is_dir('images/aboutus')){
		mkdir('images/aboutus',0777);
		}
		if($this->data['method']!= 'POST') throw new  ErrorAPI('Method not Allowed',405);
		if(!isset($this->data['portalId'])|| !isset($this->data['title'])||
		!isset($this->data['description'])) throw new ErrorAPI('Bad Request',400);

		if(!empty($this->data['files'])) {
			$this->deletePhotos($this->data['portalId']);
			$fileNames = array();
			foreach($this->data['files'] as $file){
				$upload = 'images/aboutus/'.$file['filename'];
				array_push($fileNames,$file['filename']);
				move_uploaded_file($file["tmp"],$upload);
			}
			$arrdata = array(
				'portalId' => $this->data['portalId'],
				'title' => $this->data['title'],
				'description' => $this->data['description'],
				'files' => json_encode($fileNames)
				
			);
	
			$sql = 'UPDATE AboutUs SET title=:title,description=:description,files=:files WHERE portalId=:portalId';
			$db->doRequest($sql,$arrdata);
			return 'data was updated';
		}
		else {
			$arrdata = array(
				'portalId' => $this->data['portalId'],
				'title' => $this->data['title'],
				'description' => $this->data['description']
			);
	
			$sql = 'UPDATE AboutUs SET title=:title,description=:description WHERE portalId=:portalId';
			$db->doRequest($sql,$arrdata);
			return 'data was updated';
		}
	}
	public function getAboutUs(){
		global $db;
		if($this->data['method']!='GET') throw new ErrorAPI('Method not Allowed',405);
		if(!isset($this->data['portalId'])) throw new ErrorAPI('Bad request',400);
		$sql = 'SELECT * FROM `aboutus` WHERE `portalId`=:portalId';
		$arrdata = array(
			'portalId' => $this->data['portalId']
		);
		$answer =$db->doRequest($sql,$arrdata);
		return json_encode($answer);

	}

	private function deletePhotos($id){
		global $db;
        $arrdata = array(
            'portalId' => $id
        );
        
        $sql = "SELECT 'images' FROM 'about' WHERE `portalId` = :portalId";
		$dataOfAboutUs = $db->doRequest($sql, $arrdata);
		$decode = json_decode($dataOfAboutUs[0]["images"]);
		for ($i=0; $i < 3; $i++) { 
			unlink('images/aboutus/' . $decode[$i]);
		}
        

	}
}
?>