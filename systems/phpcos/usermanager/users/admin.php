<?php
	/***************************************************************************
		               admin.php
		           -------------------
		begin         : 19-Apr-06
		copyright     : (C) 2006 The nm114.net brgd
	    email         : brgd@nm114.net
		
		$Id: admin.php, 19-Apr-06 11:42:58 PM brgd 
		
	 ***************************************************************************/
require_once("user.php");

class admin extends user{
	
	public static function makeObject($med){
		if(self::makeObj(__CLASS__)){
			return new admin($med);
		}
		return false;
	}
	
	//Constructor
	private function admin($med){
		$this->setMediator($med);
	}
	
	//display html
	public function displayHead(){
		$this->setTObject();
		
		$this->setOrgValues(); //set information of person will display and it's org information
		$this->personDN = $this->existInLDAP($this->cookie->getUserId(),false,true);
		
		$this->template->loadTemplateFile($this->config->getPhpcoFile('headtemp') . $this->config->getPhpcoGlobal('htmlfileext'));
		
		$this->template->setCurrentBlock("head1");
 			$this->template->setVariable(array(
 				"waitfor" => $this->lang->getLang('waitfor'),
 				"title" => $this->lang->getLang('title'),
 				"css"   => $this->config->getPhpcoFile('css'),
 				"admin" => $this->setAdminMenu($this->config->getGet('per')),
 				"home"  => $this->setHeads('home'),
 				"log"   => $this->setHeads('logout'),
				"date"  => '<span class="date">' . $this->lang->getLang('date') . '</span>',
				"formaction" => '?target=' . $this->config->getTarget('search'),
				"skeyv"      => $this->mediator->getSkeyValue(), 
 				"searchb1"   => $this->lang->getActionName('search'),
			));
 		
		$this->template->parseCurrentBlock("head1"); 
		
		return $this->template->show();
	}
	
	public function displayBody(){
		$this->cleanUserRegistTable(); //clean timeout user info in userregist table
		$this->cleanOuRegistTable(); //clean timeout org info in 0u-regist table
		
		$tname = $this->config->getGet('target');
		//if use SSL
		if(@$_SERVER["HTTPS"] != 'on'){
			$this->mediator->sredirector();
		}
		//end if use SSL
		if(method_exists($this,$tname)){  //In object,Can't use function_exists()!!
			return $this->$tname();
		}else{
			return $this->userHome();
		}
	}
	
	protected function homeDisplayOrg(){
		$tdes = '';$tregist = '';$tregistou = '';$tregistper = '';$tedit='';$tdelete='';
		
		$torg  = $this->getOrgValuesOrg();
		$tndn = $this->orgValues;
		
		$tregistou  = '<a href="?target=' . $this->config->getTarget('registou') . '&org=' . urlencode($torg) . '">' . $this->lang->getActionName('registou') . '</a>';
		$tregistper  = '<a href="?target=' . $this->config->getTarget('registper') . '&org=' . urlencode($torg) . '">' . $this->lang->getActionName('registper') . '</a>';
		$tsubento  = $this->mediator->getSubOuEntries($tndn['dn']);
		$tsubentp  = $this->mediator->getSubPerEntries($tndn['dn']);
		
		if($torg == $this->lang->getLang('hhht')){
			$tdes        = $this->lang->getLang('descriptionguest');
			$torgname[0] =$this->lang->getLang('position') . ' &nbsp;' . $torg;
			$tattri[0]   = null;
		}else{
			$torgname = $this->getAllParentOrg($tndn['dn'],$this->config->getPhpcoLDAP('base'));
			$tattri   = $this->getAllAttribute($tndn);
			$tedit     = '<a href="?target=' . $this->config->getTarget('editou') . '&org=' . urlencode($torg) . '">' . $this->lang->getActionName('editou') . '</a>';
			if(($tsubentp['count'] == 0) && ($tsubento[0] == '')){
				$tdelete = '<a href="?target=' . $this->config->getTarget('deleteou') . '&org=' . urlencode($torg) . '">' . $this->lang->getActionName('deleteou') . '</a>';
			}
		}
		$tcheckper = $this->getRegistedPerState();
		$tcheckou  = $this->getRegistedOuState(urlencode($torg));
		
		$this->template->loadTemplateFile($this->config->getPhpcoFile('hometemp') . $this->config->getPhpcoGlobal('htmlfileext'));
		$this->template->setCurrentBlock("body1");
			$this->setVariable($tdes,$tregistou,$tregistper,$tcheckper,$tcheckou,$tedit,$tdelete);
			
			$this->displayParentOuName('body11',$torgname);

			$this->displaySubOuEntry('body12',$tsubento,@$tsubentp[0]);
			
			$this->displaySubPerEntry('body13',$tsubentp);
			
			$this->displayAttribute('body14','homefixwli',$tattri);
		$this->template->parseCurrentBlock("body1"); 
		
		return $this->template->show();
	}

