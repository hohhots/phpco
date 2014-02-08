<?php
	/***************************************************************************
		               mediator.php
		           -------------------
		begin         : 10-Apr-06
		copyright     : (C) 2006 The nm114.net brgd
	    email         : brgd@nm114.net
		
		$Id: mediator.php, 10-Apr-06 12:11:19 AM brgd 
		
	 ***************************************************************************/
class Mediator extends sysClass{
	private $config = 'globalConfig';
	private $cookie = 'cookie';
	private $db     = 'DB_mysql';
	private $ldap   = 'ldap';
	private $html   = 'HTML_Template_ITX'; 
	private $user;
	private $lang;
	
	//!!!!!!!!in this class must have only one property $objects
	
	public static function makeObject(){
		if(self::makeObj(__CLASS__)){
			return new Mediator();
		}
		return false;
	} 
	
	//begin register classes
	private $objects = array();
	
	public function registerObject($obj){
		if(!is_object($obj)){
			exit("$obj is not a object!" . ' -- ' . __CLASS__ . ' ' .  __LINE__);
		}
		$snum = self::$instanceNum;
		if(!in_array(get_class($obj),$snum)){
			$snum[count($snum)] = get_class($obj);
			self::$instanceNum = $snum;
		}
		if(in_array(get_class($obj),self::$instanceNum) &&
			!in_array($obj,$this->objects)){//if $obj exists in object num array and 
									//not exist in this registered object array.
			$this->objects[count($this->objects)] = $obj;  //add to the registered objects array
		}
	}
	
	public function getObject($oname){
		foreach ($this->objects as $name => $value) {
			if($oname == get_class($value)){
				return $value;
			}
		}
		exit("Get object error!" . ' -- ' . __CLASS__ . ' ' .  __LINE__);
	}
	
	public function setUserObject(){
		$tcon  = $this->getObject($this->config);
		$tco  = $this->getObject($this->cookie);
		$tdir = $tcon->getPhpcoDir('users');
		$tldir = $tcon->getPhpcoDir('language');
		$tex = $tcon->getPhpcoGlobal('phpfileext');
		
		switch($tco->getUserType()){
			case $tcon->getUserType('guest'):
				$this->user = 'guest';
				$this->lang = 'guestLang';
				$tcon->includeFiles($tdir . $tcon->getPhpcoFile('guest') . $tex);
				$tcon->includeFiles($tldir . $tcon->getPhpcoFile('guestlang') . $tex);
				guestLang::makeObject($this); //this must run before guest, guest will use $action variable
				guest::makeObject($this);
				break;
			case $tcon->getUserType('user'):
				$this->user = 'user';
				$this->lang = 'userLang';
				$tcon->includeFiles($tdir . $tcon->getPhpcoFile('user') . $tex);
				$tcon->includeFiles($tldir . $tcon->getPhpcoFile('userlang') . $tex);
				userLang::makeObject($this); //this must run before user, user will use $action variable
				user::makeObject($this);
				break;
			case $tcon->getUserType('admin'):
				$this->user = 'admin';
				$this->lang = 'adminLang';
				$tcon->includeFiles($tdir . $tcon->getPhpcoFile('admin') . $tex);
				$tcon->includeFiles($tldir . $tcon->getPhpcoFile('adminlang') . $tex);
				adminLang::makeObject($this); //this must run before user, user will use $action variable
				admin::makeObject($this);
				break;
			default:
				die("This user type does not exis!" . ' -- ' . __CLASS__ . ' ' .  __LINE__);
				break;
		}		
	}
	//End register classes
	
	//get variable public function
	public function getConfig(){ return $this->config;}
	public function getCookie(){ return $this->cookie;}
	public function getDb(){ return $this->db;}
	public function getLdap(){ return $this->ldap;}
	public function getHtml(){ return $this->html;}
	public function getUser(){ return $this->user;}
	public function getLang(){ return $this->lang;}
	
	public function getSkeyValue(){
		$tv = '';
		$tv = $this->getObject($this->config)->getGet('skey');
		$tv = $this->delSlashes($tv);
		
		return $tv;
	}
	
	public function delsymbol($val){
		$tv = stripslashes(htmlspecialchars_decode($val, ENT_QUOTES));

		return $tv;
	}
	
	public function delSlashes($val){
		$tv = stripslashes($val);
				
		return $tv;
	}
	
