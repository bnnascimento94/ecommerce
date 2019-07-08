<?php
use \Slim\Slim;
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Product;

$app->get("/admin/products", function(){
	User::verifyLogin();
	$page = new  PageAdmin();
	$products = Product::listAll();
	$page->setTpl("products",array(
		'products'=>$products
	));

});

$app->get("/admin/products/create", function(){
	User::verifyLogin();	
	$page = new  PageAdmin();
	$page->setTpl("products-create");
});

$app->post("/admin/products/create",function(){
	$page = new  PageAdmin();
	$product = new Product();
	$_POST["idproduct"] = 0;
	//var_dump($_POST);
	$product->setData($_POST);
	$product->save();
     if($_FILES["file"]["name"] !== "") $product->setPhoto($_FILES['file']);
	header("Location: /admin/products");
	exit;	
});


$app->get("/admin/products/:idproduct",function($idproduct){
	User::verifyLogin();
	$page = new PageAdmin();
	$product = new Product();
	$product->get($idproduct);
	$page->setTpl("products-update",array(
	 "product"=>$product->getValues()
	
	));

});

$app->post("/admin/products/:idproduct", function($idProduct){
	$product = new Product();
	$_POST["idproduct"] = $idProduct;
	$product->setData($_POST);
	$product->update();
	$product->setPhoto($_FILES["file"]);
	header("Location: /admin/products");
	exit;
});

$app->get("/admin/products/:idProduct/delete",function($idProduct){
	User::verifyLogin();
	$page = new PageAdmin();
	$product = new Product();
	$product->delete($idProduct);
	$product ->deletePhoto($idProduct);
	header("Location: /admin/products");
	exit;
});



?>