<?php 
namespace Hcode;
use Rain\Tpl;

class Page{
	private $tpl;
	private $options =[]; //nesta variavel h� todos os dados que ser�o exibidos pelo template.
	private $defaults = [
		"header"=> true,
		"footer"=> true,
		"data"=>[]
	]; 
	public function __construct($opts = array(), $tlp_dir = "/views/"){		
		$this->options = array_merge($this->defaults,$opts); //se for igual os dados de defaults e options o que vem de options sobrescreve, se tiver diferencas entre eles um complementa o outro.
		$config = array(
			"tpl_dir"       =>$_SERVER["DOCUMENT_ROOT"].$tlp_dir,
			"cache_dir"     => $_SERVER["DOCUMENT_ROOT"]."/views-cache/",
			"debug" => false
		);
		Tpl::configure( $config );
		$this->tpl = new Tpl;
		
		$this->setData($this->options["data"]);
		if($this->options["header"]==true)$this->tpl->draw("header");
		
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
		if($this->options["footer"]==true) $this->tpl->draw("footer");
	}
}
?>