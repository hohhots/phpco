<?php
	/***************************************************************************
		               guest.php
		           -------------------
		begin         : 19-Apr-06
		copyright     : (C) 2006 The nm114.net brgd
	    email         : brgd@nm114.net
		
		$Id: guest.php, 19-Apr-06 11:42:29 PM brgd 
		
	 ***************************************************************************/
class guest extends allUser{
	
	public static function makeObject($med){
		if(self::makeObj(__CLASS__)){
			return new guest($med);
		}
		return false;
	}
	
	//Constructor
	private function guest($med){
		$this->setMediator($med);
	}
	
	//display html
	public function displayHead(){
		$this->setTObject();
		
		$this->setOrgValues();  //set information of person will display and it's org information
		$this->template->loadTemplateFile($this->config->getPhpcoFile('headtemp') . $this->config->getPhpcoGlobal('htmlfileext'));
		
		$torg = $this->getOrgValuesOrg();
		$this->template->setCurrentBlock("head1");
 			$this->template->setVariable(array(
 				"waitfor" => $this->lang->getLang('waitfor'),
 				"title" => $this->lang->getLang('title'),
 				"css"   => $this->config->getPhpcoFile('css'),
 				"home"  => $this->setHeads('home'),
 				"log"   => $this->setHeads($this->config->getTarget('login')),
				"date"  => '<span class="date">' . $this->lang->getLang('date') . '</span>',
				"skeyv"    => $this->mediator->getSkeyValue(), 
				"search"     => $this->config->getTarget('search'),
 				"searchb1" => $this->lang->getActionName('search'),
 				"org"      => $torg,
			));
 		
		$this->template->parseCurrentBlock("head1"); 
		
		return $this->template->show();
	}
	
	public function displayBody(){
		$torg  = $this->getOrgValuesOrg();
		$tkey  = $this->config->getGet('skey');
		$tname = $this->config->getGet('target');
		
		//if use SSL
		if(($tname == $this->config->getTarget('login')) || 
			($tname == $this->config->getTarget('registper')) ||
			($tname == $this->config->getTarget('registperok')) ||
			($tname == $this->config->getTarget('registou')) || 
			($tname == $this->config->getTarget('registouok'))){
			if(@$_SERVER["HTTPS"] != 'on'){
				$this->mediator->sredirector('?target=' . $tname . '&org=' . $torg);
			}
		}else{
			if(@$_SERVER["HTTPS"] == 'on'){
				if($tname == 'search'){
					$this->mediator->redirector('?target=' . $tname . '&skey=' . $tkey);
				}
				$this->mediator->redirector('?target=' . $tname . '&org=' . $torg);
			}
		}
		//end if use SSL
	
		if(method_exists($this,$tname)){  //In object,Can't use function_exists()!!
			return $this->$tname();
		}else{
			return $this->home();
		}
	}

	public function displayFoot(){
		$this->template->loadTemplateFile($this->config->getPhpcoFile('foottemp') . $this->config->getPhpcoGlobal('htmlfileext'));
		
		$this->template->setCurrentBlock("foot1");
 			$this->template->setVariable(array(
 				"temp" => '',
			));
 		
		$this->template->parseCurrentBlock("foot1"); 
		
		return $this->template->show();
	}
	
