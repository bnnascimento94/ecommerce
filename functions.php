<?php	
use \Hcode\Model\User;

	function formatPrice(float $vlprice){
		
		return number_format($vlprice, 2,",",".");
	}
	
	function valorProduto(array $products){
			$somaValores = 0;
			foreach($products as $valor){
				$somaValores += $valor['vltotal'];
			}
			return $somaValores;
	}
	
	function checkLogin($inadmin = true){
		return User::checkLogin($inadmin);
	}
	
	function getUserName(){
	
		$user = User::getFromSession();
		return $user->getdeslogin();
	}
	
?>	