	protected function homeDisplayPer(){
		$tdes = '';$tregist = '';$tregistou = '';$tregistper = '';$tedit='';$tdelete='';
		
		$tper = $this->config->getGet('per');
		$tndn = $this->orgValues;
		
		$tn = $this->getOrgValuesOrg();
		
		$tattri = $this->getPerAllAttribute();
		$tedit     = '<a href="?target=' . $this->config->getTarget('editper') . '&per=' . $tper . '">' . $this->lang->getActionName('editper') . '</a>';
		$tdelete = '<a href="?target=' . $this->config->getTarget('deleteper') . '&per=' . $tper . '">' . $this->lang->getActionName('deleteper') . '</a>';
		if((!$tndn) || ($tndn['dn'] == $this->config->getPhpcoLDAP('base'))){
			$torgname[0] = $this->lang->getLang('position') . '<a href="?">' . $this->lang->getLang('hhht') . '</a> &lt;';
			$tregistou  = '<a href="?target=' . $this->config->getTarget('registou') . '&org=' . urlencode($this->lang->getLang('hhht')) . '">' . $this->lang->getActionName('registou') . '</a>';
			$tregistper  = '<a href="?target=' . $this->config->getTarget('registper') . '&org=' . urlencode($this->lang->getLang('hhht')) . '">' . $this->lang->getActionName('registper') . '</a>';
			$tsubento  = $this->mediator->getSubOuEntries($this->config->getPhpcoLDAP('base'));
			$tsubentp  = $this->mediator->getSubPerEntries($this->config->getPhpcoLDAP('base'));
		}else{
			$torgname = $this->getAllParentOrg($tndn['dn'],$this->config->getPhpcoLDAP('base'));
			$tregistou  = '<a href="?target=' . $this->config->getTarget('registou') . '&org=' . urlencode($tn) . '">' . $this->lang->getActionName('registou') . '</a>';
			$tregistper  = '<a href="?target=' . $this->config->getTarget('registper') . '&org=' . urlencode($tn) . '">' . $this->lang->getActionName('registper') . '</a>';
			$tsubento  = $this->mediator->getSubOuEntries($tndn['dn']);
			$tsubentp  = $this->mediator->getSubPerEntries($tndn['dn']);
		}
		
		$tcheckper = $this->getRegistedPerState(urlencode($tn));
		$tcheckou  = $this->getRegistedOuState(urlencode($tn));
		
		$this->template->loadTemplateFile($this->config->getPhpcoFile('hometemp') . $this->config->getPhpcoGlobal('htmlfileext'));
		$this->template->setCurrentBlock("body1");
			$this->setVariable($tdes,$tregistou,$tregistper,$tcheckper,$tcheckou,$tedit,$tdelete);
						
			$this->displayParentOuName('body11',$torgname);

			$this->displaySubOuEntry('body12',$tsubento,@$tsubentp[0]);
			
			$this->displaySubPerEntry('body13',$tsubentp);
			
			$this->displayAttribute('body14','homefixwlip',$tattri);
		$this->template->parseCurrentBlock("body1"); 
		
		return $this->template->show();
	}

	private function checkou($fail = '',$idvalue = ''){
		$torg = $this->getOrgValuesOrg();
		$this->template->loadTemplateFile($this->config->getPhpcoFile('checktemp') . $this->config->getPhpcoGlobal('htmlfileext'));
		$this->template->setCurrentBlock("body1");
			$this->template->setVariable(array(
 				"checkpertitle" => $this->lang->getLang('checkoutitle') . ' : ',
 				"fail"                 => $fail,
 				"idvalue"          => $idvalue,
 				"action"            => '?target=' . $this->config->getTarget('checkounum') . '&org=' . urlencode($torg),
 				"id"                   => $this->lang->getLang('organization')  . $this->lang->getLang('regcode'),
 				"back"              => $this->lang->getLang('back'),
 				"check"            => $this->lang->getLang('submit'),
 				"actionb" => '?org=' . urlencode($torg),
 			));
		$this->template->parseCurrentBlock("body1"); 
		
		return $this->template->show();
	}
	
