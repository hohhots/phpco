<?php
	/***************************************************************************
		               config.php
		           -------------------
		begin         : 9-Apr-06
		copyright     : (C) 2006 The nm114.net brgd
	    email         : brgd@nm114.net
		
		$Id: config.php, 9-Apr-06 11:46:12 PM brgd 
		
	 ***************************************************************************/
//GRANT ALL PRIVILEGES ON phpco.* TO 'xxb'@'localhost'
//    ->     IDENTIFIED BY 'some_pass' WITH GRANT OPTION;
//If publish this program, must set 
//php.ini->display_errors=Off and error_reporting=Off
class globalConfig extends sysClass{
		
	public static function makeObject($med,$sysDir){
		if(self::makeObj(__CLASS__)){
			return new globalConfig($med,$sysDir);
		}
		return false;
	}
	
	//Begin get methods
	public function getClassDebug(){
		return $this->classDebug;
	}
	
	//End get methods
	
	//Begin constructor
	private $error_warn = true;	  //set php.ini warnning all switch
	private $classDebug = false;   //Debug all created class
	
	private $mediator;
	
	private function globalConfig($med,$sysDir){
		if($this->error_warn){//in php.ini - display_errors should be On
			error_reporting  (E_ALL);// This will report all error
		}else{//in php.ini - display_errors should be Off
			error_reporting  (E_ERROR | E_WARNING | E_PARSE); // This will NOT report uninitialized variables
		}
		set_magic_quotes_runtime(0); // Disable magic_quotes_runtime
		
		$med->registerObject($this);
		$this->mediator = $med;
		
		$this->addSlashGpc();
		$this->initiolizeVar($sysDir);
		//$this->setGlobalLang();
	}
	//End constructor
	
