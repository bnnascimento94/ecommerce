<?php
use \Slim\Slim;
namespace Hcode\Model;
use \Hcode\DB\Sql; 
use \Hcode\Model;
class Product extends Model{

	public static function listAll(){
		$sql = new Sql();
		return $sql->select("SELECT * FROM tb_products order by desproduct");
	}

	public static function checkList($list){
		foreach($list as &$row){
			$p = new Product();
			$p->setData($row);
			$row = $p->getValues();
		}
		return $list;
	}

	
	public function save(){
	
		 $teste = array(
			":idproduct"=>$this->getidproduct(),
			":desproduct"=>$this->getdesproduct(),
			":vlprice"=>(double)$this->getvlprice(),
			":vlwidth"=>(double)$this->getvlwidth(),
			":vlheight"=>(double)$this->getvlheight(),
			":vllength"=>(double)$this->getvllength(),
			":vlweight"=>(double)$this->getvlweight(),
			":desurl"=>$this->getdesurl()
		);
		var_dump($teste);
		$sql = new Sql();			
		$results = $sql->select("CALL sp_products_save(:idproduct, :desproduct,:vlprice,:vlwidth, :vlheight, :vllength, :vlweight, :desurl)",
		$teste);
		
		$this->setData($results[0]);
	}
	
    public function get($idproduct){
		$sql = new Sql();
		$results =$sql->select("SELECT * FROM tb_products where idproduct = :idproduct", 
		array(
			":idproduct"=>$idproduct
		));
		$this->setData($results[0]);
	}
	
	public function update(){
		$sql = new Sql();			
		$results = $sql->select("CALL sp_products_save(:idproduct, :desproduct,:vlprice,:vlwidth, :vlheight, :vllength, :vlweight, :desurl)",
		array(
			
			":idproduct"=>$this->getidproduct(),
			":desproduct"=>$this->getdesproduct(),
			":vlprice"=>$this->getvlprice(),
			":vlwidth"=>$this->getvlwidth(),
			":vlheight"=>$this->getvlheight(),
			":vllength"=>$this->getvllength(),
			":vlweight"=>$this->getvlweight(),
			":desurl"=>$this->getdesurl()
		));
		
		$this->setData($results[0]);
	}

	public function delete($idproduct){
		$sql = new Sql();
		$sql->query("delete from tb_products where idproduct = :idproduct", array(
			":idproduct"=>$idproduct
		));
		
		//Category::updateFile();
	}
	
	public function checkPhoto(){
		if(file_exists(
			$_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.
			"res".DIRECTORY_SEPARATOR.
			"site".DIRECTORY_SEPARATOR.
			"img".DIRECTORY_SEPARATOR.
			"products".DIRECTORY_SEPARATOR.
			$this->getidproduct().".jpg")){
				$url = "/res/site/img/products/".$this->getidproduct().".jpg";
			}else{
				$url = "/res/site/img/product.jpg";
			}
		return $this->setdesphoto($url); //aqui ele chama um metodo  __CALL no model para ir junto ao getValues;
	}	
	
	
	public function getValues(){
		$this->checkPhoto();
		$values = parent::getValues();
		return $values;
	}
		
	public function setPhoto($file){
		$extension = explode('.',$file['name']);
		$extension = end($extension);
		
		switch($extension){
			case "jpg":
			case "jpeg":
				$img = imagecreatefromjpeg($file["tmp_name"]);
			break;
			case "gif":
				$img = imagecreatefromgif($file["tmp_name"]);
			break;
			case "png":
				$img = imagecreatefrompng($file["tmp_name"]);
			break;
		}
		
		$dest = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.
			"res".DIRECTORY_SEPARATOR.
			"site".DIRECTORY_SEPARATOR.
			"img".DIRECTORY_SEPARATOR.
			"products".DIRECTORY_SEPARATOR.
			$this->getidproduct().".jpg";
		
		imagejpeg($img,$dest);
		imagedestroy($img);
		
		$this->checkPhoto();
	}
	
	public function deletePhoto($idproduct){
		$path = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.
			"res".DIRECTORY_SEPARATOR.
			"site".DIRECTORY_SEPARATOR.
			"img".DIRECTORY_SEPARATOR.
			"products".DIRECTORY_SEPARATOR.
			$idproduct.".jpg";
		if(file_exists($path)){
			unlink($path);
		}
	}
	
	public function getFromUrl($desurl){
		$sql = new Sql();
		$results =$sql->select("SELECT * FROM tb_products where desurl = :desurl Limit 1", 
		array(
			":desurl"=>$desurl
		));
		$this->setData($results[0]);
	}
	
	public function getCategories(){
		$sql = new Sql();
		return $sql->select("
			select * from tb_categories a INNER JOIN tb_productscategories b ON a.idcategory = b.idcategory WHERE b.idproduct = :idproduct
		", array(":idproduct"=>$this->getidproduct()));
	
	}
	
	
	
}
?>