	private function checkounum($id = '',$fail = ''){
		$failstate = false;
		$id ? $id : $id = $this->config->getPost('id');
		if(!$this->mediator->isNumber($id) ||               //id must be a number
			!$this->mediator->idExistInRegTable('ou',$id)){ //id must exist in reg table!
			$failstate = true;
		}
		if($failstate == true){
			return $this->checkou('<div id="attentionmenu">' . $this->lang->getLangError('idnotexist') . '</div>', $id);
		}
		
		$tattri = $this->mediator->getRegOuAllAttribute($id);
		$torg = $this->getOrgValuesOrg();
		$this->template->loadTemplateFile($this->config->getPhpcoFile('checkoutemp') . $this->config->getPhpcoGlobal('htmlfileext'));
		$this->template->setCurrentBlock("body1");
			$this->template->setVariable(array(
 				"checkpertitle" => $this->lang->getLang('checkoutitle') . ' : ',
 				"action"            => '?target=' . $this->config->getTarget('checkouok') . '&id=' . $id . '&org=' . urlencode($torg),
 				"fail"                 => $fail,
				"back"              => $this->lang->getLang('back'),
 				"create"           => $this->lang->getActionName('create'),
				"actionb"         => '?target=' . $this->config->getTarget('checkou') . '&org=' . urlencode($torg),
 			));
 			$this->displayAttribute('body11','',$tattri);
		$this->template->parseCurrentBlock("body1"); 
		
		return $this->template->show();
		
	}
	
	private function checkouok(){
		$db = $this->db;
		$id = $this->config->getGet('id');
		$ldapou = $this->config->getLdapOuAttr();
		
		$tpost[0] = 'orgname';
 		$tpost[1] = 'phone1';
 		$tpost[2] = 'phone2';
 		$tpost[3] = 'phone3';
 		$tpost[4] = 'phone4';
 		$tpost[5] = 'fax1';
 		$tpost[6] = 'fax2';
 		$tpost[7] = 'address';
 		$tpost[8] = 'postalcode';
 		$tpost[9] = 'description';
 		
 		$sql = "SELECT * FROM " . $this->config->getSqlTable('ouregist') . " WHERE orderid = '" . $id . "'";
		$result = $db->query($sql);
		if (DB::isError($result)) {        
			die ($result->getMessage() . ' -- ' . __METHOD__ . ' ' .  __LINE__);
		}
		$values = $result->fetchRow();
		if($values[$ldapou[0]] == ''){
			return $this->checkou('<div id="attentionmenu">' . $this->lang->getLangError('idnotexist') . '</div>', $id);
		}
		
		$this->checkOuValues($tpost[0],$values[$ldapou[0]]);
		$this->checkOuValues($tpost[1],$values[$ldapou[1] . '1']);
		$this->checkOuValues($tpost[2],$values[$ldapou[1] . '2']);
		$this->checkOuValues($tpost[3],$values[$ldapou[1] . '3']);
		$this->checkOuValues($tpost[4],$values[$ldapou[1] . '4']);
		$this->checkOuValues($tpost[5],$values[$ldapou[2] . '1']);
		$this->checkOuValues($tpost[6],$values[$ldapou[2] . '2']);
		$this->checkOuValues($tpost[7],$values[$ldapou[3]]);
		$this->checkOuValues($tpost[8],$values[$ldapou[4]]);
		$this->checkOuValues($tpost[9],$values[$ldapou[5]]);
	
		$tempp = $this->existInLDAP($values['pou'], true, true);
		if($tempp == ''){
			$fail = '<div id="attentionmenu">' . $this->lang->getLangError('porgnotexist') . '</div>';
			return $this->checkounum($id, $fail);
		}
		$tempo = $this->existInLDAP($values[$ldapou[0]], true, true);
		if($tempo != ''){
			$fail = '<div id="attentionmenu">' . $this->lang->getLangError('oualreadyexisterr') . '</div>';
			return $this->checkounum($id, $fail);
		}
		
		$tpdn = $ldapou[0] . '=' . $values[$ldapou[0]] . ',' . $tempp['dn'];
 			
		$this->mediator->addAnLdapEntry($tpdn,$this->userinfo);
		
		$sql = "DELETE FROM " . $this->config->getSqlTable('ouregist') . 
			" WHERE orderid = '" . $id . "'";
		$result = $db->query($sql);
		if (DB::isError($result)) {        
			die ($result->getMessage() . ' -- ' . __METHOD__ . ' ' .  __LINE__);
		}
		
		$this->mediator->sredirector('?org=' . $values[$ldapou[0]]);
	}
	
