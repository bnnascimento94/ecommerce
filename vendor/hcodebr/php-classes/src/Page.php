<?php 
namespace Hcode;
use Rain\Tpl;

class Page{
	private $tpl;
	private $options =[]; //nesta variavel há todos os dados que serão exibidos pelo template.
	private $defaults = [
		"data"=>[]
	]; 
	public function __construct($opts = array()){
	    // config
		
		$this->options = array_merge($this->defaults,$opts); //se for igual os dados de defaults e options o que vem de options sobrescreve, se tiver diferencas entre eles um complementa o outro.
		$config = array(
			"tpl_dir"       =>$_SERVER["DOCUMENT_ROOT"]. "/views/",
			"cache_dir"     => $_SERVER["DOCUMENT_ROOT"]."/views-cache/",
			"debug" => false
		);
		Tpl::configure( $config );
		$this->tpl = new Tpl;
		
		$this->setData($this->options["data"]);
		$this->tpl->draw("header");
		
	}
	
	private function setData($data = array()){
		foreach($data as $key=>$value){
			$this->tpl->assign($key,$value);
		}
	}
	
	public function setTpl($name, $data= array(),$returnHTML = false){
		$this ->setData($data);
		return $this->tpl->draw($name,$returnHTML);
	
	}
	
	public function __destruct(){
		$this->tpl->draw("footer");
	}
}
?>