	protected function home(){
		$tdes = '';$tregist = '';$tregistou = '';$tregistper = '';
		$torg = $this->getOrgValuesOrg();
		$tndn = $this->orgValues;

		if(strtolower($tndn['dn']) == $this->config->getPhpcoLDAP('base')){
			$tdes        = $this->lang->getLang('descriptionguest');
			$torgname[0] = $this->lang->getLang('position') . '&nbsp;' . $this->lang->getLang('hhht');
			$tattri[0]   = null;
			$tregistou  = '<a href="?target=' . $this->config->getTarget('registou') . '&org=' . urlencode($this->lang->getLang('hhht')) . '">' . $this->lang->getActionName('registou') . '</a>';
			$tregistper  = '<a href="?target=' . $this->config->getTarget('registper') . '&org=' . urlencode($this->lang->getLang('hhht')) . '">' . $this->lang->getActionName('registper') . '</a>';
			$tsubent  = $this->mediator->getSubOuEntries($this->config->getPhpcoLDAP('base'));
		}else{
			$torgname = $this->getAllParentOrg($tndn['dn'],$this->config->getPhpcoLDAP('base'));
			$tattri   = $this->getAllAttribute($tndn);
			$tregistou  = '<a href="?target=' . $this->config->getTarget('registou') . '&org=' . urlencode($torg) . '">' . $this->lang->getActionName('registou') . '</a>';
			$tregistper  = '<a href="?target=' . $this->config->getTarget('registper') . '&org=' . urlencode($torg) . '">' . $this->lang->getActionName('registper') . '</a>';
			$tsubent  = $this->mediator->getSubOuEntries($tndn['dn']);
		}
		
		$this->template->loadTemplateFile($this->config->getPhpcoFile('hometemp') . $this->config->getPhpcoGlobal('htmlfileext'));
		$this->template->setCurrentBlock("body1");
			$this->template->setVariable(array(
 				"description" => $tdes,
 				"registou"    => $tregistou,
 				"registper"   => $tregistper,
			));
			
			$this->displayParentOuName('body11',$torgname);
			
			$this->template->setCurrentBlock("body12");
				if($tsubent[0] == ''){
						$this->template->setVariable(array(
 							"subentries"  => $this->lang->getLang('none'),
						));
				}else{
					for($i = 0;$i < count($tsubent); $i++){
						if($tsubent[$i] != ''){
							$this->template->setVariable(array(
 								"subentries"  => '<li class="hiconou">&nbsp;</li><li>' . $tsubent[$i] . '</li>',
							));
						}
					
 						$this->template->parseCurrentBlock("body12");
					}
				}
			$this->displayAttribute("body13",'',$tattri);
		$this->template->parseCurrentBlock("body1"); 
		
		return $this->template->show();
	}

	private function login(){
		if($this->config->getLoginFail() == true){
			$tfail =  '<div id="attentionmenu">' . $this->lang->getLang('fail') . '</div>';
		}
		$torg = $this->getOrgValuesOrg();
		$this->template->loadTemplateFile($this->config->getPhpcoFile('logintemp') . $this->config->getPhpcoGlobal('htmlfileext'));
		$this->template->setCurrentBlock("login1");
			$this->template->setVariable(array(
				"fail"    => @$tfail,
				"login"   => $this->lang->getLang('login'),
				"action"  => '?org=' . urlencode($torg),
 				"id"      => $this->lang->getLang('userid'),
 				"pass"    => $this->lang->getLang('pass'),
 				"back"    => $this->lang->getLang('back'),
 				"loginto" => $this->lang->getLang('login'),
 				"actionb" => '?org=' . urlencode($torg),
 			));
		
		$this->template->parseCurrentBlock("login1"); 
		
		return $this->template->show();
	}
	private function help(){
		
	}
	
	protected function search(){
		$keyw = '';
		$keyw = $this->config->getGet('skey');
		if($keyw == ''){
			return $this->home();
		}
		
		$tens   = $this->mediator->searchAllLdapOu($keyw);
		$tresult = array();
		if($tens['count'] == 0){
			$tresult[0] = $this->lang->getLang('nomatch');
		}else{
			$attri = $this->lang->getLdapo(0);
			for ($i = 0; $i < $tens["count"]; $i++) {
    			$tresult[$i] = '<li class="num">' . ($i+1) . ':</li><li class=content><a href="?org=' . urlencode($tens[$i][$attri][0]) . '">' . $tens[$i][$attri][0] . '</a></li>';
			}
		}
		
		$this->template->loadTemplateFile($this->config->getPhpcoFile('searchtemp') . $this->config->getPhpcoGlobal('htmlfileext'));
		$this->template->setCurrentBlock("body1");
			$this->template->setVariable(array(
 				"searchtitle" => $this->lang->getLang('SearchTitle'),
 			));
			$this->template->setCurrentBlock("body11");
				for($i = 0;$i < count($tresult); $i++){
 					$this->template->setVariable(array(
 						"result" => $tresult[$i],
					));
 					$this->template->parseCurrentBlock("body11");
				}
		$this->template->parseCurrentBlock("body1"); 
		return $this->template->show();
	}	
		
