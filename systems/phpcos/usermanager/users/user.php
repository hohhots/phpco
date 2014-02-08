<?php
	/***************************************************************************
		               user.php
		           -------------------
		begin         : 19-Apr-06
		copyright     : (C) 2006 The nm114.net brgd
	    email         : brgd@nm114.net
		
		$Id: user.php, 19-Apr-06 11:42:45 PM brgd 
		
	 ***************************************************************************/
require_once("guest.php");

class user extends guest{
	public static function makeObject($med){
		if(self::makeObj(__CLASS__)){
			return new user($med);
		}
		return false;
	}
	
	//Constructor
	private function user($med){
		$this->setMediator($med);
	}
	
	public function displayHead(){
		$this->setTObject();
		 
		$this->setOrgValues();
		$this->personDN = $this->existInLDAP($this->cookie->getUserId(),false,true);  //get org or person info.;;
		
		$tuser = $this->personDN[$this->lang->getLdapp(0)][0];
		$tuid   = $this->personDN[$this->lang->getLdapp(3)][0];

		$torg = $this->getOrgValuesOrg();
		$teditper = '<a href="?target=' . $this->config->getTarget('editper') . '&org=' . $torg . '">' . $tuser . '</a>';
		$ttarget = $this->config->getGet('target');
		if($ttarget == $this->config->getTarget('editper')){
			$teditper = '<span class="as">' . $tuser . '</span>';
		}
		$tcheckper = $this->getRegistedPerState(); //if display checkper
		$this->template->loadTemplateFile($this->config->getPhpcoFile('headtemp') . $this->config->getPhpcoGlobal('htmlfileext'));
		
		$this->template->setCurrentBlock("head1");
 			$this->template->setVariable(array(
 				"waitfor" => $this->lang->getLang('waitfor'),
				"title" => $this->lang->getLang('title'),
 				"css"   => $this->config->getPhpcoFile('css'),
 				"editper"  => $teditper,
 				"home"  => $this->setHeads('home'),
				"checkper"  => $tcheckper, 				
				"log"   => $this->setHeads('logout'),
				"date"  => $this->lang->getLang('date'),
				"formaction" => '?target=' . $this->config->getTarget('search'),
				"skeyv"      => $this->mediator->getSkeyValue(), 
				"search"     => $this->config->getTarget('search'),
 				"searchb1"   => $this->lang->getActionName('search'),
 				"org"      => $torg,
			));
 		
		$this->template->parseCurrentBlock("head1"); 
		
		return $this->template->show();
	}
	