	public function ifTargetExist($target){
		$tval = true;
		if(($this->config->getGet('target')) != $target){
			$tval = false;
		}
		
		return $tval;
	}
	//Begin debug 
	public function debugClass(){
		if($this->getObject($this->config)->getClassDebug()){
			foreach ($this->objects as $name => $value) {
				$value->debug($value);
			}
		}
	}
	//End debug 
	
	//Begin constructor
	private function Mediator(){
		$this->registerObject($this);
	}
	//End constructor
	
	//run cookies
	public function setCookieVal($cookie){ //cookie 58,
		$tcon = $this->getObject($this->config);
		$tck  = $this->getObject($this->cookie);
		$db   = $this->getObject($this->db);
					
		$sql = "SELECT * FROM " . $tcon->getSqlTable('cookie') . " WHERE cookie = '$cookie'";
		$result = $db->query($sql);
		if (DB::isError($result)) {        
			die ($result->getMessage() . ' -- ' . __METHOD__ . ' ' .  __LINE__);
		}
		$tval = $result->fetchRow();
		
		$tck->setUserId($tval['userid']);
		$tck->setUserType($tval['usertype']);
		$tck->setCookieVal($tval['cookie']);
		$tck->setBrowser($tval['browser']);
		$tck->setUserIp($tval['ip']);
		$tck->setLaw($tval['law']);
	}
	
	public function ifLogin(){
		$tcon = $this->getObject($this->config);
		$tco = $this->getObject($this->cookie);
		
		if(($tco->getUserId() == 0) && ($tcon->getPost('userid') != null)){
			return true;
		}
		return false;
	}
	
	public function setFirstCookie(){
		$tc = $this->getObject($this->config);
		$db = $this->getObject($this->db);
				
		$ip = $this->getIp();
		$browser = $_SERVER['HTTP_USER_AGENT'];
		$cookie = md5(uniqid($ip . $tc->getSystemTime()));
		
		setcookie($tc->getPhpcoCookie('name'), $cookie, 0, $tc->getPhpcoCookie('path'), $tc->getPhpcoCookie('domain'), $tc->getPhpcoCookie('secure'));
		
		$sql = "INSERT INTO " . $tc->getSqlTable('cookie') . " (userid, cookie, usertype, start,browser,ip) 
				VALUES (0,'" . $cookie . "','G'," . $tc->getSystemTime() . ",'" . $browser . "','" . $ip . "')";
		$result = $db->query($sql);
		if (DB::isError($result)) {        
			die ($result->getMessage() . ' -- ' . __METHOD__ . ' ' .  __LINE__);
		}
		return $cookie;
	}
	
	public function userLogin($cookie){
		$tcon = $this->getObject($this->config);
		$ldap = $this->getObject($this->ldap);
		
		$this->unlockCookie();
		
		$info = $this->searchUid('(uid=' . $tcon->getPost('userid') . ')');
		if($info['count'] > 1){
			die("This user id is not nuique! -- " . ' -- ' . __METHOD__ . ' ' .  __LINE__);
		}
		if((@$_SERVER["HTTPS"] != 'on') ||
			!eregi("^[1-9]{1}[0-9]*$", $tcon->getPost('userid')) || 
			($tcon->getPost('passwd') == null) ||
			($info['count'] == 0) || 
			$this->cookieLocked($cookie) ||
			$this->loginTooQuick($cookie) ){ //login action must last some time
			$tcon->setLoginFail(true);//$this->sredirector($tredirct);
			$tcon->setGet('target',$tcon->getTarget('login'));
			return;
		}
		
		if($ldap->ifCanBind($info[0]['dn'],$tcon->getPost('passwd'))){
			$this->UpCookieInDB($cookie);
			$this->loginInfoDB($cookie);
		}else{
			$this->logCount($cookie);
			$tcon->setLoginFail(true);//$this->sredirector($tredirct);
			$tcon->setGet('target',$tcon->getTarget('login'));
		}			
	}
	
	private function unlockCookie(){
		//unlock cookie after $tcon->getPhpcoGlobal('locktime') minute
		$tcon = $this->getObject($this->config);
		$db = $this->getObject($this->db);
		
		$extlog = ($tcon->getSystemTime() - $tcon->getPhpcoGlobal('locktime'));
		$sql = "UPDATE " . $tcon->getSqlTable('cookie') . "
				SET logcount = '0',locktime = '0'" . " 
				WHERE locktime > 0 AND locktime < $extlog";
		$result = $db->query($sql);
		if (DB::isError($result)) {        
			die ($result->getMessage() . ' -- ' . __METHOD__ . ' ' .  __LINE__);
		}
	}
	
