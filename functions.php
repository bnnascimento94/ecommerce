<?php	
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
	
?>	