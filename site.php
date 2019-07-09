<?php
//use \Slim\Slim;
use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;

$app->get('/', function() {
	$products = Product::listAll();
	$page = new Page();
	$page ->setTpl("index",[
		'products'=>Product::checkList($products)
	]);
});

$app->get("/categories/:idCategory", function($idCategory){
	$category = new Category();
	$category->get((int)$idCategory);
	$page = new Page();
	$page->setTpl("category",[
		'category'=>$category->getValues(),
		'products'=>Product::checkList($category->getProducts())
	]);
});


?>