	private function logCount($cookie){ //this 217,
		$tcon = $this->getObject($this->config);
		$db = $this->getObject($this->db);
		
		$sql = "SELECT logcount FROM " . $tcon->getSqlTable('cookie') . " WHERE cookie = '$cookie'";
		$result = $db->query($sql);
		if (DB::isError($result)) {        
			die ($result->getMessage() . ' -- ' . __METHOD__ . ' ' .  __LINE__);
		}
		$tval = $result->fetchRow();
		
		if($tval['logcount'] < $tcon->getPhpcoGlobal('logcount')){
			$sql = "UPDATE " . $tcon->getSqlTable('cookie') . "
				SET logcount = logcount+1" . " 
				WHERE cookie  = '$cookie'";
		}else{
			$sql = "UPDATE " . $tcon->getSqlTable('cookie') . "
				SET locktime = " . $tcon->getSystemTime() . " 
				WHERE cookie  = '$cookie'";
		}
		$result = $db->query($sql);
		if (DB::isError($result)) {        
			die ($result->getMessage() . ' -- ' . __METHOD__ . ' ' .  __LINE__);
		}
	}
	
	private function cookieLocked($cookie){ // this 207,
		$tv = false;
		$tcon = $this->getObject($this->config);
		$db = $this->getObject($this->db);
		
		$sql = "SELECT count(*) AS lognum FROM " . $tcon->getSqlTable('cookie') . " WHERE cookie = '$cookie' AND locktime > 0";
		$result = $db->query($sql);
		if (DB::isError($result)) {        
			die ($result->getMessage() . ' -- ' . __METHOD__ . ' ' .  __LINE__);
		}
		$tval = $result->fetchRow();

		if($tval['lognum'] != 0){
			$tv = true;
		}
		
		return $tv;
	}
	
	private function loginTooQuick($cookie){ // guest must do next login after some time
		$tco = $this->getObject($this->config);
		$tck = $this->getObject($this->cookie);
		
		if( ($tco->getSystemTime() - $tck->getLastVisitTime()) > $tco->getPhpcoGlobal('loginlasttime')){
			return false;
		}
		return true;
	}
	
	public function GuestRegistTooQuick($cookie){
		$tco = $this->getObject($this->config);
		$tck = $this->getObject($this->cookie);

		if( ($tco->getSystemTime() - $tck->getLastVisitTime()) > $tco->getPhpcoGlobal('guestregistlasttime')){
			return false;
		}
		return true;
	}
	
	public function userLogout($cookie){
		$tc = $this->getObject($this->config);
		$db = $this->getObject($this->db);
		
		$ut = $tc->getUserType('guest');
		$sql =  "UPDATE " . $tc->getSqlTable('cookie') . "
				SET userid = 0,start = '" . $tc->getSystemTime() . "',usertype = '$ut' WHERE cookie  = '$cookie'";
		$result = $db->query($sql);
		if (DB::isError($result)) {        
			die ($result->getMessage() . ' -- ' . __METHOD__ . ' ' .  __LINE__);
		}
	}
	
	public function alreadyLoginAndToLogout($cookie){
		$tck = $this->getObject($this->cookie);
		$tcon = $this->getObject($this->config);
		
		$uid = $tck->getUserId();
		if(($uid != 0) && ($tcon->getGet('target') == 'logout')){
			return true;
		}
	}
	
	public function hasCookieAndExistInDB(){
		$tc = $this->getObject($this->config)->getCookie();
		if($tc != null){
			if($this->searchCookieInDB($tc)){
				return $tc;
			}
		}
		return;
	}
	
	private function searchCookieInDB($coval){
		$tc = $this->getObject($this->config);
		$db = $this->getObject($this->db);
		$tck = $this->getObject($this->cookie);
		
		$this->deleteExpiredCookies();
		
		$sql = "SELECT * FROM " . $tc->getSqlTable('cookie') . " WHERE cookie = '$coval'";
		$result = $db->query($sql);
		if (DB::isError($result)) {        
			die ($result->getMessage() . ' -- ' . __METHOD__ . ' ' .  __LINE__);
		}
		$tnum = $result->numRows();
		if($tnum == 0)	{
			return false;
		}
		
		$tval = $result->fetchRow();
		
		if($tnum == 1){
			if(($tval['browser'] != $_SERVER['HTTP_USER_AGENT']) ||
				 ($tval['ip'] != $this->getIp())){
				return false;
			}
		}
		$tck->setUserId($tval['userid']);
		$tck->setLastVisitTime($tval['start']);        //set last visit time
		
		//Refresh cookie start time in db
		$sql =  "UPDATE " . $tc->getSqlTable('cookie') . "
				SET start = '" . $tc->getSystemTime() . "' 
			WHERE cookie  = '$coval'";
		$result = $db->query($sql);
		if (DB::isError($result)) {        
			die ($result->getMessage() . ' -- ' . __METHOD__ . ' ' .  __LINE__);
		}		
				
		return true;
	}
	
