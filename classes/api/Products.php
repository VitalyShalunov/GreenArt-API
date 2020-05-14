<?php
class Products
{
    private $data;
    private $namePhoto;
    private $whitelist = array('jpg','png','bmp','gif','wmf','jpeg','tif','tiff');
    private $id;
    function __construct($data) {
        $this->data = $data;
    }

    public function createProduct()
    {
        global $db;
        if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
        if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
        if (!isset($this->data['idPortal']))throw new ErrorAPI("Data wasn't sent", 400);

        $path = 'images/products';
        if(!is_dir($path)){
            mkdir($path,0777);
        }

        $category = 0;
        if($this->data['idPortal'] == 2) {
            $category = $this->data["categoryId"];
        }

        $arrdata = array(
            'name' => "name product",
            'description' => "description",
            'cost' => 1000,
            'discount' => 0,
            'namePhoto' => "namePhoto",
            'idPortal' => $this->data['idPortal'],
            'categoryId' => $category
        );
        $sql = 'INSERT INTO products(name,description,cost,discount, namePhoto, idPortal, categoryId) VALUES (:name, :description, :cost, :discount, :namePhoto, :idPortal, :categoryId)';
        $db->doRequest($sql, $arrdata);
        return "Product was uploaded";
    }

    public function deleteProduct()
    {
        global $db;
        if($this->data["method"] != "DELETE") throw new ErrorAPI("Method not allowed", 405);
        if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
        if (!isset($this->data["get"]["id"]))throw new ErrorAPI("Data wasn't sent", 400);

        $sql = "SELECT namePhoto FROM `products`WHERE id=:id";
        $data = array(
            "id" => $this->data["get"]["id"]
        );
        $this->namePhoto = $db->doRequest($sql, $data)[0]["namePhoto"];

        $this->removePhotoOfProduct();

        $arrdata = array(
            'id' => $this->data["get"]["id"]
        );
        $sql = 'DELETE FROM `products` WHERE `id` = :id';
        $db->doRequest($sql, $arrdata);
        return "Products was removed";
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

        $path = 'images/products';
        if(!is_dir($path)){
            mkdir($path,0777);
        }

        $tmp = explode(".", $this->data['files'][0]['filename']);
        $ext =  array_pop($tmp);
        if (!in_array($ext, $this->whitelist)) throw new ErrorAPI("The extension is not supported", 400);
       
        $this->id = $this->data["id"];

        $sql = "SELECT namePhoto FROM `products`WHERE id=:id";
        $data = array(
            "id" => $this->data["id"]
        );
        $this->namePhoto = $db->doRequest($sql, $data)[0]["namePhoto"];

        $this->removePhotoOfProduct();

        $imageName = time().".".$ext;

        $upload = 'images/products/'.$imageName;
        move_uploaded_file($this->data['files'][0]['tmp'], $upload);
       
        $arrdata = array(
            'id' => $this->data['id'],
            'namePhoto' => $imageName
        );
        $sql = "UPDATE `products` SET namePhoto=:namePhoto WHERE id=:id";
        $db->doRequest($sql, $arrdata);
        return "Product was changed";
    }

    private function removePhotoOfProduct()
    {
        if ($this->namePhoto != "nameImage")
        {
            unlink('images/products/'.$this->namePhoto);
        }  
    }

    public function getCategories() {
        global $db;
        if($this->data["method"] != "GET") throw new ErrorAPI("Method not allowed", 405);

        $sql = "SELECT * FROM `productCategories`";
        return json_encode($db->doRequest($sql));
    }

    public function getProductsFromCategory() {
        global $db;
        if($this->data["method"] != "GET") throw new ErrorAPI("Method not allowed", 405);
        if (!isset($this->data['categoryId']))throw new ErrorAPI("Data wasn't sent", 400);

        $sql = "SELECT * FROM `products` WHERE `categoryId` = :categoryId";
        $data = array("categoryId" => $this->data['categoryId']);

        return json_encode($db->doRequest($sql, $data));
    }

    public function changeDescription() 
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
        $sql = "UPDATE `products` SET description=:description WHERE id=:id";
        $db->doRequest($sql, $arrdata);
        return "Description was changed";
    }   

    public function changeName() {
        global $db;
        if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
        if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
        if (!isset($this->data['id'])||
            !isset($this->data['name']))throw new ErrorAPI("Data wasn't sent", 400);
        $arrdata = array(
            'id' => $this->data['id'],
            'name' => $this->data['name']
        );
        $sql = "UPDATE `products` SET name=:name WHERE id=:id";
        $db->doRequest($sql, $arrdata);
        return "Name was changed";
    }

    public function changeCost() 
    {
        global $db;
        if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
        if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
        if (!isset($this->data['id'])||
            !isset($this->data['cost']))throw new ErrorAPI("Data wasn't sent", 400);
        $arrdata = array(
            'id' => $this->data['id'],
            'cost' => $this->data['cost']
        );
        $sql = "UPDATE `products` SET cost=:cost WHERE id=:id";
        $db->doRequest($sql, $arrdata);
        return "Cost was changed";
    }   

    public function changeDiscount() 
    {
        global $db;
        if($this->data["method"] != "POST") throw new ErrorAPI("Method not allowed", 405);
        if(!Engine::checkAuth($this->data["auth"])) throw new ErrorAPI("Unauthorized", 401);
        if (!isset($this->data['id'])||
            !isset($this->data['discount']))throw new ErrorAPI("Data wasn't sent", 400);
        $arrdata = array(
            'id' => $this->data['id'],
            'discount' => $this->data['discount']
        );
        $sql = "UPDATE `products` SET discount=:discount WHERE id=:id";
        $db->doRequest($sql, $arrdata);
        return "Discount was changed";
    }   

    public function getProducts()
    {
        global $db;
        if($this->data["method"] != "GET") throw new ErrorAPI("Method not allowed", 405);
        if(!isset($this->data['idPortal'])) throw new ErrorAPI("Data wasn't sent", 400);
        $sql = "SELECT * FROM `products` WHERE `idPortal` = :idPortal";
        $arrdata = array(
            "idPortal" => $this->data['idPortal']
        );
        $products = $db->doRequest($sql,$arrdata);

        $path = 'images/products/';
        foreach($products as $key => $product) {
            $products[$key]["pathPhotoProduct"]= $path.$product['namePhoto'];
        }

        return json_encode($products);
    }

    public function getProduct() {
        global $db;
        if($this->data["method"] != "GET") throw new ErrorAPI("Method not allowed", 405);
        if(!isset($this->data['productId'])) throw new ErrorAPI("Data wasn't sent", 400);

        $sql = "SELECT * FROM `products` WHERE `id` = :id";
        $arrdata = array(
            "id" => $this->data['productId']
        );
        $product = $db->doRequest($sql,$arrdata)[0];
        $path = 'images/products/';
        $product["pathPhotoProduct"]= $path.$product['namePhoto'];
        return json_encode($product);
    }
}
?>