	//Begin initiolize variables
	private $loginFail = false;
	private $systemTime;
	private $phpcoModule = array();
	private $phpcoGlobal = array();
	private $phpcoDir    = array();
	private $phpcoCookie = array();
	private $phpcoFile   = array();
	private $phpcoLDAP   = array();
	private $ldapOuAttr  = array();
	private $sqlUser     = array();
	private $sqlTable    = array();
	private $userType    = array();
	
	
	private function initiolizeVar($sysDir){
		//Set included in phpcos module name 
		$this->phpcoModule[0] = 'usermanager';
		
		$this->systemTime = time();
		
		$this->phpcoGlobal['machinename']        = '127.0.0.1';
		$this->phpcoGlobal['burl']        = $this->phpcoGlobal['machinename'] . '/phpco/';
		$this->phpcoGlobal['url']         = 'http://' . $this->phpcoGlobal['burl']; //Your domain name
		$this->phpcoGlobal['urls']        = 'https://' . $this->phpcoGlobal['burl'];
		$this->phpcoGlobal['phpfileext']  = '.php';
		$this->phpcoGlobal['htmlfileext'] = '.html';
		$this->phpcoGlobal['logcount']    = '10';  //The count number of that can try to log 
		$this->phpcoGlobal['locktime']    = '300'; //lock cookies for 5 minute.
		$this->phpcoGlobal['regperlasttime'] = '1800'; //regist person information keep last 30 minute.
		$this->phpcoGlobal['maxuid']      = '1000';//user id start from 1000
		$this->phpcoGlobal['maxperregistcount']   = 10;//Guest can regist 10 times.
		$this->phpcoGlobal['minregid']    = 100;   //Guest regist order id start from 100.
		$this->phpcoGlobal['minpasslen']  = 6;//password min length.
		$this->phpcoGlobal['maxvaluelen'] = 1500;//max input variable length.
		$this->phpcoGlobal['loginlasttime'] = 3; //guest next login must action after this seconds.
		$this->phpcoGlobal['guestregistlasttime'] = 10; //guest regist must action after this seconds.
		$this->phpcoGlobal['namelength']      = 100; //person or orgnization name can't longer then this value.
		$this->phpcoGlobal['editouperiod']    = 2592000; //a month. an ou can edited one time in this period .
		
		//ouregist config
		$this->phpcoGlobal['regoulasttime']  = '86400'; //regist ou information keep last 24 houres.
		$this->phpcoGlobal['maxouregistcount']   = 1;//Guest can regist ou 1 time.
		
		$this->phpcoCookie['name']   = 'nm114phpco';
		$this->phpcoCookie['path']   = '/';
		$this->phpcoCookie['domain'] = $this->phpcoGlobal['machinename']; //www.example.com
		$this->phpcoCookie['secure'] = '0';//When close browser,cookie deleted.
		$this->phpcoCookie['length'] = '1800'; 
		
		//guest target name
		$this->target['search']       = 'search';
		$this->target['login']          = 'login';
		$this->target['registper']   = 'registper';
		$this->target['registperok']   = 'registperok';
		$this->target['registou']     = 'registou';
		$this->target['registouok']   = 'registouok';
		
		//users  target name
		$this->target['logout']       = 'logout';
		
		//admin  target name
		$this->target['editper']       = 'editper';
		$this->target['editperok']   = 'editperok';
		$this->target['editou']         = 'editou';
		$this->target['editouok']    = 'editouok';
		$this->target['checkou']     = 'checkou';
		$this->target['checkounum']   = 'checkounum';
		$this->target['checkouok']      = 'checkouok';
		$this->target['checkper']         = 'checkper';
		$this->target['checkpernum']   = 'checkpernum';
		$this->target['checkperok']      = 'checkperok';
		$this->target['deleteper']  = 'deleteper';
		$this->target['deleteou']    = 'deleteou';
		
		//set ldap variables
		$this->phpcoLDAP['host'] = 'localhost';
		$this->phpcoLDAP['port']   = '389';
		$this->phpcoLDAP['base']   = 'dc=nm114,dc=com';
		$this->phpcoLDAP['dn']     = 'cn=root,' . $this->phpcoLDAP['base'];
		$this->phpcoLDAP['password'] = '7y8u9i';
		$this->phpcoLDAP['tls']    = false;
		
		//ldap server include schemas:core.schema,cosine.schema,inetorgperson.schema
		//ldap organization unit attributes name to display
		$this->phpcoGlobal['perobjectclass'] = 'inetorgperson';
		$this->phpcoGlobal['ouobjectclass']   = 'organizationalunit'; 
		
		$this->ldapOAttr[0]  = 'o';
		
		$this->ldapOuAttr[0] = 'ou';  
		$this->ldapOuAttr[1] = 'telephonenumber';
		$this->ldapOuAttr[2] = 'facsimiletelephonenumber'; //fax
		$this->ldapOuAttr[3] = 'postaladdress';
		$this->ldapOuAttr[4] = 'postalcode';
		$this->ldapOuAttr[5] = 'description';
				
		//organization person attributes name to display
		$this->ldapPAttr[0]  = 'cn';
		$this->ldapPAttr[1]  = 'givenname'; //lastname
		$this->ldapPAttr[2]  = 'sn';        //surname, firatname
		$this->ldapPAttr[3]  = 'uid';
		$this->ldapPAttr[4]  = 'userpassword';
		$this->ldapPAttr[5]  = 'telephonenumber';     //office phone number
		$this->ldapPAttr[6]  = 'facsimiletelephonenumber'; //fax
		$this->ldapPAttr[7]  = 'homephone';
		$this->ldapPAttr[8]  = 'mail';
		$this->ldapPAttr[9]  = 'mobile';
		$this->ldapPAttr[10] = 'roomnumber';
		$this->ldapPAttr[11] = 'postaladdress'; //office address
		$this->ldapPAttr[12] = 'postalcode';
		$this->ldapPAttr[13] = 'homepostaladdress';
		$this->ldapPAttr[14] = 'employeetype';
		$this->ldapPAttr[15] = 'photo';
		$this->ldapPAttr[16] = 'description';
				
		//Set database variable
		$this->sqlUser['DBtype'] = 'mysql';
		$this->sqlUser['user']   = 'phpco';
		$this->sqlUser['pass']   = '4e5r6t';
		$this->sqlUser['server'] = 'localhost';
		$this->sqlUser['db']     = 'phpco';
		
		$this->sqlTable['cookie']        = 'cookies';
		$this->sqlTable['loginfo']       = 'loginfo';
		$this->sqlTable['sysadmin']  = 'admin';
		$this->sqlTable['delusers']    = 'delusers';
		$this->sqlTable['users']          = 'users';
		$this->sqlTable['userregist']  = 'user_regist';
		$this->sqlTable['ouregist']     = 'ou_regist';
		$this->sqlTable['editou']         = 'editou';
		
		//Set dir name
		$this->phpcoDir['system'] = $sysDir;
		$this->phpcoDir['pear']   = $this->phpcoDir['system'] . 'pear/';
		$this->phpcoDir['phpcos'] = $this->phpcoDir['system'] . 'phpcos/';
				
		$this->phpcoDir['usermanager'] = $this->phpcoDir['phpcos'] . $this->phpcoModule[0] . '/';
		$this->phpcoDir['language']    = $this->phpcoDir['usermanager'] . 'language/';
		$this->phpcoDir['template']    = $this->phpcoDir['usermanager'] . 'template/';
		$this->phpcoDir['guest']       = $this->phpcoDir['template'] . 'guest/';
		$this->phpcoDir['user']        = $this->phpcoDir['template'] . 'user/';
		$this->phpcoDir['admin']       = $this->phpcoDir['template'] . 'admin/';
		
		$this->phpcoDir['users']       = $this->phpcoDir['usermanager'] . 'users/';
		
		//Set file name
		$this->phpcoFile['pear']     = 'pear';
		$this->phpcoFile['db']       = 'db';
		$this->phpcoFile['ldap']     = 'ldap';
		$this->phpcoFile['html']     = 'html';
		$this->phpcoFile['cookie']   = 'cookie';
		$this->phpcoFile['language']   = 'language';
		$this->phpcoFile['guestlang']  = 'guestlang';
		$this->phpcoFile['userlang']   = 'userlang';
		$this->phpcoFile['adminlang']  = 'adminlang';
		
		$this->phpcoFile['headtemp']   = 'head';
		$this->phpcoFile['hometemp']   = 'home';
		$this->phpcoFile['searchtemp'] = 'search';
		$this->phpcoFile['logintemp']  = 'login';
		$this->phpcoFile['foottemp']   = 'foot';
		
		//guest html temp file
		$this->phpcoFile['registpertemp']  = 'registper';
		$this->phpcoFile['registoutemp']   = 'registou';
		$this->phpcoFile['lawtemp']        = 'law';
		$this->phpcoFile['registperoktemp'] = 'registperok';
		$this->phpcoFile['checkpertemp']      = 'checkper';
		$this->phpcoFile['checkoutemp'] = 'checkou';
		
		$this->phpcoFile['alluser'] = 'alluser';
		$this->phpcoFile['guest']   = 'guest';
		$this->phpcoFile['user']    = 'user';
		$this->phpcoFile['admin']   = 'admin';
		
		//In 212 line; $this->phpcoDir['css']  = $this->phpcoFile['template'] . $tcook->getUserType() . 'css/phpco.css';
		
		//Set user type constants
		$this->userType['guest'] = 'G';
		$this->userType['user']  = 'U';
		$this->userType['admin'] = 'A';
		
		//Include common used classes
		$this->includeFiles($this->phpcoDir['pear'] . $this->phpcoFile['pear'] . $this->phpcoGlobal['phpfileext'] );
		
		//set database object
		$this->includeFiles($this->phpcoDir['pear'] . $this->phpcoFile['db']   . $this->phpcoGlobal['phpfileext'] );
		$this->setDbObject();
		
		//include user's language classes parent class
		$this->includeFiles($this->phpcoDir['language'] . $this->phpcoFile['language'] . $this->phpcoGlobal['phpfileext']);
		
		//set ldap object
		$this->includeFiles($this->phpcoDir['system'] . $this->phpcoFile['ldap']   . $this->phpcoGlobal['phpfileext'] );
		ldap::makeObject($this->mediator);
				
		//set cookie object
		$this->includeFiles($this->phpcoDir['usermanager'] . $this->phpcoFile['cookie'] . $this->phpcoGlobal['phpfileext']);
		
		if(isset($this->get['target'])){
			$tval = $this->get['target'];
		}else{
			$tval = 'empty';
		}
		cookie::makeObject($this->mediator,$tval);
		
		//set user object
		$this->includeFiles($this->phpcoDir['users'] . $this->phpcoFile['alluser'] . $this->phpcoGlobal['phpfileext']);
		$this->mediator->setUserObject();
		
		$this->setCssFile();
		
		$this->includeFiles($this->phpcoDir['pear'] . $this->phpcoFile['html'] . $this->phpcoGlobal['phpfileext'] );
		$this->setHtmlObject();
	}
	