	private function deleteExpiredCookies(){
		//delete expired session
		$tcon = $this->getObject($this->config);
		$db = $this->getObject($this->db);
		
		$ext = ($tcon->getSystemTime() - $tcon->getPhpcoCookie('length'));
		$sql = "DELETE FROM " . $tcon->getSqlTable('cookie') . " 
			WHERE start < $ext";
		$result = $db->query($sql);
		if (DB::isError($result)) {        
			die ($result->getMessage() . ' -- ' . __METHOD__ . ' ' .  __LINE__);
		}
	}
	
	private function UpCookieInDB($cookie){
		$tc = $this->getObject($this->config);
		$db = $this->getObject($this->db);
		
		$ut = $tc->getUserType('user');
		if($this->isAdminUser($tc->getPost('userid'))){
			$ut = $tc->getUserType('admin');
		}
		
		$sql =  "UPDATE " . $tc->getSqlTable('cookie') . "
			SET userid = " . $tc->getPost('userid') . ",usertype = '$ut',logcount = '0', locktime = '0' " . " 
			WHERE cookie  = '$cookie'";
		$result = $db->query($sql);
		if (DB::isError($result)) {        
			die ($result->getMessage() . ' -- ' . __CLASS__ . ' ' .  __LINE__);
		}
	}
	
