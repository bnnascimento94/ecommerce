<?php
namespace Hcode\Model;
use \Hcode\DB\Sql; 
use \Hcode\Model;
use \Hcode\Model\User;
class Cart extends Model{
	const SESSION = 'Cart';
	function getidcart(){
		return 0;
	}
	public static function getFromSession(){
		$cart = new Cart();
			if(isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]['idcart'] >0){
				$cart->get((int) $_SESSION[Cart::SESSION]['idcart']);
			
			}else{
					 $cart->getFromSessionID();
					if(!(int)$cart->getidcart()>0){
						$data=[
							'dessessionid'=> session_id()
						];
						
					if(User::checkLogin(false)){
						$user = User::getFromSession();
						$data['iduser'] =$user->getiduser();
						$data['deszipcode'] = 0;
						$data['vlfreight'] = 0;
						$data['nrdays'] = 0;
					}else{
						$data['iduser'] = 0;
						$data['deszipcode'] = 0;
						$data['vlfreight'] = 0;
						$data['nrdays'] = 0;
					}
				
					var_dump($data);
					$cart->setData($data);
					$cart->save();
					$cart->setToSession();
				}
			}
		return $cart;
	}
	
	public function setToSession(){
		$_SESSION[Cart::SESSION] = $this->getValues();
	}
	
	public function getFromSessionId(){
	    $sql = new Sql();
		$results = $sql->select("select * from tb_carts where dessessionid = :dessessionid",array(
			":dessessionid"=>session_id()
		));
		if(count($results) >0){
			$this->setData($results[0]);
		}
	}
	
	public function get($idcart){
		$sql = new Sql();
		$results = $sql->select("select * from tb_carts where idcart = :idcart",array(
			":idcart"=>$idcart
		));
		
		if(count($results) >0){
			$this->setData($results[0]);
		}	
	}
	
	public function save(){
		$sql = new Sql();
        $results = $sql->select("CALL sp_carts_save(:idcart,:dessessionid, :iduser, :deszipcode,:vlfreight,:nrdays)", [
            ':idcart' => $this->getidcart(),
            ':dessessionid' => $this->getdessessionid(),
            ':iduser' => $this->getiduser(),
            ':deszipcode' =>$this->getdeszipcode(),
            ':vlfreight' => $this->getvlfreight(),
            ':nrdays' => $this->getnrdays(),
        ]);
		$this->setData($results[0]);
	}
	
	public function addProduct(Product $product){
		$sql = new Sql();
			//var_dump($this->getvalues()['idcart']);
			//var_dump($product->getidproduct());
			//exit;
		
		$row = $sql->query("Insert into tb_cartsproducts(idcart, idproduct) values (:idcart, :idproduct)",[
			":idcart"=>$this->getvalues()['idcart'],
			":idproduct"=>$product->getidproduct()
		]);
	
	}
	
	public function removeProduct(Product $product, $all= false){
	$sql = new Sql();
		if($all){
			$sql->query("Update tb_cartsproducts set dtremoved = now() where idcart = :idcart and idproduct = :idproduct",[
				//":idcart"=>$this->getidcart(),
				":idcart"=>$this->getvalues()['idcart'],
				":idproduct"=>$product ->getidproduct()
			]);
		
		}else{
			$sql->query("Update tb_cartsproducts set dtremoved = now() where idcart = :idcart and idproduct = :idproduct and dtremoved is null LIMIT 1",[
				":idcart"=>$this->getvalues()['idcart'],
				//":idcart"=>$this->getidcart(),
				":idproduct"=>$product ->getidproduct()
			]);
		
		}
		
	
	}
	
	public function getProducts(){
		$sql = new Sql();
		//var_dump($this->getidcart());
		$rows = $sql->select("select b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight,b.vllength, b.vlweight,b.desurl, count(*) as nrqtd, sum(vlprice) as vltotal from tb_cartsproducts a INNER JOIN tb_products b on a.idproduct = b.idproduct where a.idcart = :idcart and dtremoved is null group by b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight,b.vllength, b.vlweight, b.desurl order by b.desproduct",[
		
			":idcart"=>$this->getvalues()['idcart']
		
		]);
		
		return Product::checkList($rows);
	
	}
	
	
}
?>