	private function setCssFile(){
		$tcook = $this->mediator->getObject($this->mediator->getCookie());
		switch($tcook->getUserType()){
			case $this->getUserType('guest'):
				$tcss = 'guest';				
				break;
			case $this->getUserType('user'):
				$tcss = 'user';
				break;
			case $this->getUserType('admin'):
				$tcss = 'admin';
				break;
			default:
				$tcss = 'guest';
				
		}		
		$this->phpcoFile['css']  = $this->phpcoDir['template'] . $tcss . '/css/phpco.css';
		
	}
	
	public function getLoginFail (){
		return $this->loginFail ;
	}
	
	public function getPhpcoGlobal($val){
		return $this->phpcoGlobal[$val];
	}
	public function getTarget($val){
		return $this->target[$val];
	}
	public function getUserType($val){
		return $this->userType[$val];
	}	
	public function getGet($aname){
		if($aname == NULL){
			return $this->get; //return all get
		}
		if(isset($this->get[$aname])){
			return $this->get[$aname];
		}
		return null;
	}
	public function getPost($aname){
		if($aname == NULL){
			return $this->post; //return all post
		}
		if(isset($this->post[$aname])){
			return $this->post[$aname]; //return one post
		}
	}
	public function getCookie(){
		$tn = $this->phpcoCookie['name'];
		if(isset($this->cookie[$tn])){
			return $this->cookie[$tn];
		}
		return null;
	}
	public function getSystemTime(){
		return $this->systemTime;
	}
	
