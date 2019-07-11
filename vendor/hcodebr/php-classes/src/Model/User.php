<?php 

namespace Hcode\Model;

use \Hcode\Model;
use \Hcode\DB\Sql;
use \Hcode\Mailer;

class User extends Model {

	const SESSION = "User";
	const CIPHER  = "aes-128-gcm";
	const SECRET = "HcodePhp7_Secret";


	protected $fields = [
		"iduser", "idperson", "deslogin", "despassword", "inadmin", "dtergister"
	];
	
	
	public static function getFromSession(){
		$user = new User();
		if(isset($_SESSION[User::SESSION]) && (int) $_SESSION[User::SESSION]['iduser']>0){
			$user->setData($_SESSION[User::SESSION]);
		}
		return $user;
	}
	
	public static function checkLogin($inadmin = true){
	    if (
			!isset($_SESSION[User::SESSION])
			|| 
			!$_SESSION[User::SESSION]
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0
		){
			//nгo estб logado
			return false;
		}else{
		//var_dump($_SESSION[User::SESSION]);
			//pergunta se o usuario faz parte da rota da administraзгo
			if($inadmin ===true &&  (bool)$_SESSION[User::SESSION]['inadmin']===true ){
				return true;
			} 
			else if($inadmin=== false){
				return true;
			}else{
				return false;
			}
		}
	}

	public static function login($login, $password):User
	{
		$db = new Sql();

		$results = $db->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
			":LOGIN"=>$login
		));
		
		if (count($results) === 0) {
		
			throw new \Exception("Nгo foi possнvel fazer login.");
		}
		$data = $results[0];
		var_dump($password);
		if (password_verify($password, $data['despassword'])) {

			$user = new User();
			$user->setData($data);
			$_SESSION[User::SESSION] = $user->getValues();
			return $user;

		} else {

			throw new \Exception("Nгo foi possнvel fazer login.");

		}

	}

	public static function logout()
	{

		$_SESSION[User::SESSION] = NULL;

	}

	public static function verifyLogin($inadmin = true)
	{

		if (User::checkLogin($inadmin)) {
			
			//header("Location: /admin");
			//exit;
			return true;

		}

	}

	public static function listAll(){
		$sql = new Sql();
		
		return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");
	
	}
	
	public static function getForgot($email){
		$sql = new Sql();
		
		$results= $sql-> select("select * from tb_persons a inner join tb_users b USING(idperson) where a.desemail = :email;",array(
			":email"=> $email
		));
		
		if(count($results) === 0){
			throw new \Exception("Error Processing request", 1);			
		
		}else {
		
		    $data = $results[0];
			$results2 = $sql ->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)",array(
				":iduser"=> $data["iduser"], 
				":desip"=> $_SERVER["REMOTE_ADDR"]
			));
			if(count($results2) ===0){      			
				throw new \Exception("It's not possible send Emails", 1);
			
			}else{
				$dataRecovery = $results2[0];
				$ivlen = openssl_cipher_iv_length(User::CIPHER);
				$iv = openssl_random_pseudo_bytes($ivlen);
				$code = openssl_encrypt($dataRecovery["idrecovery"],User::CIPHER, User::SECRET, $options=0, $iv, $tag);
				$results3 = $sql->select("CALL sp_userspasswordsrecoveries_update(:idrecovery,:iv,:tag,:code)",array(
					":idrecovery"=>$dataRecovery["idrecovery"],
					":iv"=>$iv,
					":tag"=>$tag,
					":code"=>$code
				));
				if($results3 == 0){
					throw new \Exception("It was not possible send emails encrypted", 1);
				
				}else{
					$link = "http://www.hcodecommerce.com.br:8080/admin/forgot/reset/?code=$code";
					$mailer = new Mailer($data["desemail"],$data["desperson"],"Redefinir senha de Hcode Store","forgot",array(
						"name"=> $data["desperson"],
						"link"=>$link
					));
					$mailer->send();	
					return $data;
				}

			
			}
		}	
	}
	
	public static function validForgotDecrypt($code){
		$sql = new Sql();
	    $data = $sql->select("select * from tb_userspasswordsrecoveries where code_encrypt = :code",array(
			":code"=>$code
		));
		if($data ===0){
			throw new \Exception("It is impossible to decrypt", 1);
		}else{
		    //var_dump($data);
			$values = $data[0];
			$idrecovery = openssl_decrypt($code, User::CIPHER, User::SECRET, $options=0, $values['iv_encrypt'],$values['tag_encrypt']);
			$results = $sql->select("
			SELECT *
				FROM tb_userspasswordsrecoveries a 
				INNER JOIN tb_users b USING(iduser)
				INNER JOIN tb_persons c USING(idperson)
				WHERE 
				a.idrecovery = :idrecovery
				AND 
				a.dtrecovery IS NULL
				AND 
				date_add(a.dtregister, interval 1 HOUR)>= now()
			",array(":idrecovery"=>$idrecovery));
			
			if(count($results)===0){
				throw new \Exception("Nгo foi possнvel recuperar a senha");
			
			}else{
				return $results[0];
			}
		}
	}
	
	public static function setForgotUser($idrecovery){
		$sql = new Sql();
		$sql->query("Update tb_userspasswordsrecoveries SET dtrecovery = now() where idrecovery =:idrecovery",
		array(":idrecovery"=>$idrecovery));
	}
	
	public function setPassword($password){
		$sql = new Sql();
		$sql->query("Update tb_users SET despassword = :password where iduser = :iduser", array(
			":password"=>$password,
			":iduser"=>$this->getiduser()
		));
	
	}
	
	public function save(){
		$sql = new Sql();
				
		$results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)",
		array(
		":desperson"=>$this-> getdesperson(),
		":deslogin"=>$this-> getdeslogin(),
		":despassword"=>$this-> getdespassword(),
		":desemail"=>$this-> getdesemail(),
		":nrphone"=>$this-> getnrphone(),
		":inadmin"=>$this-> getinadmin()
		));
		
		$this->setData($results[0]);
	}

	public function get($iduser){
		$sql = new Sql();
		$results =$sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser ", 
		array(
			":iduser"=>$iduser
		));
		$this->setData($results[0]);
	}
	
	
	public function delete($iduser){
		$sql = new Sql();
		$sql->select("CALL sp_users_delete(:iduser)", array(
			":iduser"=>$iduser
		));
	}
	
	public function update($iduser){
		$sql = new Sql();
		$sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :desemail, :nrphone, :inadmin)", array(
		":iduser"=>$iduser,
		":desperson"=>$this-> getdesperson(),
		":deslogin"=>$this-> getdeslogin(),
		":desemail"=>$this-> getdesemail(),
		":nrphone"=>$this-> getnrphone(),
		":inadmin"=>$this-> getinadmin()
		
		));
	} 
}
 ?>