	protected function refreshPerWeb($cn){ //if press refresh button in web browser
		$sql = "SELECT COUNT(*) AS mnum FROM " . $this->config->getSqlTable('userregist') . 
			" WHERE cookie='" . $this->cookie->getCookieVal() . "' AND " . 
			" cn='" . $cn . "' AND pou='" . $this->orgValues['dn'] . "'";
		$result = $this->db->query($sql);
		if (DB::isError($result)) {        
			die ($result->getMessage() . ' -- ' . __METHOD__ . ' ' .  __LINE__);
		}
		
		$tval = $result->fetchRow();
		if($tval['mnum'] > 0){
			return true;
		}
		return false;
	}
	
	private function ifCanRegistPer(){
		$torg = $this->getOrgValuesOrg();
		if(($this->existInLDAP($torg,true,true) == null)){
			$this->mediator->redirector();
		}
		
		if(($this->mediator->getLawState()) == 'n'){
			if($this->config->getPost('law') == 'law'){
				if($this->config->getPost('read') == 'no'){
					$this->mediator->redirector('?org=' . urlencode($torg),false);
				}
			}
			return true; //display law page
		}
		return false;
	}
	
	private function displayLaw(){
		$torg = $this->getOrgValuesOrg();
		$this->template->loadTemplateFile($this->config->getPhpcoFile('lawtemp') . $this->config->getPhpcoGlobal('htmlfileext'));
		$this->template->setCurrentBlock("body1");
			$this->template->setVariable(array(
 				"pouname"      => $this->lang->getLang('registper') . '：',
 				"action"       => '?target=' . $this->config->getGet('target') . '&org=' . urlencode($torg),
 				"law"          => $this->lang->getLang('law'),
 				"lawcont"      => $this->lang->getLang('lawcont'),
 				"agree"        => $this->lang->getLang('agree'),
 				"decline"      => $this->lang->getLang('decline'),
			));
		$this->template->parseCurrentBlock("body1"); 
		
		return $this->template->show();
	}
			
	protected function insInUserTable($ou){
		$tcon = $this->config;
		$ldapp = $tcon->getLdapPAttr();
		$maxorder = $this->config->getPhpcoGlobal('minregid');
		
		$dn = $this->orgValues['dn'];
				
		$sql = "SELECT MAX(orderid) as ord FROM " . $this->config->getSqlTable('userregist');
		$result = $this->db->query($sql);
		if (DB::isError($result)) {        
			die ($result->getMessage() . ' -- ' . __METHOD__ . ' ' .  __LINE__);
		}
		$tval = $result->fetchRow();
		if($tval['ord'] != null){
			$maxorder = $tval['ord'] + 1;
		}
		
		$sql =  "INSERT INTO " . $this->config->getSqlTable('userregist') . 
				" ( orderid , cookie , cn , pou , userpassword , telephonenumber1 , telephonenumber2 , telephonenumber3 , telephonenumber4 , facsimiletelephonenumber1 , facsimiletelephonenumber2 , homephone , mail , mobile , roomnumber , postaladdress , postalcode , homepostaladdress , employeetype , description , regtime ) VALUES (" . 
				"'" . $maxorder . "'," . //orderid
				" '" . $this->cookie->getCookieVal() . "'," . //cookie
				" '" . $this->userinfo[$ldapp[0]] . "'," . //cn
				" '" . $ou . "'," . //parent org
				" '" . $this->userinfo[$ldapp[4]] . "'," . //userpassword
				" '" . $this->userinfo[$ldapp[5]][0] . "'," . //telephonenumber1
				" '" . @$this->userinfo[$ldapp[5]][1] . "'," . //telephonenumber2
				" '" . @$this->userinfo[$ldapp[5]][2] . "'," . //telephonenumber3
				" '" . @$this->userinfo[$ldapp[5]][3] . "'," . //telephonenumber4
				" '" . @$this->userinfo[$ldapp[6]][0] . "'," . //facsimiletelephonenumber1
				" '" . @$this->userinfo[$ldapp[6]][1] . "'," . //facsimiletelephonenumber2
				" '" . @$this->userinfo[$ldapp[7]] . "'," . //homephone
				" '" . @$this->userinfo[$ldapp[8]] . "'," . //mail
				" '" . @$this->userinfo[$ldapp[9]] . "'," . //mobile
				" '" . @$this->userinfo[$ldapp[10]] . "'," . //roomnumber
				" '" . @$this->userinfo[$ldapp[11]] . "'," . //postaladdress
				" '" . @$this->userinfo[$ldapp[12]] . "'," . //postalcode
				" '" . @$this->userinfo[$ldapp[13]] . "'," . //homepostaladdress
				" '" . @$this->userinfo[$ldapp[14]] . "'," . //employeetype
				" '" . @$this->userinfo[$ldapp[16]] . "'," . //description
				" '" . $this->config->getSystemTime() . "'" . ")";//regtime
		$result = $this->db->query($sql);
		if (DB::isError($result)) {        
			die ($result->getMessage() . ' -- ' . __METHOD__ . ' ' .  __LINE__);
		}
		
		$this->resetLawState();
				
		return $maxorder;
	}
	