	private function loginInfoDB(){
		$tc = $this->getObject($this->config);
		$db = $this->getObject($this->db);
		
		$sql =  "INSERT INTO " . $tc->getSqlTable('loginfo') . "
			 (userid,ip,time,sysinfo) Values (" . $tc->getPost('userid') . ",'" . $this->getIp() . "','" . $tc->getSystemTime() . "','"
			 .  $_SERVER['HTTP_USER_AGENT'] . "')";
		$result = $db->query($sql);
		if (DB::isError($result)) {        
			die ($result->getMessage() . ' -- ' . __CLASS__ . ' ' .  __LINE__);
		}
	}
	
	public function isAdminUser($uid){ //admin/deleteper //$this/UpCookieInDB
		$tc = $this->getObject($this->config);
		$db = $this->getObject($this->db);
		
		$sql = "SELECT COUNT(*) AS admin FROM " . $tc->getSqlTable('sysadmin') . " WHERE userid = '" . $uid . "'";
		$result = $db->query($sql);
		if (DB::isError($result)) {        
			die ($result->getMessage() . ' -- ' . __METHOD__ . ' ' .  __LINE__);
		}
		$tnum = $result->fetchRow();
		if($tnum['admin'] == 0)	{
			return false;
		}
		return true;
	}
	
	public function getLawState(){
		$lawstat = 'n';
		$tcon = $this->getObject($this->config);
		$tc = $this->getObject($this->cookie);
		$tlang = $this->getObject($this->lang);
		
		$tlaw = $tc->getLaw();
		if($tlaw == 'y'){
			$lawstat = 'y';
		}else{
			if($tcon->getPost('law') == 'law'){
				if($tcon->getPost('read') == 'yes'){
					$lawstat = 'y';
					$this->setLawState();
				}
			}
		}
		
		return $lawstat;
	}
	
	private function setLawState(){
		$tcon = $this->getObject($this->config);
		$tc = $this->getObject($this->cookie);
		$db = $this->getObject($this->db);
		
		$sql =  "UPDATE " . $tcon->getSqlTable('cookie') . "
			SET law='y' " . " 
			 WHERE cookie='" . $tc->getCookieVal() . "'";
		$result = $db->query($sql);
		if (DB::isError($result)) {        
			die ($result->getMessage() . ' -- ' . __METHOD__ . ' ' .  __LINE__);
		}
	
		return;
	}
	
	//End run cookies
	
	//DB function
	public function getRegOuAllAttribute($id,$fail = ''){
		$tattr = null;
		$tcon = $this->getObject($this->config);
		$db = $this->getObject($this->db);
		$tck = $this->getObject($this->cookie);
		
		$sql = "SELECT * FROM " . $tcon->getSqlTable('ouregist') . " WHERE orderid = '" . $id . "'";
		$result = $db->query($sql);
		if (DB::isError($result)) {        
			die ($result->getMessage() . ' -- ' . __METHOD__ . ' ' .  __LINE__);
		}
			
		$tval = $result->fetchRow();
		
		$ldapou = $tcon->getLdapOuAttr();
		$tlang   = $this->getObject($this->lang);
		
		if(!is_array($this->getObject($this->user)->existInLDAP($tval['pou'], true, true))){
			$fail = ' - (' . $tlang->getLangError('ounotexist') . ')';
		}
		$tattr[0][0] = $tlang->getOLdap($ldapou[0]);
		$tattr[0][1] = $tval[$ldapou[0]];
		$tattr[1][0] = $tlang->getLang('porgname');
		$tattr[1][1] = $tval['pou'] . ($fail ? " &nbsp;$fail" : '');
		$tattr[2][0] = $tlang->getOLdap($ldapou[1]); //phone
		$tattr[2][1] = $tval[$ldapou[1] . '1'] . ($tval[$ldapou[1] . '2'] ? '; ' . $tval[$ldapou[1] . '2'] : '') . 
								($tval[$ldapou[1] . '3'] ? '; ' . $tval[$ldapou[1] . '3'] : '')  . 
								($tval[$ldapou[1] . '4'] ? '; ' . $tval[$ldapou[1] . '4'] : '');
		$tattr[3][0] = $tlang->getOLdap($ldapou[2]); //fax
		$tattr[3][1] = ($tval[$ldapou[2] . '1'] ? $tval[$ldapou[2] . '1'] . '; ' : '') . $tval[$ldapou[2] . '2'];
		$tattr[4][0] = $tlang->getOLdap($ldapou[3]); //address
		$tattr[4][1] = $tval[$ldapou[3]];
		$tattr[5][0] = $tlang->getOLdap($ldapou[4]); //post code
		$tattr[5][1] = $tval[$ldapou[4]];
		$tattr[6][0] = $tlang->getOLdap($ldapou[5]); //description
		$tattr[6][1] = $tval[$ldapou[5]];
		
		return $tattr;
	}
	
	public function getRegPerAllAttribute($id,$fail = ''){
		$tattr = null;
		$tcon = $this->getObject($this->config);
		$db = $this->getObject($this->db);
		$tck = $this->getObject($this->cookie);
		
		$sql = "SELECT * FROM " . $tcon->getSqlTable('userregist') . " WHERE orderid = '" . $id . "'";
		$result = $db->query($sql);
		if (DB::isError($result)) {        
			die ($result->getMessage() . ' -- ' . __METHOD__ . ' ' .  __LINE__);
		}
			
		$tval = $result->fetchRow();
		
		$ldapp = $tcon->getLdapPAttr();
		$tlang   = $this->getObject($this->lang);
		
		if(!is_array($this->getObject($this->user)->existInLDAP($tval['pou'], true, true))){
			$fail = ' - (' . $tlang->getLangError('ounotexist') . ')';
		}
		$tattr[0][0] = $tlang->getPLdap($ldapp[0]); //cn
		$tattr[0][1] = $tval[$ldapp[0]];
		$tattr[1][0] = $tlang->getLang('organization') . '：';  //org
		$tattr[1][1] = $tval['pou'] . ($fail ? " &nbsp;$fail" : '');
		$tattr[2][0] = $tlang->getPLdap($ldapp[5]);  //phone
		$tattr[2][1] = $tval[$ldapp[5] . '1'] . ($tval[$ldapp[5] . '2'] ? '; ' . $tval[$ldapp[5] . '2'] : '') . 
								($tval[$ldapp[5] . '3'] ? '; ' . $tval[$ldapp[5] . '3'] : '')  . 
								($tval[$ldapp[5] . '4'] ? '; ' . $tval[$ldapp[5] . '4'] : '');
		$tattr[3][0] = $tlang->getPLdap($ldapp[6]); //fax
		$tattr[3][1] = ($tval[$ldapp[6] . '1'] ? $tval[$ldapp[6] . '1'] . '; ' : '') . $tval[$ldapp[6] . '2'];
		$tattr[4][0] = $tlang->getPLdap($ldapp[7]); //homephone
		$tattr[4][1] = $tval[$ldapp[7]];
		$tattr[5][0] = $tlang->getPLdap($ldapp[8]); //mail
		$tattr[5][1] = $tval[$ldapp[8]];
		$tattr[6][0] = $tlang->getPLdap($ldapp[9]); //mobile
		$tattr[6][1] = $tval[$ldapp[9]];
		$tattr[7][0] = $tlang->getPLdap($ldapp[10]); //room number
		$tattr[7][1] = $tval[$ldapp[10]];
		$tattr[8][0] = $tlang->getPLdap($ldapp[11]); //address
		$tattr[8][1] = $tval[$ldapp[11]];
		$tattr[9][0] = $tlang->getPLdap($ldapp[12]); //post code
		$tattr[9][1] = $tval[$ldapp[12]];
		$tattr[10][0] = $tlang->getPLdap($ldapp[13]); //home address
		$tattr[10][1] = $tval[$ldapp[13]];
		$tattr[11][0] = $tlang->getPLdap($ldapp[14]); //employtype
		$tattr[11][1] = $tval[$ldapp[14]];
		$tattr[12][0] = $tlang->getPLdap($ldapp[16]); //description
		$tattr[12][1] = $tval[$ldapp[16]];
		
		return $tattr;
	}
	
	public function insertInEditOuDB($oldname,$newname){
		$tcon = $this->getObject($this->config);
		$tck = $this->getObject($this->cookie);
		$db = $this->getObject($this->db);
		
		$tuid   = $tck->getUserId();
		$ttime = $tcon->getSystemTime();
		
		$sql =  "INSERT INTO " . $tcon->getSqlTable('editou') . 
				" ( userid , time , oldname , newname) VALUES (" . 
				$tuid . "," . 
				$ttime . "," . 
				"'" . $oldname . "'," . 
				"'" . $newname . "')";
		$result = $db->query($sql);
		if (DB::isError($result)) {        
			die ($result->getMessage()  . ' -- ' . __METHOD__ . ' ' .  __LINE__);
		}
	}
	
	public function ouAlreadyEdited($orgname){ //ou  can edited one time in a month.
		$tcon = $this->getObject($this->config);
		$db = $this->getObject($this->db);
		$tck = $this->getObject($this->cookie);
		
		$time = ($tcon->getSystemTime() - $tcon->getPhpcoGlobal('editouperiod'));
		$sql = "SELECT * FROM " . $tcon->getSqlTable('editou') . " WHERE newname = '" . $orgname . "' AND time > $time";
	$result = $db->query($sql);
		if (DB::isError($result)) {        
			die ($result->getMessage() . ' -- ' . __METHOD__ . ' ' .  __LINE__);
		}
		$tval = $result->numRows();
		if($tval > 0){
			return true;
		}
		return false;
	}
	
	//End DB function
	
	//get client IP information
	public function decode_Ip($int_ip)
	{
		$hexipbang = explode('.', chunk_split($int_ip, 2, '.'));
		return hexdec($hexipbang[0]). '.' . hexdec($hexipbang[1]) . '.' . hexdec($hexipbang[2]) . '.' . hexdec($hexipbang[3]);
	}	
	
	public function getIp()
	{
		$client_ip = ( !empty($_SERVER['REMOTE_ADDR']) ) ? $_SERVER['REMOTE_ADDR'] : ( ( !empty($HTTP_ENV_VARS['REMOTE_ADDR']) ) ? $HTTP_ENV_VARS['REMOTE_ADDR'] : getenv('REMOTE_ADDR') );
		return  $this->encode_Ip($client_ip);
	}
	
	private function encode_Ip($dotquad_ip)
	{
		$ip_sep = explode('.', $dotquad_ip);
		return sprintf('%02x%02x%02x%02x', $ip_sep[0], $ip_sep[1], $ip_sep[2], $ip_sep[3]);
	}
	
	
	//ldap functions
	public function addAnLdapEntry($dn,$info){
		$ld = $this->getObject($this->ldap);
		$ld->addAnEntry($dn,$info);
	}
	
	public function delAnLdapEntry($dn){
		$ld = $this->getObject($this->ldap);
		$ld->delAnEntry($dn);
	}
	
	public function modifyAnLdapEntry($dn,$info){
		$ld = $this->getObject($this->ldap);
		return $ld->modifyAnEntry($dn,$info);
	}
	
	public function userIdExist($uid){
		$tc = $this->getObject($this->config);
		$ld = $this->getObject($this->ldap);
		
		$rs = $ld->search($tc->getPhpcoLDAP('base'),$uid);//echo($rs['count']);exit();
		if($rs['count'] == 0){
			return false;
		}
		return true;
	}
	
	public function connectLdap(){
		$tc = $this->getObject($this->config);
				
		$tl = ldap_connect($tc->getPhpcoLDAP('host'), $tc->getPhpcoLDAP('port'))
			or	die("Can't connect to ldap server!" . ' -- ' . __METHOD__ . ' ' .  __LINE__);
			
		return $tl;
	}
	
	public function rootBindLdap($ldapc){
		$tc = $this->getObject($this->config);
		$ld = $this->getObject($this->ldap);
		
		$ld->setRootBind($tc->getPhpcoLDAP('dn'),$tc->getPhpcoLDAP('password'));
	}
	
	public function searchUid($uid){
		$tc = $this->getObject($this->config);
		$ld = $this->getObject($this->ldap);
		
		$rs = $ld->search($tc->getPhpcoLDAP('base'),$uid);
		return $rs;
	}
	
	public function searchAllUid(){
		$tc = $this->getObject($this->config);
		$ld = $this->getObject($this->ldap);
		
		$dn = $tc->getPhpcoLDAP('base');
		$filter="(uid=*)";
		$these = array("uid");
		
		$rs = $ld->search($dn,$filter,$these);
		return $rs;
	}
	
	public function searchALdapOu($val){
		$tcon = $this->getObject($this->config);
		$tlang = $this->getObject($this->getLang());
		$ld = $this->getObject($this->ldap);
		
		$to = $tlang->getLdapo(0);
		if($val == $tlang->getLang('hhht')){
			$to = $tlang->getLdapr(0);
		}
		$val = $to . '=' . $val . '';
		$rs = $ld->search($tcon->getPhpcoLDAP('base'),$val);
		return $rs;
	}
	
	public function searchALdapPer($val){
		$tcon = $this->getObject($this->config);
		$tlang = $this->getObject($this->getLang());
		$ld = $this->getObject($this->ldap);
		
		$val = $tlang->getLdapp(3) . '=' . $val;
		$rs = $ld->search($tcon->getPhpcoLDAP('base'),$val);
		return $rs;
	}
	
	public function ldapHasPerCnWithDn($cn,$dn){
		$tcon = $this->getObject($this->config);
		$tlang = $this->getObject($this->getLang());
		$ld = $this->getObject($this->ldap);
		
		$val = $tlang->getLdapp(0) . '=' . $cn;
		$rs = $ld->search($dn,$val);
		if($rs['count'] > 0){
			return true;
		}
		return false;
	}
	
	public function ldapHasOuWithDn($ou,$dn){
		$tcon = $this->getObject($this->config);
		$tlang = $this->getObject($this->getLang());
		$ld = $this->getObject($this->ldap);
		
		$val = $tlang->getLdapo(0) . '=' . $ou;
		$rs = $ld->search($dn,$val);
		if($rs['count'] > 0){
			return true;
		}
		return false;
	}
	
	public function ldapHasPerCnJustWithDn($cn,$dn){
		$tcon = $this->getObject($this->config);
		$tlang = $this->getObject($this->getLang());
		$ld = $this->getObject($this->ldap);
		
		$val = $tlang->getLdapp(0) . '=' . $cn;
		$rs = $ld->ldapList($dn,$val);
		if($rs['count'] > 0){
			return true;
		}
		return false;
	}
	
	public function searchAllLdapOu($val){
		$tc = $this->getObject($this->config);
		$tlang = $this->getObject($this->getLang());
		$ld = $this->getObject($this->ldap);
		
		$val = $tlang->getLdapo(0) . '=*' . $val . '*';
		$these = array($tlang->getLdapo(0));
		$rs = $ld->search($tc->getPhpcoLDAP('base'),"$val",$these);
		return $rs;
	}
	
	public function searchAllLdapOuAll($key){
		$tc = $this->getObject($this->config);
		$tlang = $this->getObject($this->getLang());
		$ld = $this->getObject($this->ldap);
		
		$tallldapo = $tlang->getLdapo(-1);
		$val = "(&(objectclass=organizationalunit)(|";
		for($i = 0; $i < count($tallldapo); $i++){
			$val .= '(' . $tallldapo[$i] . '=*' . $key . '*)';
		}
		$val .= '))';
		
		$these = array($tlang->getLdapo(0));
		$rs = $ld->search($tc->getPhpcoLDAP('base'),"$val",$these);
		return $rs;
	}
	
	public function searchAllLdapPerAll($perdn,$key){
		$tc = $this->getObject($this->config);
		$tlang = $this->getObject($this->getLang());
		$ld = $this->getObject($this->ldap);
		
		$tallldapp = $tlang->getLdapp(-1);
		$val = "(&(objectclass=inetorgperson)(|";
		for($i = 0; $i < count($tallldapp); $i++){
			if(($tallldapp[$i] != 'uid') && ($tallldapp[$i] != 'userpassword') && ($tallldapp[$i] != 'photo')){
			$val .= '(' . $tallldapp[$i] . '=*' . $key . '*)';
			}
		}
		$val .= '))';
			
		$these = array($tlang->getLdapp(0),$tlang->getLdapp(3));
		
		$rs = $ld->search($tc->getPhpcoLDAP('base'),$val,$these);
		return $rs;
		/**
		if(count(explode(',',$perdn['dn'])) == 3){
			$rs = $ld->search($tc->getPhpcoLDAP('base'),$val,$these);
			return $rs;
		}
		
		if(count(explode(',',$perdn['dn'])) > 3){
			$dn = substr($perdn['dn'],(strpos($perdn['dn'],',')+1));
			$rs = $ld->ldapList($dn,$val,$these);
			return $rs;
		}
		**/
	}
		
	public function getSubOuEntries($dn){
		$ld = $this->getObject($this->ldap);
		
		$tsuben = $ld->getAnEntryOuList($dn);
		
		if(count($tsuben) == 0){
			$tsuben[0] = '';
		}
		
		return $tsuben;
	}
	
	public function getSubPerEntries($dn){
		$ld = $this->getObject($this->ldap);
		
		$tsuben = $ld->getAnEntryPerList($dn);
		
		if(count($tsuben) == 0){
			$tsuben[0] = '';
		}
		
		return $tsuben;
	}

	public function ouHasSubEntry($dn){
		$ld = $this->getObject($this->ldap);
		if($ld->ouHasSubEntry($dn)){
			return true;
		}
		return false;
	}
	
	//redirect
	public function redirector($url = ""){
		$tc = $this->getObject($this->config);
		header('Location:' . $tc->getPhpcoGlobal('url') . $url);
		exit();
	}
	
	public function sredirector($url = ""){
		$tc = $this->getObject($this->config);
		header('Location:' . $tc->getPhpcoGlobal('urls') . $url);
		exit();
	}
	
	//check values
	public function invalidName($name){
		if(ereg("[[:punct:]]",$name)){
			return true;
		}
		return false;
	}
	public function isNumber($val){
		if(ereg("^[1-9]{1}[[:digit:]]*$",$val)){ //number must have none zero head
			return true;
		}
		return false;
	}
	
	public function isPhoneNumber($val){
		if(ereg("^[1-9]{1}[[:digit:]]{6,}$",$val)){ //phone number must have 7 or up digit
			return true;
		}
		return false;
	}
	public function isPassword($val){
		if($this->getObject($this->cookie)->getUserType() == $this->getObject($this->config)->getUserType('guest')){
			if(!eregi("^[[:alnum:]]{6,}$",$val)){ //password number must have 6 or up digit
				return false;
			}
		}else{ //if is admin
			if($val != ''){
				if(!eregi("^[[:alnum:]]{6,}$",$val)){ //password number must have 6 or up digit
					return false;
				}
			}
		}
		return true;
	}
	public function isMail($val){
		if(eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$",$val)){
			return true;
		}
		return false;
	}
	public function isMobile($val){
		if(ereg("^[[:digit:]]{7,}$",$val)){ //mobile or xiao ling tong
			return true;
		}
		return false;
	}
	public function isRoomnum($val){
		if(ereg("^[[:alnum:]]+$",$val)){ 
			return true;
		}
		return false;
	}
	public function isPostalcode($val){
		if(ereg("^[0]{1}[1]{1}[[:digit:]]{4}$",$val)){ 
			return true;
		}
		return false;
	}
	//html function
	public function displayHtml(){
		header('Content-Type: text/html; charset="UTF-8"');
		$tu = $this->getObject($this->user);
		
		echo($tu->displayHead() . $tu->displayBody() . $tu->displayFoot());
	}
}
?>
