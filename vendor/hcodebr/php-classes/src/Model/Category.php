<?php

namespace Hcode\Model;
use \Hcode\DB\Sql; 
use \Hcode\Model;
class Category extends Model{

	public static function listAll(){
		$sql = new Sql();
		return $sql->select("SELECT * FROM tb_categories");
	}

	public function save(){
		$sql = new Sql();	
		$results = $sql->select("CALL sp_categories_save(:idcategory,:category)",
		array(
			":idcategory"=>0,
			":category"=>$this->getdescategory()
		));
		$this->setData($results[0]);
		Category::updateFile();
	}
	
    public function get($idcategory){
		$sql = new Sql();
		$results =$sql->select("SELECT * FROM tb_categories where idcategory = :idcategory", 
		array(
			":idcategory"=>$idcategory
		));
		$this->setData($results[0]);
	}
	
	public function update($category){
		$sql = new Sql();
		$sql->select("CALL sp_categories_save(:idcategory,:category)", array(
		":idcategory"=>$category,
		":category"=>$this->getdescategory()
		));
		Category::updateFile();
	}

	public function delete($idcategory){
		$sql = new Sql();
		$sql->query("delete from tb_categories where idcategory = :idcategory", array(
			":idcategory"=>$idcategory
		));
		
		Category::updateFile();
	}
	
	public static function updateFile(){
		$categories = Category::listAll();
		$html = [];
		
		foreach($categories as $row){
		
			array_push($html,'<li><a href="/categories/'.$row['idcategory'].'">'.$row['descategory'].'</a></li>');
			file_put_contents($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR.'categories-menu.html',implode('',$html));
		}
	
	}
	
	public function getProducts($related =true){
		$sql = new Sql();
		
		if($related ===true){
		return $sql->select("
				Select * from tb_products where idproduct in (
				Select a.idproduct
				From tb_products a 
				INNER JOIN tb_productscategories b on a.idproduct = b.idproduct
				where b.idcategory = :idcategory)
			",[":idcategory"=>$this->getidcategory()]);
		
		}else{			
		  return  $sql->select("
				Select * from tb_products where idproduct not in (
				Select a.idproduct
				From tb_products a 
				INNER JOIN tb_productscategories b on a.idproduct = b.idproduct
				where b.idcategory = :idcategory)
			",[":idcategory"=>$this->getidcategory()]);
		}
	
	}
	
	public function addProduct(Product $product){
		$sql = new Sql();
		$sql->query("insert into tb_productscategories (idcategory,idproduct) values (:idcategory,:idproduct)", array(
			":idcategory"=>$this->getidcategory(),
			":idproduct"=>$product->getidproduct()
		));
	}
	
    public function removeProduct(Product $product){
		$sql = new Sql();
		$sql->query("delete from tb_productscategories where idcategory= :idcategory and idproduct= :idproduct", array(
			":idcategory"=>$this->getidcategory(),
			":idproduct"=>$product->getidproduct()
		));
	}
	
	
}
?>