	private function reachMaxPerRegistCount(){
		$maxcount = $this->config->getPhpcoGlobal('maxperregistcount');
		
		$sql = "SELECT COUNT(*) AS mnum FROM " . $this->config->getSqlTable('userregist') . 
			" WHERE cookie='" . $this->cookie->getCookieVal() . "'";
		$result = $this->db->query($sql);
		if (DB::isError($result)) {        
			die ($result->getMessage() . ' -- ' . __METHOD__ . ' ' .  __LINE__);
		}
		
		$tval = $result->fetchRow();
		
		if($maxcount > $tval['mnum']){
			return false;
		}else{
			return true;
		}
	}
		
	protected function checkRegistPer(){
		$this->cleanUserRegistTable(); //clean timeout user info in userregist table
		
		if($this->reachMaxPerRegistCount()){
			return $this->displayMaxCountError('per'); //every guest just can regist 10 user.
		}
		
		if($this->ifCanRegistPer()){
			return $this->displayLaw();
		}
	}

	private function displayMaxCountError($name){
		//display ok page
		switch($name){
			case 'per':
				$pouname = $this->lang->getLang('registper') . '：';
 				$ok      = $this->lang->getLangError('maxpercounterr');
				break;
			case 'ou':
				$pouname = $this->lang->getLang('registou') . '：';
 				$ok      = $this->lang->getLangError('maxoucounterr');
				break;
			default:
				echo('Error! guest->displayMaxCountError');
		}
		
		$actionb = '?org=' . urlencode($this->getOrgValuesOrg());
		$back    = $this->lang->getLang('submit');
		
		return $this->displayokpage($pouname,$actionb,$ok,$back);
	}
	
	protected function displayokpage($pouname,$action,$ok,$back){
		$this->template->loadTemplateFile($this->config->getPhpcoFile('registperoktemp') . $this->config->getPhpcoGlobal('htmlfileext'));
		$this->template->setCurrentBlock("body1");
			$this->template->setVariable(array(
 				"pouname"      => $pouname,
 				"actionb"      => $action,
 				"ok"           => $ok,
 				"back"         => $back,
			));
		$this->template->parseCurrentBlock("body1"); 
		
		return $this->template->show();
	}

	protected function checkRegistOu(){
		$this->cleanOuRegistTable(); //clean timeout org  info in ou-regist table
		if($this->reachMaxOuRegistCount()){
			return $this->displayMaxCountError('ou'); //every guest just can regist 1 ou.
		}
		if($this->ifCanRegistOu()){
			return $this->displayLaw();
		}
	}
	
	private function reachMaxOuRegistCount(){
		$maxcount = $this->config->getPhpcoGlobal('maxouregistcount');
		
		$sql = "SELECT COUNT(*) AS mnum FROM " . $this->config->getSqlTable('ouregist') . 
			" WHERE cookie='" . $this->cookie->getCookieVal() . "'";
		$result = $this->db->query($sql);
		if (DB::isError($result)) {        
			die ($result->getMessage() . ' -- ' . __METHOD__ . ' ' .  __LINE__);
		}
		
		$tval = $result->fetchRow();
		
		if($maxcount > $tval['mnum']){
			return false;
		}else{
			return true;
		}
	}
	