	public function displayBody(){
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
	
	public function displayFoot(){
		
	}
	
	protected function homeDisplayOrg(){
		$tdes = '';
		$torg = $this->getOrgValuesOrg();
		$tndn = $this->orgValues;
		if($torg == $this->lang->getLang('hhht')){
			$tdes        = $this->lang->getLang('descriptionguest');
			$torgname[0] =$this->lang->getLang('position') . ' &nbsp;' . $this->lang->getLang('hhht');
			$tattri[0]   = null;
			$tsubento  = $this->mediator->getSubOuEntries($this->config->getPhpcoLDAP('base'));
			$tsubentp  = $this->mediator->getSubPerEntries($this->config->getPhpcoLDAP('base'));
		}else{
			$torgname = $this->getAllParentOrg($tndn['dn'],$this->config->getPhpcoLDAP('base'));
			$tattri   = $this->getAllAttribute($tndn);
			$tsubento  = $this->mediator->getSubOuEntries($tndn['dn']);
			$tsubentp  = $this->mediator->getSubPerEntries($tndn['dn']);
		}
		
		$this->template->loadTemplateFile($this->config->getPhpcoFile('hometemp') . $this->config->getPhpcoGlobal('htmlfileext'));
		$this->template->setCurrentBlock("body1");
			$torg   = $this->getOrgValuesOrg();
			$tporg = $this->getPersonDNOrg();
			if($torg == $tporg){
				$teditou =  '<a href="?target=' . $this->config->getTarget('editou') . '&org=' . $this->getPersonDNOrg() . '">' . $this->lang->getActionName('editou') . '</a>';
				$this->template->setVariable(array(
 					"editou"        => $teditou,
				));
			}
			$this->setVariable($tdes);
			
			$this->displayParentOuName('body11',$torgname);
			
			$this->displayAttribute('body12','homefixwli',$tattri);
			
			$this->displaySubOuEntry('body13',$tsubento,@$tsubentp[0]);
			
			$this->displaySubPerEntry('body14',$tsubentp);
			
		$this->template->parseCurrentBlock("body1"); 
		
		return $this->template->show();
	}
	
	protected function homeDisplayPer(){
		$tdes = '';
		
		$tper = $this->config->getGet('per');
		$tndn = $this->orgValues;
		$ts = explode(',',$tndn['dn']);
		$tn = substr($ts[0],3);
				
		$tattri = $this->getPerAllAttribute();
		if($this->cookie->getUserId() != $this->config->getGet('per')){
			$tattri = $this->getPerFilters($tattri); //user just can view some attribute of other person
		}
		
		if((!$tndn) || ($tndn['dn'] == $this->config->getPhpcoLDAP('base'))){
			$torgname[0] = $this->lang->getLang('position') . '<a href="?">' . $this->lang->getLang('hhht') . '</a> &lt;';
			$tsubento  = $this->mediator->getSubOuEntries($this->config->getPhpcoLDAP('base'));
			$tsubentp  = $this->mediator->getSubPerEntries($this->config->getPhpcoLDAP('base'));
		}else{
			$torgname = $this->getAllParentOrg($tndn['dn'],$this->config->getPhpcoLDAP('base'));
			$tsubento  = $this->mediator->getSubOuEntries($tndn['dn']);
			$tsubentp  = $this->mediator->getSubPerEntries($tndn['dn']);
		}
		
		$this->template->loadTemplateFile($this->config->getPhpcoFile('hometemp') . $this->config->getPhpcoGlobal('htmlfileext'));
		$this->template->setCurrentBlock("body1");
			$this->setVariable($tdes);
						
			$this->displayParentOuName('body11',$torgname);
			
			$this->displayAttribute('body12','homefixwlip',$tattri);
			
			$this->displaySubOuEntry('body13',$tsubento,$tsubentp[0]);
			
			$this->displaySubPerEntry('body14',$tsubentp);
			
		$this->template->parseCurrentBlock("body1"); 
		
		return $this->template->show();
	}
	
	protected function editper(){
		$ldapp = $this->config->getLdapPAttr();
		$tperval = $this->perValues;      //admin edit any user
		if($this->cookie->getUserType() == $this->config->getUserType('user')){
			$tperval = $this->personDN;
			if($this->config->getGet('per') != ''){ //if set user's id in http get,redirect',because just can edit self information
				$this->mediator->redirector();
			}
		}
		
		$values[0] = $this->lang->getLang('hhht');
		$tens = explode(',',$tperval['dn']);
		$base = count(explode(',',$this->config->getPhpcoLDAP('base')));//ldap base dn count number
		if(count($tens) > ($base+1)){
			$values[0] = substr($tens[1],(strpos($tens[1],'=')+1));//"orgnameval"
		}
				
		$values[1] = $tperval[$ldapp[0]][0];//"fullnameval"
 			
 		$values[2] = @$tperval[$ldapp[5]][0];//"phone1val" 
 		$values[3] = @$tperval[$ldapp[5]][1];//"phone2val" 
 		$values[4] = @$tperval[$ldapp[5]][2];//"phone3val" 
 		$values[5] = @$tperval[$ldapp[5]][3];//"phone4val"
 		$values[6] = '';//"password1val"
 		$values[7] = '';//"password2val"
 		
 		$values[8] = @$tperval[$ldapp[6]][0];//"fax1val"
 		$values[9] = @$tperval[$ldapp[6]][1];//"fax2val"
 			
 		$values[10] = @$tperval[$ldapp[7]][0];//"homephoneval" 
 		$values[11] = @$tperval[$ldapp[8]][0];//"mailval" 
 		$values[12] = @$tperval[$ldapp[9]][0];//"mobileval" 
 		$values[13] = @$tperval[$ldapp[10]][0];//"roomnumval"
 		$values[14] = @$tperval[$ldapp[11]][0];//"addressval" 
 		$values[15] = @$tperval[$ldapp[12]][0];//"postalcodeval" 
 		$values[16] = @$tperval[$ldapp[13]][0];//"homeaddressval"
 		$values[17] = @$tperval[$ldapp[14]][0];//"employtypeval"
 		$values[18] = @$tperval[$ldapp[16]][0];//"descriptionval"
		
		return $this->registper($values);
	}

	protected function editperok(){
		return $this->registperok();
	}

	protected function editou(){
		if($this->orgValues['dn'] ==$this->config->getPhpcoLDAP('base')){ //can't edit base org'
			$this->mediator->sredirector('?org=' . $this->lang->getLang('hhht'));
		}
		if($this->cookie->getUserType() == $this->config->getUserType('user')){
			$torg    = $this->existInLDAP($this->getPersonDNOrg(),true,true);
			$torg1  = $this->orgValues;
			$tperorgdn = substr($this->personDN['dn'],(strpos($this->personDN['dn'],',')+1));
			if(($tperorgdn == $this->config->getPhpcoLDAP('base')) ||  //in base users,can't edit ldap base  org'
				 ($torg['dn'] != $torg1['dn'])){ //if set org in http,redirect,because just can edit self org information
				$this->mediator->redirector();
			}
		}
		$ldapou = $this->config->getLdapOuAttr();
		$torg   = $this->orgValues;
		
		$values[0] = $torg[$ldapou[0]][0];//"orgnameval"
 		$values[1] = $this->lang->getLang('hhht');//"porgnameval"
 		$tv = explode(',',$torg['dn']);
 		$base = count(explode(',',$this->config->getPhpcoLDAP('base')));//ldap base dn count number
 		if(count($tv) > ($base+1)){
 			$values[1] = substr($tv[1],(strpos($tv[1],'=')+1));//"porgnameval"
 		} 
 		$values[2] = @$torg[$ldapou[1]][0];//phone1val
 		$values[3] = @$torg[$ldapou[1]][1];//phone2val
 		$values[4] = @$torg[$ldapou[1]][2];//phone3val
 		$values[5] = @$torg[$ldapou[1]][3];//phone4val
 		$values[6] = @$torg[$ldapou[2]][0];//fax1val
 		$values[7] = @$torg[$ldapou[2]][1];//fax2val
 		$values[8] = @$torg[$ldapou[3]][0];//addressval
 		$values[9] = @$torg[$ldapou[4]][0];//postalcodeval
 		$values[10] = @$torg[$ldapou[5]][0];//descriptionval
 		
 		return $this->registou($values);
	}

	protected function editouok(){
		return $this->registouok();
	}

	private function rightForPerson($org){
		$flag = true;
		$tp = $this->personDN;
		
		$ton = explode(',',$org['dn']);//echo($to['dn'] . '<br>' . $tp['dn'] . '<br>' . substr($tp['dn'],0,strpos($tp['dn'],','.$to['dn'])) . '<br>' . substr($tp['dn'],0,strpos($tp['dn'],',')));
		$tpn = explode(',',$tp['dn']);
		if(count($tpn) > 3){
			if($org['dn'] == null){
				$flag = false;
			}else{
				if(substr($tp['dn'],0,strpos($tp['dn'],','.$org['dn'])) != substr($tp['dn'],0,strpos($tp['dn'],','))){
					$flag = false;
				}
			}
		}
		
		return $flag;
	}

	protected function search(){
		$num = 0; $keyw = '';
		$keyw = $this->config->getGet('skey');
		if($keyw == ''){
			return $this->userHome();
		}
		
		$order = 0;
		
		//search all persons
		$tensp   = $this->mediator->searchAllLdapPerAll($this->personDN,$keyw);
		$tresult = array();
		if($tensp['count'] != 0){
			$attri = $this->lang->getLdapp(3);
			$attrin = $this->lang->getLdapp(0);
			for ($i = 0; $i < $tensp["count"]; $i++) {
    			$tresult[$order] = '<li class="num">' . (++$num) . ':</li><li class=content><a href="?per=' . urlencode($tensp[$i][$attri][0]) . '">' . $tensp[$i][$attrin][0] . '</a></li>';
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
	
	private function setVariable($des){
		$this->template->setVariable(array(
 			"description" => $des,
		));
	}
	
	protected function displaySubPerEntry($blockname,$tsubentp){
		$this->template->setCurrentBlock($blockname);
		$disatt = $this->mediator->getObject($this->mediator->getLang())->getLdapp(0);
		$attri  = $this->mediator->getObject($this->mediator->getLang())->getLdapp(3);
		for($i = 0;$i < $tsubentp['count']; $i++){
			if($tsubentp[$i] != ''){
				if($tsubentp[$i][$attri][0] != $this->config->getGet('per')){
					$sst = '<li class="hiconper">&nbsp;</li><li><a href="?per=' . $tsubentp[$i][$attri][0] . '">' . $tsubentp[$i][$disatt][0] . '</a></li>';
				}else{
					$sst = '<li class="hiconper">&nbsp;</li><li class="current">' . $tsubentp[$i][$disatt][0] . '</li>';;
			}
				$this->template->setVariable(array(
 					"subentriesp"  => $sst,
				));
			}
 			$this->template->parseCurrentBlock($blockname);
		}
	}

	protected function displaySubOuEntry($blockname,$tsubento,$firstper){
		$this->template->setCurrentBlock($blockname);
		if(($firstper == '') && ($tsubento[0] == '')){ //if have no a person and a org.
			$this->template->setVariable(array(
 				"subentrieso"  => $this->lang->getLang('none'),
			));
		}else{
			for($i = 0;$i < count($tsubento); $i++){
				if($tsubento[$i] != ''){
					$this->template->setVariable(array(
 						"subentrieso"  => '<li class="hiconou">&nbsp;</li><li>' . $tsubento[$i] . '</li>',
					));
				}
 				$this->template->parseCurrentBlock($blockname);
			}
		}
	}
	
	private function getPerFilters($attri){  //a user just can view some attributes of other person
		$tattri = '';$j = 0;
		$filter = array(0,5,6,8,10,11,12); //attributr order of person in config file
		for($i = 0; $i <count($attri); $i++){
			if(in_array($i, $filter)){
				$tattri[$j][0] = $attri[$i][0];
				$tattri[$j][1] = $attri[$i][1];
				$j++;
			}
		}
		return $tattri;
	}

	protected function checkper($fail = '',$idvalue = ''){
		if(!($this->idExistInRegTable('per'))){
			return $this->userHome();
		}
		$torg = $this->getOrgValuesOrg();
		$this->template->loadTemplateFile($this->config->getPhpcoFile('checkpertemp') . $this->config->getPhpcoGlobal('htmlfileext'));
		$this->template->setCurrentBlock("body1");
			$this->template->setVariable(array(
 				"checkpertitle" => $this->lang->getLang('checkpertitle') . ' : ',
 				"fail"                 => $fail,
 				"idvalue"          => $idvalue,
 				"action"            => '?target=' . $this->config->getTarget('checkpernum') . '&org=' . urlencode($torg),
 				"id"                   => $this->lang->getLang('ouperson')  . $this->lang->getLang('regcode'),
 				"back"              => $this->lang->getLang('back'),
 				"check"            => $this->lang->getLang('submit'),
 				"actionb" => '?org=' . urlencode($torg),
 			));
		$this->template->parseCurrentBlock("body1"); 
		
		return $this->template->show();
	}
	
	protected function checkpernum($id = '',$fail = ''){
		$failstate = false;
		$id ? $id : $id = $this->config->getPost('id');
		
		if(!($this->mediator->isNumber($id)) ||               //id must be a number
			!($this->idExistInRegTable('per',$id))){ //id must exist in reg table!
			return $this->checkper('<div id="attentionmenu">' . $this->lang->getLangError('idnotexist') . '</div>', $id);
		}

		$tattri = $this->mediator->getRegPerAllAttribute($id);
		$torg = $this->getOrgValuesOrg();
		$this->template->loadTemplateFile($this->config->getPhpcoFile('checkoutemp') . $this->config->getPhpcoGlobal('htmlfileext'));
		$this->template->setCurrentBlock("body1");
			$this->template->setVariable(array(
 				"checkpertitle" => $this->lang->getLang('checkpertitle') . ' : ',
 				"action"            => '?target=' . $this->config->getTarget('checkperok') . '&id=' . $id . '&org=' . urlencode($torg),
 				"fail"                 => $fail,
				"back"              => $this->lang->getLang('back'),
 				"create"           => $this->lang->getActionName('create'),
				"actionb"         => '?target=' . $this->config->getTarget('checkper') . '&org=' . urlencode($torg),
 			));
 			$this->displayAttribute('body11','',$tattri);
		$this->template->parseCurrentBlock("body1"); 
		
		return $this->template->show();
	}
	
	protected function checkperok(){
		$db = $this->db;
		$id = $this->config->getGet('id');
		$ldapp = $this->config->getLdapPAttr();
		
		if(!($this->mediator->isNumber($id)) ||               //id must be a number
			!($this->idExistInRegTable('per',$id))){ //id must exist in reg table!
			return $this->checkper('<div id="attentionmenu">' . $this->lang->getLangError('idnotexist') . '</div>', $id);
		}
		
		$tpost[0] = 'fullname';
 		$tpost[1] = 'phone1';
 		$tpost[2] = 'phone2';
 		$tpost[3] = 'phone3';
 		$tpost[4] = 'phone4';
 		$tpost[5] = 'fax1';
 		$tpost[6] = 'fax2';
 		$tpost[7] = 'homephone';
 		$tpost[8] = 'mail';
 		$tpost[9] = 'mobile';
 		$tpost[10] = 'roomnum';
 		$tpost[11] = 'address';
 		$tpost[12] = 'postalcode';
 		$tpost[13] = 'homeaddress';
 		$tpost[14] = 'employtype';
 		$tpost[15] = 'description'; 
		
		$sql = "SELECT * FROM " . $this->config->getSqlTable('userregist') . " WHERE orderid = '" . $id . "'";
		$result = $db->query($sql);
		if (DB::isError($result)) {        
			die ($result->getMessage() . ' -- ' . __METHOD__ . ' ' .  __LINE__);
		}
		$values = $result->fetchRow();

		if(($values[$ldapp[0]] == '') || 
			($values['pou'] != $this->getPersonDNOrg())){ //if reg id not exist
			return $this->checkper('<div id="attentionmenu">' . $this->lang->getLangError('idnotexist') . '</div>', $id);
		}
		
		$tempp = $this->existInLDAP($values['pou'], true, true);
				
		if($tempp == ''){ //if org not exist
			$fail = '<div id="attentionmenu">' . $this->lang->getLangError('ounotexist') . '</div>';
			return $this->checkpernum($id, $fail);
		}		
		if($this->mediator->ldapHasPerCnJustWithDn($values[$ldapp[0]],$tempp['dn'])){ //if person already exist
			$fail = '<div id="attentionmenu">' . $this->lang->getLangError('peralreadyexisterr') . '</div>';
			return $this->checkpernum($id, $fail);
		}		
		
		$this->checkPerValues($tpost[0],$values[$ldapp[0]]);        //cn
		$this->checkPerValues($tpost[1],$values[$ldapp[5] . '1']); //phone1
		$this->checkPerValues($tpost[2],$values[$ldapp[5] . '2']); //phone2
		$this->checkPerValues($tpost[3],$values[$ldapp[5] . '3']); //phone3
		$this->checkPerValues($tpost[4],$values[$ldapp[5] . '4']); //phone4
		$this->userinfo[$ldapp[4]] = $values[$ldapp[4]];                //password
		$this->checkPerValues($tpost[5],$values[$ldapp[6] . '1']);//fax1
		$this->checkPerValues($tpost[6],$values[$ldapp[6] . '2']);//fax2
		$this->checkPerValues($tpost[7],$values[$ldapp[7]]);                    //homephone
		$this->checkPerValues($tpost[8],$values[$ldapp[8]]);                    //mail
		$this->checkPerValues($tpost[9],$values[$ldapp[9]]);                    //moble
		$this->checkPerValues($tpost[10],$values[$ldapp[10]]);                //roomnum
		$this->checkPerValues($tpost[11],$values[$ldapp[11]]);                //address
		$this->checkPerValues($tpost[12],$values[$ldapp[12]]);                //postcode
		$this->checkPerValues($tpost[13],$values[$ldapp[13]]);                //home address
		$this->checkPerValues($tpost[14],$values[$ldapp[14]]);                //'employtype'
 		$this->checkPerValues($tpost[15],$values[$ldapp[16]]);                //'description'
				
		$tpdn = $ldapp[3] . '=' . $this->userinfo[$ldapp[3]] . ',' . $tempp['dn'];
		$this->mediator->addAnLdapEntry($tpdn,$this->userinfo);
		
		$sql = "DELETE FROM " . $this->config->getSqlTable('userregist') . 
			" WHERE orderid = '" . $id . "'";
		$result = $db->query($sql);
		if (DB::isError($result)) {        
			die ($result->getMessage() . ' -- ' . __METHOD__ . ' ' .  __LINE__);
		}
		
		$this->mediator->sredirector('?org=' . $values['pou']);
	}
	
	private function getRegistedPerState(){
		$tpou = $this->getPersonDNOrg();;
		$sql = "SELECT COUNT(*) AS oun FROM  " . $this->config->getSqlTable('userregist') . 
		" WHERE pou='" . $tpou . "'";
		$result = $this->db->query($sql);
		if (DB::isError($result)) {        
			die ($result->getMessage() . ' -- ' . __CLASS__ . ' ' .  __LINE__);
		}
		$tnum =  $result->fetchRow();
		if($tnum['oun'] > 0){
			if(($this->config->getGet('target') == $this->config->getTarget('checkper')) || 
			($this->config->getGet('target') == $this->config->getTarget('checkpernum'))){ //if target equal checkper,return this.
				$thref = '<span class="as">' . $this->lang->getActionName('checkper') . '</span>&nbsp;|';
				return $thref;
			}
			$ou = $this->getOrgValuesOrg();
			if($ou != $this->lang->getLang('hhht')){
				 $thref = '<a href="?target=' .  $this->config->getTarget('checkper') . '&org=' . $ou . '">' . $this->lang->getActionName('checkper') . '</a>&nbsp;|';
			}else{
				$thref = '<a href="?target=' .  $this->config->getTarget('checkper') . '&org=' .  urlencode($this->lang->getLang('hhht')) . '">' . $this->lang->getActionName('checkper') . '</a>&nbsp;|';
			}
			return $thref;
		}
		return;
	}
	
	public function idExistInRegTable($op,$id=''){
		$tcon = $this->config;
		$tcok = $this->cookie;
		$db    = $this->db;
		if($id != ''){
			if(($tcok->getUserType() == $tcon->getUserType('user')) && ($op == 'per')){ 
				$sql = $tcon->getSqlTable('userregist') . ' WHERE orderid=' . $id . " AND pou='" . $this->getPersonDNOrg() . "'";//user sql format
			}
			if($tcok->getUserType() == $tcon->getUserType('admin')){ 
				$sql = $tcon->getSqlTable('ouregist');
				if($op == 'per'){
					$sql = $tcon->getSqlTable('userregist');
				}
				$sql = $sql . ' WHERE orderid=' . $id;//admin sql format
			}
		}else{
			$sql = $tcon->getSqlTable('ouregist');
			if($op == 'per'){
				$sql = $tcon->getSqlTable('userregist');
			}
			if($tcok->getUserType() == $tcon->getUserType('user')){
				$sql = $sql ." WHERE pou='" . $this->getPersonDNOrg() . "'";
			} 
			if($tcok->getUserType() == $tcon->getUserType('admin')){
				$sql = $sql;
			}
		}
		$sql = "SELECT COUNT(*) AS oun FROM  " . $sql;
		$result = $db->query($sql);
		if (DB::isError($result)) {        
			die ($result->getMessage() . ' -- ' . __CLASS__ . ' ' .  __LINE__);
		}
		$tnum =  $result->fetchRow();
		if($tnum['oun'] > 0){
			return true;
		}
		return false;
	}
}
?>
