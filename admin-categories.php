<?php

use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;


$app->get("/admin/categories", function(){
	User::verifyLogin();
	$page = new  PageAdmin();
	$categories = Category::listAll();
	$page->setTpl("categories",array(
		'categories'=>$categories
	));

});

$app->get("/admin/categories/create", function(){
	User::verifyLogin();	
	$page = new  PageAdmin();
	$page->setTpl("categories-create");
});

$app->post("/admin/categories/create",function(){
	$page = new  PageAdmin();
	$category = new Category();
	$category->setData($_POST);
	$category->save();
	header("Location: /admin/categories");
	exit;	
});


$app->get("/admin/categories/:idCategory",function($idCategory){
	User::verifyLogin();
	$page = new PageAdmin();
	$category = new Category();
	$category->get($idCategory);
	$page->setTpl("categories-update",array(
	 "category"=>$category->getValues()
	));

});

$app->post("/admin/categories/:idCategory", function($idCategory){
	$category = new Category();
	$category->setData($_POST);
	$category->update($idCategory);
	header("Location: /admin/categories");
	exit;
});

$app->get("/admin/categories/:idCategory/delete",function($idCategory){
	User::verifyLogin();
	$page = new PageAdmin();
	$category = new Category();
	$category->delete($idCategory);
	header("Location: /admin/categories");
	exit;
});

$app->get("/categories/:idCategory", function($idCategory){
	$category = new Category();
	$category->get((int)$idCategory);
	$page = new Page();
	$page->setTpl("category",[
		'category'=>$category->getValues(),
		'products'=>[]
	]);
});

?>