	private function ifCanRegistOu(){
		$torg = $this->getOrgValuesOrg();
		if(($this->existInLDAP($torg,true,true) == null)){
			$this->mediator->redirector();
		}
		
		if(($this->mediator->getLawState()) == 'n'){
			if($this->config->getPost('law') == 'law'){
				if($this->config->getPost('read') == 'no'){
					$this->mediator->redirector('?org=' . urlencode($torg));
				}
			}
			return true; //display law page
		}
		return false;
	}
	
	protected function refreshOuWeb($ou,$pou){ //if press refresh button in web browser
		$sql = "SELECT COUNT(*) AS mnum FROM " . $this->config->getSqlTable('ouregist') . 
			" WHERE cookie='" . $this->cookie->getCookieVal() . "' AND " . 
			" ou='" . $ou . "' AND pou='" . $pou . "'";
		$result = $this->db->query($sql);
		if (DB::isError($result)) {        
			die ($result->getMessage() . ' -- ' . __METHOD__ . ' ' .  __LINE__);
		}
		
		$tval = $result->fetchRow();
		if($tval['mnum'] > 0){
			return true;
		}
		return false;
	}
	
	protected function insInOuTable($pou){
		$tcon = $this->config;
		$ldapou = $tcon->getLdapOuAttr();
		$maxorder = $this->config->getPhpcoGlobal('minregid');
		
		$sql = "SELECT MAX(orderid) as ord FROM " . $this->config->getSqlTable('ouregist');
		$result = $this->db->query($sql);
		if (DB::isError($result)) {        
			die ($result->getMessage() . " --> guest 1064");
		}
		$tval = $result->fetchRow();
		if($tval['ord'] != null){
			$maxorder = $tval['ord'] + 1;
		}
		
		$sql =  "INSERT INTO " . $this->config->getSqlTable('ouregist') . 
				" ( orderid , cookie , ou , pou , telephonenumber1 , telephonenumber2 , telephonenumber3 , telephonenumber4 , facsimiletelephonenumber1 , facsimiletelephonenumber2 , postaladdress , postalcode , description , regtime) VALUES (" . 
				"'" . $maxorder . "'," . //orderid
				" '" . @$this->cookie->getCookieVal() . "'," . //cookie
				" '" . @$this->userinfo[$ldapou[0]] . "'," . //ou
				" '" . @$pou . "'," .                        //pou
				" '" . @$this->userinfo[$ldapou[1]][0] . "'," . //telephonenumber1
				" '" . @$this->userinfo[$ldapou[1]][1] . "'," . //telephonenumber2
				" '" . @$this->userinfo[$ldapou[1]][2] . "'," . //telephonenumber3
				" '" . @$this->userinfo[$ldapou[1]][3] . "'," . //telephonenumber4
				" '" . @$this->userinfo[$ldapou[2]][0] . "'," . //facsimiletelephonenumber1
				" '" . @$this->userinfo[$ldapou[2]][1] . "'," . //facsimiletelephonenumber2
				" '" . @$this->userinfo[$ldapou[3]] . "'," . //postaladdress
				" '" . @$this->userinfo[$ldapou[4]] . "'," . //postalcode
				" '" . @$this->userinfo[$ldapou[5]] . "'," . //description
				" '" . @$this->config->getSystemTime() . //regtime
				"')";
		$result = $this->db->query($sql);
		if (DB::isError($result)) {        
			die ($result->getMessage()  . ' -- ' . __METHOD__ . ' ' .  __LINE__);
		}
		$this->resetLawState();
		
		return $maxorder;
	}
	
	private function resetLawState(){
		$sql =  "UPDATE " . $this->config->getSqlTable('cookie') . "
				SET law = 'n' WHERE cookie = '" . $this->cookie->getCookieVal() . "'";
		$result = $this->db->query($sql);
		if (DB::isError($result)) {        
			die ($result->getMessage() . ' -- ' . __METHOD__ . ' ' .  __LINE__);
		}
	}

}

?>