	protected function search(){
		$num = 0;
		$keyw = '';
		$keyw = $this->config->getGet('skey');
		if($keyw == ''){
			return $this->home();
		}
		
		$order = 0;
		
		//search all persons
		$tensp   = $this->mediator->searchAllLdapPerAll($this->personDN,$keyw);
		$tresult = array();
		if($tensp['count'] != 0){
			$attri = $this->lang->getLdapp(3);
			$attrin = $this->lang->getLdapp(0);
			for ($i = 0; $i < $tensp["count"]; $i++) {
    			$tresult[$order] =  '<li class="num">' . (++$num) . ':</li><li class=content><a href="?per=' . urlencode($tensp[$i][$attri][0]) . '">' . $tensp[$i][$attrin][0] . '</a></li>';
				$order++;
    		}
		}
		
		//search all organizations
		$tenso   = $this->mediator->searchAllLdapOuAll($keyw);
		if($tenso['count'] != 0){
			$attri = $this->lang->getLdapo(0);
			for ($i = 0; $i < $tenso["count"]; $i++) {
    			$tresult[$order] = '<li class="num">' . (++$num) . ':</li><li class=content><a href="?org=' . urlencode($tenso[$i][$attri][0]) . '">' . $tenso[$i][$attri][0] . '</a></li>';
				$order++;
			}
		}
		
		if($order == 0){
			$tresult[0] = $this->lang->getLang('usernomatch');
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
	
	protected function deleteper(){
		if(!$this->mediator->isAdminUser($this->perValues['uid'][0])){
			$this->mediator->delAnLdapEntry($this->perValues['dn']);
		}
		return $this->homeDisplayOrg();
	}
		
	protected function deleteou(){
		if(!$this->mediator->ouHasSubEntry($this->orgValues['dn'])){
			$this->mediator->delAnLdapEntry($this->orgValues['dn']);
		}
		$torg = explode(',',$this->orgValues['dn']);
		$org = substr($torg[1],(strpos($torg[1],'=')+1));
		$this->mediator->sredirector('?org=' . $org);
	}
	
	private function setAdminMenu($uid){
		$per = $this->personDN['uid'][0]; //loged in user id.
		$st = '<a href="?per=' . $per . '">' . $this->personDN['cn'][0] . '</a>';
		
		if($uid == $per){
			$st = '<span class="as">' . $this->personDN['cn'][0] . '</span>';
		}
		return $st;
	}
	
	private function setVariable($des,$registou,$registper,$checkper,$checkou,$edit,$delete){
		$this->template->setVariable(array(
 			"description" => $des,
 			"registou"      => $registou,
 			"registper"    => $registper,
			"checkou"     => $checkou,
			"checkper"    => $checkper, 			
			"edit"            => $edit,
 			"delete"        => $delete,
		));
	}
	
	protected function refreshPerWeb($cn){ //if press refresh button in web browser
		//if in ldap, $cn exist under $this->orgValues['dn'],return true.
		$tens = $this->mediator->ldapHasPerCnWithDn($cn,$this->orgValues['dn']);
		return $tens;
	}
	
	protected function refreshOuWeb($ou){ //if press refresh button in web browser
		//if in ldap, $ou exist under $this->orgValues['dn'],return true.
		$tens = $this->mediator->ldapHasOuWithDn($ou,$this->orgValues['dn']);
		return $tens;
	}
	
	private function getRegistedPerState(){
		$ou  = $this->getOrgValuesOrg();
		
		$sql = "SELECT COUNT(*) AS oun FROM  " . $this->config->getSqlTable('userregist');
		$result = $this->db->query($sql);
		if (DB::isError($result)) {        
			die ($result->getMessage() . ' -- ' . __CLASS__ . ' ' .  __LINE__);
		}
		$tnum =  $result->fetchRow();
		if($tnum['oun'] > 0){
			if($ou != ''){
				 $thref = '<a href="?target=' . $this->config->getTarget('checkper') . '&org=' . $ou . '">' . $this->lang->getActionName('checkpertitle') . '</a>';
			}else{
				$thref = '<a href="?target=' . $this->config->getTarget('checkper') . '&org=' .  urlencode($this->lang->getLang('hhht')) . '">' . $this->lang->getActionName('checkpertitle') . '</a>';
			}
			return $thref;
		}
		return;
	}
	
	public function getRegistedOuState(){
		$ou  = $this->getOrgValuesOrg();
		
		$sql = "SELECT COUNT(*) AS oun FROM  " . $this->config->getSqlTable('ouregist');
		$result = $this->db->query($sql);
		if (DB::isError($result)) {        
			die ($result->getMessage() . ' -- ' . __CLASS__ . ' ' .  __LINE__);
		}
		$tnum =  $result->fetchRow();
		if($tnum['oun'] > 0){
			if($ou != ''){
				 $thref = '<a href="?target=' . $this->config->getTarget('checkou') . '&org=' . $ou . '">' . $this->lang->getActionName('checkoutitle') . '</a>';
			}else{
				$thref = '<a href="?target=' . $this->config->getTarget('checkou') . '&org=' .  urlencode($this->lang->getLang('hhht')) . '">' . $this->lang->getActionName('checkoutitle') . '</a>';
			}
			return $thref;
		}
		return;
	}
}
?>