	public function getPhpcoLDAP($val = null){
		if($val == null){
			return $this->phpcoLDAP; //return all post
		}
		return $this->phpcoLDAP[$val];
	}
	
	public function getLdapOAttr($val = null){
		if($val == null){
			return $this->ldapOAttr; //return all ldapOAttr
		}
		return $this->ldapOAttr[$val];
	}
		
	public function getLdapOuAttr($val = null){
		if($val == null){
			return $this->ldapOuAttr; //return all ldapOuAttr
		}
		return $this->ldapOuAttr[$val];
	}
	
	public function getLdapPAttr($val = null){
		if($val == null){
			return $this->ldapPAttr; //return all ldapPAttr
		}
		return $this->ldapPAttr[$val];
	}
	
	public function getPhpcoCookie($val){
		return $this->phpcoCookie[$val];
	}
	
	public function getSqlTable($val){
		return $this->sqlTable[$val];
	}
	
	public function getPhpcoDir($dir){
		if(isset($this->phpcoDir[$dir])){
			return $this->phpcoDir[$dir];
		}
		
	}
	
	public function getPhpcoFile($file){
		if(isset($this->phpcoFile[$file])){
			return $this->phpcoFile[$file];
		}
		
	}
	//Begin add slash
	private $get     = array();
	private $post    = array();
	private $cookie  = array();
	
	private function addSlashGpc(){ //Addslashes in  GPC values
		$gpcvars = 0;
		for($i = 0; $i < 3; $i++){
			switch($i){
				case 0:
					$gpc = $_GET;
					break;
				case 1:
					$gpc = $_POST;
					break;
				case 2:
					$gpc = $_COOKIE;
					break;
			}
			if(get_magic_quotes_gpc()){
				if( is_array($gpc)){
					while( list($k, $v) = each($gpc)){
						if( is_array($gpcvars[$k])){
							while( list($k2, $v2) = each($gpc[$k])){
								$$gpcvars[$k][$k2] = trim(htmlspecialchars($v2, ENT_QUOTES));
							}
							@reset($gpc[$k]);
						}
						else{
							$gpc[$k] = trim(htmlspecialchars($v, ENT_QUOTES));
						}
					}
					@reset($gpc);
				}else{
					$gpc = trim(htmlspecialchars($gpc));	
				}
			}else{
				if(is_array($gpc)){
					while( list($k, $v) = each($gpc)){
						if( is_array($gpcvars[$k])){
							while(list($k2, $v2) = each($gpc[$k])){
								$$gpcvars[$k][$k2] = trim(htmlspecialchars(addslashes($v2), ENT_QUOTES));
							}
							@reset($gpc[$k]);
						}
						else{
							$gpc[$k] = trim(htmlspecialchars(addslashes($v), ENT_QUOTES));
						}
					}
					@reset($gpc);
				}else{
					$gpc = trim(htmlspecialchars(addslashes($gpc)));	
				}
			}
			switch($i){
				case 0:
					$this->get = $gpc;
					break;
				case 1:
					$this->post = $gpc;
					break;
				case 2:
					$this->cookie = $gpc;
					break;
			}
		}
	}
	//End add slash
	
	public function includeFiles($filename){
		if(file_exists($filename)){
			require_once($filename);
		}else{
			exit('<font color="#ff0000" size="6">Can\'t find the file ' . $filename . '!</font>');
		}
	}

	public function setLoginFail ($fail = false){
		return $this->loginFail = $fail ;
	}

	public function setGet($aname,$value){
		$this->get[$aname] = $value;
	}

	public function setPost($aname,$value){
		$this->post[$aname] = $value;
	}

	private function setDbObject(){
		$dsn = $this->sqlUser['DBtype'] . "://" . $this->sqlUser['user'] . ":" . $this->sqlUser['pass'] . "@" . $this->sqlUser['server']. "/" . $this->sqlUser['db'];
		$dbo = DB::connect($dsn,true); //second para for pconnect
		if (DB::isError($dbo)) {die ($dbo->getMessage()  . ' -- ' . __METHOD__ . ' ' .  __LINE__);}
		$dbo->setFetchMode(DB_FETCHMODE_ASSOC);
		$dbo->query("SET NAMES 'utf8'");
		
		$this->mediator->registerObject($dbo);
	}
	
	private function setHtmlObject(){
		$tcook  = $this->mediator->getObject($this->mediator->getCookie());
		
		$turl = '';
		switch($tcook->getUserType()){
			case $this->userType['guest']:
				$turl .= $this->phpcoDir['guest'];
				
				break;
			case $this->userType['user']:
				$turl .= $this->phpcoDir['user'];
				
				break;
			case $this->userType['admin']:
				$turl .= $this->phpcoDir['admin'];
				
				break;
			default:
				die("This user type does not exis -- config 391");
				
				break;
		}
		
		$html = new HTML_Template_ITX($turl);
		
		$this->mediator->registerObject($html);
	}
}
?>
