<?php
	/***************************************************************************
		               alluser.php
		           -------------------
		begin         : 13-Apr-06
		copyright     : (C) 2006 The nm114.net brgd
	    email         : brgd@nm114.net
		
		$Id: alluser.php, 13-Apr-06 1:54:14 AM brgd 
		
	 ***************************************************************************/
abstract class allUser extends sysClass{
	protected $mediator;
	protected $orgValues;
	protected $personDN;  //information of loged in person in ldap server.
	protected $perValues; //information of person that will be displayed.
	
	protected $userinfo; //store values which insert in ldap.
	
	protected function setMediator($med){
		$this->mediator = $med;
		$this->mediator->registerObject($this);
	}
	
	protected function setHeads($name){
		$target = $this->config->getGet('target');
		$torg = $this->getOrgValuesOrg();
		if($target == null){$target = 'home';}
		$helpn = $this->lang->getActionName($name);
		
		$thelp = '<span class="as">' . $helpn . '</span>';
		if($target != $name){
			if($name =='logout'){
				$thelp = '<a href="?target=' . $name . '&org=' . urlencode($torg) . '">' . $helpn . '</a>';
			}else{
				if(($name == ($this->config->getTarget('login'))) || 
				($name == ($this->config->getTarget('checkper')))){
					$thelp = '<a href="' . $this->config->getPhpcoGlobal('urls') . '?target=' . $name . '&org=' . urlencode($torg) . '">' . $helpn . '</a>';
				}else{
					$thelp = '<a href="?org=' . urlencode($torg) . '">' . $helpn . '</a>';
				}
			}
		}
		
		return $thelp;
	}
	
	protected function userHome(){
		if(($this->config->getGet('org') == '') && ($this->config->getGet('per') != '')){ 
			return $this->homeDisplayPer(); //diaplay person info.
		}
		
		return $this->homeDisplayOrg();   //display org info.
	}
	
	protected function displayAttribute($blockname,$homefix,$attri){
		$this->template->setCurrentBlock($blockname);
		for($i = 0;$i < count($attri); $i++){
			//if($attri[$i] > 2){
				//echo($attri[$i]['count'] . ' ');
			//}
			if(!isset($attri[$i][0][2])){
				continue;
			}
			$this->template->setVariable(array(
				"homefixwli" => $homefix,
 				"attriname"  => $attri[$i][0],
 				"attrivalue" => $attri[$i][1],
			));
 			$this->template->parseCurrentBlock($blockname);
		}
	}
	
	protected function displayParentOuName($blockname,$pouname){
		$this->template->setCurrentBlock($blockname);
		for($i = 0;$i < count($pouname); $i++){
 			$this->template->setVariable(array(
 				"pouname"    => $pouname[$i],
			));
 			$this->template->parseCurrentBlock($blockname);
		}
	}

	protected function getAllParentOrg($dn,$base){
		$td = explode(",",$dn);
		$tb = explode(",",$base);
		
		$tc = count($td) - count($tb);
		
		if($tc == 0){
			$tp[0] = $this->lang->getLang('position') . ' &nbsp;' . $this->lang->getLang('hhht');
			if($this->config->getGet('per') != ''){
				$tp[0] = $this->lang->getLang('position') . '<a href="?">' . $this->lang->getLang('hhht') . '</a> &lt;';
			}
			return $tp;
		}else{
			$tp[0] = $this->lang->getLang('position') . '<a href="?">' . $this->lang->getLang('hhht') . '</a> &lt;';
		}
		
		if(($tc == 1) && ($this->config->getGet('org') == '')){ //no parent organization
			$tp[1] = '<a href="?org=' . urlencode(substr($td[0],(strpos($td[0],"=")+1))) . '">' . substr($td[0],3) . '</a> &lt;';
			return $tp;
		}

		$j = 1;
		for($i = ($tc-1); $i >= 0; $i--){
			if(($i == 0) && ($this->config->getGet('org') != '')){
				$tp[$j] = '<span class="current">' . substr($td[$i],(strpos($td[$i],"=")+1)) . '</span>';
			}else{
				$tp[$j] = '<a href="?org=' . urlencode(substr($td[$i],(strpos($td[$i],"=")+1))) . '">' . substr($td[$i],(strpos($td[$i],"=")+1)) . '</a> &lt;';
			}
			$j++;
		}
		return $tp;
	}	
	//set temp object
	protected $config;
	protected $cookie;
	protected $lang;
	protected $template;
	protected $db;
	
	protected function setTObject(){
		$this->lang     = $this->mediator->getObject($this->mediator->getLang());   //language object
		$this->template = $this->mediator->getObject($this->mediator->getHtml());   //language object
		$this->config   = $this->mediator->getObject($this->mediator->getConfig()); //config object
		$this->cookie   = $this->mediator->getObject($this->mediator->getCookie());
		$this->db       = $this->mediator->getObject($this->mediator->getDb());
	}

	protected function getLdapRegUid(){
		if($this->cookie->getUserType() == $this->config->getUserType('guest')){
			return '';
		}
		
		$max = ''; $tuid = '';
		
		$auid = $this->mediator->searchAllUid(); //get all uid in base dn;
		for($i = 0; $i < $auid['count']; $i++){
			$tuid[$i] = $auid[$i]['uid'][0];
		}
		
		$max = $this->getBrokenValueInArray($tuid);
		
		return $max;
	}
	
	private function getBrokenValueInArray($uid){
		$min = $this->config->getPhpcoGlobal('maxuid');
		for($i = 0; $i < count($uid); $i++){
			if(!in_array($min + $i,$uid)){
				return $min + $i;
			}
		}
		return $min + $i;
	}

	protected function cleanUserRegistTable(){ //to clear user records that does not checked  in time.
		$sql = "DELETE FROM " . $this->config->getSqlTable('userregist') . 
					" WHERE  regtime<" . (($this->config->getSystemTime()) - ($this->config->getPhpcoGlobal('regperlasttime')));
		$result = $this->db->query($sql);
		if (DB::isError($result)) {        
			die ($result->getMessage()  . ' -- ' . __METHOD__ . ' ' .  __LINE__);
		}
		
		return;
	}
	
	protected function cleanOuRegistTable(){ //to clear ou records that does not checked  in time.
		$sql = "DELETE FROM " . $this->config->getSqlTable('ouregist') . 
					" WHERE  (regtime<" . (($this->config->getSystemTime()) - ($this->config->getPhpcoGlobal('regoulasttime'))) .	")";
		$result = $this->db->query($sql);
		if (DB::isError($result)) {        
			die ($result->getMessage()  . ' -- ' . __METHOD__ . ' ' .  __LINE__);
		}
		
		return;
	}
	
	public function existInLDAP($op,$org,$retur){ //if a entry exist in ldap
		if($org){
			if($retur){
				return  $this->orgExistInLDAP($op,true);
			}
			$this->orgExistInLDAP($op,false);
			return;
		}
		if($this->cookie->getUserType() == $this->config->getUserType('guest')){ //guest can't see person information'
			$this->mediator->redirector();  //if person does not exist, redirect.
		}
		$tens = $this->mediator->searchALdapPer($op);
		
		if($tens['count'] == 0){
			return;     //just can return,because regist new  person function
		}
		
		if($tens['count'] > 1){
			exit("error! person \"$op\" is already exist or multiple! "  . ' -- ' . __METHOD__ . ' ' .  __LINE__);
		}
		
		if($retur){
			return $tens[0];
		}
		
		$this->perValues = $tens[0];
		
		$tens2 = explode(',',$tens[0]['dn']);
		if(count($tens2) > 3){
			$ten = substr($tens2[1],(strpos($tens2[1],'=')+1));
			$this->orgValues = $this->existInLDAP($ten,true,true);
		}else{
			$this->orgValues = $this->existInLDAP($this->lang->getLang('hhht'),true,true);
		}
	}
	
	private function orgExistInLDAP($org,$retur){ //if a org unit entry exist in ldap
		if($org == ''){
			$org = $this->lang->getLang('hhht');
		}
		$tens = $this->mediator->searchALdapOu($org);
		if($tens['count'] == 0){
			return;   //just can return,because regist new  organization function
		}
		if($tens['count'] > 1){
			die("error! organization \"$org\" is already exist or multiple! "  . ' -- ' . __METHOD__ . ' ' .  __LINE__);
		}
		
		if($retur){
			return $tens[0];
		}

		$this->orgValues = $tens[0];
	}
	
	protected function setOrgValues(){
		//set information of person will display and it's org information
		$tper = $this->config->getGet('per');
		$tget = $this->config->getGet('org');
				
		$torg = true;
		if(($tget == '') && ($tper != '')){
			$tget = $tper;
			if(eregi("^[1-9]{1}[0-9]*$", $tget)){
				$torg = false;
			}
		}
		$this->existInLDAP($tget,$torg,false);  //get org info.
		if(!$this->orgValues['dn']){ //http has org or person and does not exist in ldap,redirect
			$this->mediator->redirector();
		}
	}

	public function getOrgValuesOrg(){
		$ldapo = $this->config->getLdapOAttr();         //ou
		$ldapou = $this->config->getLdapOuAttr();    //o
		if(@$this->orgValues[$ldapou[0]][0]){
			return $this->orgValues[$ldapou[0]][0];
		}
		return  $this->orgValues[$ldapo[0]][0];
	}
	
	public function getPerValuesOrg(){
		
	}
	
	public function getPersonDNOrg(){
		$temp = explode(',',$this->personDN['dn']);
		$temp = substr($temp[1],(strpos($temp[1],'=')+1));
		return $temp;
	}
	
	protected function getPerAllAttribute(){
		$torg = $this->orgValues;
		$sr = $this->perValues;
		if((count($sr) == 0) && (count($torg) != 0)){
			return $this->getAllAttribute($torg);
		}		
		
		$tlang = $this->lang;
		$tldapp = $tlang->getLdapp(-1);
		for($i = 0; $i < count($tldapp); $i++){
			$tfound = false;
			for($j = 0; $j < $sr['count']; $j++){
				$ta[$i][0] = $tlang->getPLdap($tldapp[$i]);
				if($sr[$j] == $tldapp[$i]){
					$tvalue = '';
					for($z = 0; $z < $sr[$sr[$j]]['count']; $z++){
						if($z < ($sr[$sr[$j]]['count'] - 1)){
							$tvalue .= $sr[$sr[$j]][$z] . ';';
						}
						if($z == ($sr[$sr[$j]]['count'] - 1)){
							$tvalue .= $sr[$sr[$j]][$z];
						}
						
					}
					$ta[$i][1] = $tvalue;
					$tfound = true;
				}
				if(!$tfound){
					$ta[$i][1] = '&nbsp;';
				}
			}
		}
		
		return $ta;
	}

	protected function getAllAttribute($sr){
		$ta = null;
		$tlang = $this->lang;
		$tldapo = $tlang->getLdapo(-1);
		for($i = 0; $i < count($tldapo); $i++){
			$tfound = false;
			for($j = 0; $j < $sr['count']; $j++){
				$ta[$i][0] = $tlang->getOLdap($tldapo[$i]);
				if($sr[$j] == $tldapo[$i]){
					$tvalue = '';
					for($z = 0; $z < $sr[$sr[$j]]['count']; $z++){
						if($z < ($sr[$sr[$j]]['count'] - 1)){
							$tvalue .= $sr[$sr[$j]][$z] . ';';
						}
						if($z == ($sr[$sr[$j]]['count'] - 1)){
							$tvalue .= $sr[$sr[$j]][$z];
						}
					}
					$ta[$i][1] = $tvalue;
					$tfound = true;
				}
				if(!$tfound){
					$ta[$i][1] = '&nbsp;';
				}
			}
		}
		
		return $ta;
	}
	
	protected function registper($values = null,$fail = null){
		$torg  = $this->getOrgValuesOrg();
		if(!isset($values[0])){ //set parent org name
			$values[0] = $torg;
		}
		
		$failAlreadyReg = '';
		if($this->cookie->getUserType() == $this->config->getUserType('guest')){ //guest must read law statement
			$registcheck = '';
			$registcheck = $this->checkRegistPer();
			if($registcheck != ''){
				return $registcheck;
			}
		}
		if(isset($fail[19])){
			$failAlreadyReg = '<div id="attentionmenu">' . $fail[19] . '</div>';
		}
						
		$ldapp = $this->config->getLdapPAttr();
		
		$this->template->loadTemplateFile($this->config->getPhpcoFile('registpertemp') . $this->config->getPhpcoGlobal('htmlfileext'));
		$tfillupmin = $this->lang->getLang('minpasslen');
		if($this->cookie->getUserType() == $this->config->getUserType('guest')){
			$tfillupmin = $this->lang->getLang('fillup') . $tfillupmin;
		}
		
		$tpouname = $this->lang->getLang('registper');
		$taction = '?target=' . $this->config->getTarget('registperok') . '&org=' . urlencode($torg); //ok button action
		$tactionb = '?org=' . urlencode($torg); //back nutton action
		if(($this->config->getGet('target') == $this->config->getTarget('editper')) ||
			$this->config->getGet('target') == 'editperok'){
			$tpouname = $this->lang->getActionName('editper');
			$taction = '?target=' . $this->config->getTarget('editperok') . '&org=' . urlencode($torg);
			if($this->cookie->getUserType() == $this->config->getUserType('admin')){ //guest must read law statement
				$taction = '?target=' . $this->config->getTarget('editperok') . '&per=' . urlencode($this->config->getGet('per'));
			}
			$tactionb = '?org=' . urlencode($torg);
			if((($a = $this->config->getGet('org')) == '') && (($b = $this->config->getGet('per')) != '')){
				$tactionb = '?per=' . urlencode($this->config->getGet('per'));
			}
			if($this->cookie->getUserType() == $this->config->getUserType('admin')){ //guest must read law statement
				$tactionb = '?per=' . urlencode($this->config->getGet('per'));
			}
		}
		
		$this->template->setCurrentBlock("body1");
			$this->template->setVariable(array(
 				"pouname"      => $tpouname . '：',
 				"action"       => $taction,
 				"fillup"       => $this->lang->getLang('fillup'),
 				"fillupmin"    => $tfillupmin,
 				"orgname"      => $this->lang->getLang('orgname'),
 				"fullname"     => $this->lang->getLang('ouperson') . $this->lang->getPLdap($ldapp[0]),
 				"phone"        => $this->lang->getPLdap($ldapp[5]),
 				"password"     => $this->lang->getLang('shuru') . $this->lang->getLang('password'),
 				"rewrite"      => $this->lang->getLang('rewrite'),
 				"fax"          => $this->lang->getPLdap($ldapp[6]),
 				"homephone"    => $this->lang->getPLdap($ldapp[7]),
 				"mail"         => $this->lang->getPLdap($ldapp[8]),
 				"mobile"       => $this->lang->getPLdap($ldapp[9]),
 				"roomnum"      => $this->lang->getPLdap($ldapp[10]),
 				"address"      => $this->lang->getPLdap($ldapp[11]),
 				"postalcode"   => $this->lang->getPLdap($ldapp[12]),
 				"homeaddress"  => $this->lang->getPLdap($ldapp[13]),
 				"employtype"   => $this->lang->getPLdap($ldapp[14]),
 				"description"  => $this->lang->getPLdap($ldapp[16]),
 				"actionb"       => $tactionb,
 				"back"           => $this->lang->getLang('back'),
 				"reset"          => $this->lang->getLang('reset'),
 				"submit"       => $this->lang->getLang('submit'),
 				"confirmper"   => $this->lang->getLang('confirmper'),
 				"noorgname"    => $this->lang->getLangError('noorgname'),
 				"nofullname"   => $this->lang->getLangError('nofullname'),
 				"nophone"      => $this->lang->getLangError('nophone'),
 				"nopassword"   => $this->lang->getLangError('nopassword'),
 				"norepassword" => $this->lang->getLangError('norepassword'),
 				
				"orgnameval"  => @$values[0],
 				"fullnameval" => @$values[1],
 			
 				"phone1val" => @$values[2],
 				"phone2val" => @$values[3],
 				"phone3val" => @$values[4],
 				"phone4val" => @$values[5],
 			
 				"password1val"   => @$values[6],
 				"password2val"   => @$values[7],
 			
 				"fax1val"        => @$values[8],
 				"fax2val"        => @$values[9],
 			
 				"homephoneval"   => @$values[10],
 				"mailval"        => @$values[11],
 				"mobileval"      => @$values[12],
 				"roomnumval"     => @$values[13],
 				"addressval"     => @$values[14],
 				"postalcodeval"  => @$values[15],
 				"homeaddressval" => @$values[16],
 				"employtypeval"  => @$values[17],
 				"descriptionval" => @$values[18],
 				
 				"alreadyregistfail" => $failAlreadyReg,
				"orgnamefail"   => $fail[0],
 				"fullnamefail"  => $fail[1],
 				"phone1fail"    => $fail[2],
 				"phone2fail"    => $fail[3],
 				"phone3fail"    => $fail[4],
 				"phone4fail"    => $fail[5],
 				"passwordfail"  => $fail[6],
 				"repasswordfail"=> $fail[7],
 				"fax1fail"      => $fail[8],
 				"fax2fail"      => $fail[9],
 				"homephonefail" => $fail[10],
 				"mailfail"      => $fail[11],
 				"mobilefail"    => $fail[12],
 				"roomnumfail"   => $fail[13],//not set address $fail[14]
 				"addressfail"   => $fail[14],
 				"postalcodefail"=> $fail[15],
 				"homeaddressfail"=> $fail[16],
 				"employtypefail" => $fail[17],
 				"descriptionfail"=> $fail[18],
 			));
		
		$this->template->parseCurrentBlock("body1"); 
		
		return $this->template->show();
	}

	protected function registperok(){
		if($this->cookie->getUserType() == $this->config->getUserType('guest')){//guest must be checked
			$registcheck = '';
			$registcheck = $this->checkRegistPer();
			if($registcheck != ''){
				return $this->home();
			}
		}
		$tpost[0] = 'orgname';
 		$tpost[1] = 'fullname';
 		$tpost[2] = 'phone1';
 		$tpost[3] = 'phone2';
 		$tpost[4] = 'phone3';
 		$tpost[5] = 'phone4';
 		$tpost[6] = 'password1';
 		$tpost[7] = 'password2';
 		$tpost[8] = 'fax1';
 		$tpost[9] = 'fax2';
 		$tpost[10] = 'homephone';
 		$tpost[11] = 'mail';
 		$tpost[12] = 'mobile';
 		$tpost[13] = 'roomnum';
 		$tpost[14] = 'address';
 		$tpost[15] = 'postalcode';
 		$tpost[16] = 'homeaddress';
 		$tpost[17] = 'employtype';
 		$tpost[18] = 'description'; 
		
		$stat = false;
		
		$this->config->setPost($tpost[1],substr($this->config->getPost($tpost[1]),0,$this->config->getPhpcoGlobal('namelength')));  //name can't too long
		
		$values[0] = stripslashes($this->config->getPost($tpost[0]));
		$values[1] = stripslashes($this->config->getPost($tpost[1]));
 		$values[2] = stripslashes($this->config->getPost($tpost[2]));
 		
 		$values[3] = stripslashes($this->config->getPost($tpost[3]));
 		$values[4] = stripslashes($this->config->getPost($tpost[4]));
 		$values[5] = stripslashes($this->config->getPost($tpost[5]));
 		$values[6] = stripslashes($this->config->getPost($tpost[6]));
 			
 		$values[7] = stripslashes($this->config->getPost($tpost[7]));
 		$values[8] = stripslashes($this->config->getPost($tpost[8]));
 			
 		$values[9]  = stripslashes($this->config->getPost($tpost[9]));
 		$values[10] = stripslashes($this->config->getPost($tpost[10]));
 			
 		$values[11] = stripslashes($this->config->getPost($tpost[11]));
 		$values[12] = stripslashes($this->config->getPost($tpost[12]));
 		$values[13] = stripslashes($this->config->getPost($tpost[13]));
 		$values[14] = stripslashes($this->config->getPost($tpost[14]));
 		$values[15] = stripslashes($this->config->getPost($tpost[15]));
 		$values[16] = stripslashes($this->config->getPost($tpost[16]));
 		$values[17] = stripslashes($this->config->getPost($tpost[17]));
 		$values[18] = stripslashes($this->config->getPost($tpost[18]));
		
		$fail[0] = $this->checkPerValues($tpost[0],$values[0]);
		$fail[1] = $this->checkPerValues($tpost[1],$values[1]);
		$fail[2] = $this->checkPerValues($tpost[2],$values[2]);
		$fail[3] = $this->checkPerValues($tpost[3],$values[3]);
		$fail[4] = $this->checkPerValues($tpost[4],$values[4]);
		$fail[5] = $this->checkPerValues($tpost[5],$values[5]);
		$fail[6] = $this->checkPerValues($tpost[6],$values[6]);
		$fail[7] = $this->checkPerValues($tpost[7],$values[7]);
		$fail[8] = $this->checkPerValues($tpost[8],$values[8]);
		$fail[9] = $this->checkPerValues($tpost[9],$values[9]);
		$fail[10] = $this->checkPerValues($tpost[10],$values[10]);
		$fail[11] = $this->checkPerValues($tpost[11],$values[11]);
		$fail[12] = $this->checkPerValues($tpost[12],$values[12]);
		$fail[13] = $this->checkPerValues($tpost[13],$values[13]);
		$fail[14] = $this->checkPerValues($tpost[14],$values[14]);
		$fail[15] = $this->checkPerValues($tpost[15],$values[15]);
		$fail[16] = $this->checkPerValues($tpost[16],$values[16]);
 		$fail[17] = $this->checkPerValues($tpost[17],$values[17]); //'employtype'
		$fail[18] = $this->checkPerValues($tpost[18],$values[18]); //'description'
		
		for($i = 0; $i < count($fail); $i++){
			if($fail[$i] != ''){
				$stat = true;$y=$i;
			}
		}
		
		if($stat){
			return $this->registper($values,$fail);
		}
		
		if(($this->config->getGet('target') == $this->config->getTarget('registperok')) && ($this->refreshPerWeb($values[1]))){
			$fail[19] = $this->lang->getLangError('peralreadyexisterr');
			if($this->cookie->getUserType() == $this->config->getUserType('guest')){
				$fail[19] = $this->lang->getLangError('peralreadyregisterr');
			}
			return $this->registper($values,$fail);
		}		
		
		if($this->cookie->getUserType() == $this->config->getUserType('guest')){
			if($this->mediator->GuestRegistTooQuick($this->cookie->getCookieVal())){
				$fail[19] = $this->lang->getLangError('checkOuInfo');
				return $this->registper($values,$fail);
			}
			
			$regnum = $this->insInUserTable($values[0]);
			$torg  = $this->getOrgValuesOrg();
			//display ok page
			$pouname = $this->lang->getLang('registper') . '：';
 			$actionb = '?org=' . urlencode($torg);
 			$ok      = $this->lang->getRegPerOk($values[0],$values[1],$regnum,($this->config->getPhpcoGlobal('regperlasttime')/60));
 			$back    = $this->lang->getLang('submit');
	
			return $this->displayokpage($pouname,$actionb,$ok,$back);
		}
		if($this->cookie->getUserType() == $this->config->getUserType('user')){
			$ldapp = $this->config->getLdapPAttr();
			$tpdn  = $this->personDN['dn'];
			if($this->config->getGet('target') == $this->config->getTarget('editperok')){ //if edit person
				$this->userinfo[$ldapp[3]] = $this->cookie->getUserId();
				$this->mediator->modifyAnLdapEntry($tpdn,$this->userinfo);
				$this->mediator->sredirector('?per=' . $this->cookie->getUserId());
			}
		}
		if($this->cookie->getUserType() == $this->config->getUserType('admin')){
			$ldapp = $this->config->getLdapPAttr();
			$tpdn = $ldapp[3] . '=' . $this->userinfo[$ldapp[3]] . ',' . $this->orgValues['dn'];
			
			if($this->config->getGet('target') == $this->config->getTarget('editperok')){ //if edit person
				$this->mediator->modifyAnLdapEntry($tpdn,$this->userinfo);
				$this->mediator->sredirector('?per=' . $this->userinfo[$ldapp[3]]);
			}
			
			//if regist person
			$this->mediator->addAnLdapEntry($tpdn,$this->userinfo);
			$fail[19] = $this->lang->getLang('perregistok');
			return $this->registper('',$fail);
		}
	}

	protected function registou($values = null,$fail = null){
		$torg  = $this->getOrgValuesOrg();
		if(!isset($values[1])){
			$values[0] = $torg;
			$values[1] = $values[0];
		}
		
		$failAlreadyReg = '';
		if($this->cookie->getUserType() == $this->config->getUserType('guest')){ //guest must read law statement
			$registcheck = '';
			$registcheck = $this->checkRegistOu();
			if($registcheck != ''){
				return $registcheck;
			}
		}
		if(isset($fail[11])){
			$failAlreadyReg = '<div id="attentionmenu">' . $fail[11] . '</div>';
		}
						
		$ldapou = $this->config->getLdapOuAttr();
		$tpouname = $this->lang->getActionName('registou');
		$taction  = '?target=' . $this->config->getTarget('registouok') . '&org=' . urlencode($torg); //ok button action
		if(($this->config->getGet('target') == $this->config->getTarget('editou')) || 
			($this->config->getGet('target') == $this->config->getTarget('editouok'))){
			$tpouname = $this->lang->getActionName('editou');
			$taction  = '?target=' . $this->config->getTarget('editouok') . '&org=' . urlencode($torg); //ok button action
		}
		$this->template->loadTemplateFile($this->config->getPhpcoFile('registoutemp') . $this->config->getPhpcoGlobal('htmlfileext'));
		$this->template->setCurrentBlock("body1");
			$this->template->setVariable(array(
 				"pouname"      => $tpouname . '：',
 				"action"       => $taction,
 				"fillup"       => $this->lang->getLang('fillup'),
 				"orgname"      => $this->lang->getLang('orgname'),
 				"porgname"     => $this->lang->getLang('porgname'),
 				"phone"        => $this->lang->getOLdap($ldapou[1]),
 				"fax"          => $this->lang->getOLdap($ldapou[2]),
 				"address"      => $this->lang->getOLdap($ldapou[3]),
 				"postalcode"   => $this->lang->getOLdap($ldapou[4]),
 				"description"  => $this->lang->getOLdap($ldapou[5]),
 				"actionb"      => '?org=' . urlencode($torg), //back nutton action,
 				"back"         => $this->lang->getLang('back'),
 				"reset"        => $this->lang->getLang('reset'),
 				"submit"       => $this->lang->getLang('submit'),
 				"confirmou"    => $this->lang->getLang('confirmou'),
 				"noorgname"    => $this->lang->getLangError('noorgname'),
 				"noporgname"   => $this->lang->getLangError('noporgname'),
 				"nophone"      => $this->lang->getLangError('nophoneou'),
 				"nopassword"   => $this->lang->getLangError('nopassword'),
 				"norepassword" => $this->lang->getLangError('norepassword'),
 				
				"orgnameval"   => @$values[0],
 				"porgnameval"  => @$values[1],
				 			
 				"phone1val" => @$values[2],
 				"phone2val" => @$values[3],
 				"phone3val" => @$values[4],
 				"phone4val" => @$values[5],
 				"fax1val"        => @$values[6],
 				"fax2val"        => @$values[7],
 				"addressval"     => @$values[8],
 				"postalcodeval"  => @$values[9],
 				"descriptionval" => @$values[10],
 				"alreadyregistfail" => $failAlreadyReg,
 				"orgnamefail"     => $fail[0],
 				"porgnamefail"   => $fail[1],
				"phone1fail"       => $fail[2],
 				"phone2fail"       => $fail[3],
 				"phone3fail"       => $fail[4],
 				"phone4fail"       => $fail[5],
 				"fax1fail"            => $fail[6],
 				"fax2fail"            => $fail[7],
 				"addressfail"      => $fail[8],
 				"postalcodefail" => $fail[9],
 				"addressfail"     => $fail[8],
 				"descriptionfail"=> $fail[10],//not set home address description $fail[10]
 			));
		
		$this->template->parseCurrentBlock("body1"); 
		
		return $this->template->show();
	}

	protected function registouok(){
		$tpost[0] = 'orgname';
 		$tpost[1] = 'porgname';
 		$tpost[2] = 'phone1';
 		$tpost[3] = 'phone2';
 		$tpost[4] = 'phone3';
 		$tpost[5] = 'phone4';
 		$tpost[6] = 'fax1';
 		$tpost[7] = 'fax2';
 		$tpost[8] = 'address';
 		$tpost[9] = 'postalcode';
 		$tpost[10] = 'description';
 		
		if($this->cookie->getUserType() == $this->config->getUserType('guest')){//guest must be checked
			$registcheck = '';
			$registcheck = $this->checkRegistOu();
			if($registcheck != ''){
				return $this->home();
			}
		}
		
		$stat = false;
		
		$this->config->setPost($tpost[0],substr($this->config->getPost($tpost[0]),0,$this->config->getPhpcoGlobal('namelength')));  //name can't too long
		$values[0] = stripslashes($this->config->getPost($tpost[0]));
		$values[1] = stripslashes($this->config->getPost($tpost[1]));
 		$values[2] = stripslashes($this->config->getPost($tpost[2]));
 		
 		$values[3] = stripslashes($this->config->getPost($tpost[3]));
 		$values[4] = stripslashes($this->config->getPost($tpost[4]));
 		$values[5] = stripslashes($this->config->getPost($tpost[5]));
 		
 		$values[6] = stripslashes($this->config->getPost($tpost[6]));
 		$values[7] = stripslashes($this->config->getPost($tpost[7]));
 		
 		$values[8] = stripslashes($this->config->getPost($tpost[8]));
 		$values[9]  = stripslashes($this->config->getPost($tpost[9]));
 		$values[10] = stripslashes($this->config->getPost($tpost[10]));
 		
 		$fail[0] = $this->checkOuValues($tpost[0],$values[0]);
		$fail[1] = $this->checkOuValues($tpost[1],$values[1]);
		$fail[2] = $this->checkOuValues($tpost[2],$values[2]);
		$fail[3] = $this->checkOuValues($tpost[3],$values[3]);
		$fail[4] = $this->checkOuValues($tpost[4],$values[4]);
		$fail[5] = $this->checkOuValues($tpost[5],$values[5]);
		$fail[6] = $this->checkOuValues($tpost[6],$values[6]);
		$fail[7] = $this->checkOuValues($tpost[7],$values[7]);
		$fail[8] = $this->checkOuValues($tpost[8],$values[8]);
		$fail[9] = $this->checkOuValues($tpost[9],$values[9]);
		$fail[10] = $this->checkOuValues($tpost[10],$values[10]); //'description'
		//$fail[11] = '';   //define this after 13 lines .
		
		for($i = 0; $i < count($fail); $i++){
			if($fail[$i] != ''){
				$stat = true;
			}
		}
		if($stat){
			return $this->registou($values,$fail);
		}
		
		if($this->refreshOuWeb($values[0],$values[1])){
			if($this->cookie->getUserType() == $this->config->getUserType('guest')){
				$fail[11] = $this->lang->getLangError('oualreadyregisterr');
			}
			if(($this->cookie->getUserType() == $this->config->getUserType('admin')) && 
				($this->config->getGet('target') != 'editouok')){
					$fail[11] = $this->lang->getLangError('oualreadyexisterr');
			}
			if(isset($fail[11])){
				return $this->registou($values,$fail);
			}
		}
		$torg  = $this->getOrgValuesOrg();
		if($this->cookie->getUserType() == $this->config->getUserType('guest')){
			if($this->mediator->GuestRegistTooQuick($this->cookie->getCookieVal())){
				$fail[11] = $this->lang->getLangError('checkOuInfo');
				return $this->registou($values,$fail);
			}
			$regnum = $this->insInOuTable($values[1]);
			//display ok page
			$pouname = $this->lang->getLang('registou') . '：';
 			$actionb = '?org=' . urlencode($torg);
 			$ok      = $this->lang->getRegOuOk($values[1],$values[0],$regnum,($this->config->getPhpcoGlobal('regoulasttime')/3600));
 			$back    = $this->lang->getLang('submit');
			return $this->displayokpage($pouname,$actionb,$ok,$back);
		}
		if($this->cookie->getUserType() == $this->config->getUserType('user')){
			$ldapo = $this->config->getLdapOuAttr();
			$tpdn = $ldapo[0] . '=' . $this->userinfo[$ldapo[0]] . ',' . $this->orgValues['dn'];
			$torg   = $this->getOrgValuesOrg();	$tporg = $this->getPersonDNOrg();
			if($this->config->getGet('target') == $this->config->getTarget('editouok')){//if edit org
				if($torg == $tporg){ //if is user's org
					$tpdn = array();
					$tpdn[0] = $this->orgValues['dn']; //old value
					$tv = substr($this->orgValues['dn'],(strpos($this->orgValues['dn'],',')+1));
 					$tpdn[1] = $ldapo[0] . '=' . $values[0] . ',' . $tv; //new value
					if(!$this->mediator->ouAlreadyEdited($torg)){  //ou  just can edited one time in a month.
						$this->mediator->insertInEditOuDB($torg,$values[0]);//write log to DB,record who change the info, last 10 year.
						if($this->mediator->modifyAnLdapEntry($tpdn,$this->userinfo)){ 
							$this->mediator->sredirector('?org=' . $values[0]);
						}
					}else{
						$fail[11] = $this->lang->getLangError('oualreadyediterr');
						return $this->registou($values,$fail);
					}
				}else{
					$this->mediator->sredirector('?org=' . $torg);
				}
			}
		}
		if($this->cookie->getUserType() == $this->config->getUserType('admin')){
			$ldapo = $this->config->getLdapOuAttr();
			$tpdn = $ldapo[0] . '=' . $this->userinfo[$ldapo[0]] . ',' . $this->orgValues['dn'];
			if($this->config->getGet('target') == $this->config->getTarget('editouok')){ //if edit org
				$tpdn = array();
				$tpdn[0] = $this->orgValues['dn']; //old value
				$tv = substr($this->orgValues['dn'],(strpos($this->orgValues['dn'],',')+1));
 				$tpdn[1] = $ldapo[0] . '=' . $values[0] . ',' . $tv; //new value
				if($this->mediator->modifyAnLdapEntry($tpdn,$this->userinfo)){
					$this->mediator->sredirector('?org=' . $values[0]);
				}
				$fail[11] = $this->lang->getLangError('ouediterr');
				return $this->registou('',$fail);
			}
			
			if($values[1] != 	$torg){ //admin can't regist org under any org
				$fail[1] = $this->lang->getLangError('notparentorg');
				return $this->registou($values,$fail);
			}
			
			$this->mediator->addAnLdapEntry($tpdn,$this->userinfo);
			$fail[11] = $this->lang->getLang('ouregistok');
			return $this->registou('',$fail);
		}
	}

	protected function checkPerValues($tname,$tval){
		$value = '';
		$ldapp = $this->config->getLdapPAttr();
		$torg  = $this->getOrgValuesOrg();
		
		if(strlen($tval) > $this->config->getPhpcoGlobal('maxvaluelen')){ //if values too long,return false
			$value = $this->lang->getLangError('toolongvalue');
		}
		
		if($tname == 'orgname'){
			if(($this->existInLDAP($tval,true,true) == null)){
				$this->mediator->redirector('?org=' . $torg);
			}
		}
		
		if($tname == 'fullname'){
			if($tval == ''){
				$value = $this->lang->getLangError('notempty');
			}else{
				if( $this->mediator->invalidName($tval)){
					$value = $this->lang->getLangError('incilligal');
				}
				$this->userinfo[$ldapp[0]] = $tval;                //cn
				$this->userinfo[$ldapp[1]] = substr($tval,3);      //givenname
				$this->userinfo[$ldapp[2]] = substr($tval,0,3);    //sn
				if($this->userinfo[$ldapp[1]] == ''){
					$this->userinfo[$ldapp[1]] = $this->userinfo[$ldapp[0]];
				}
				$minuid = $this->getLdapRegUid();
				$this->userinfo[$ldapp[3]] = $minuid;
				if($this->config->getGet('target') == 'editperok'){
					$this->userinfo[$ldapp[3]] = $this->config->getGet('per');              //uid
					
				}
			}
		}
		
		$spid = @count($this->userinfo[$ldapp[5]]); //phone number must in order 0,1,2,3 etc,,
		if($tname == 'phone1'){
			if(!($this->mediator->isPhoneNumber($tval))){
				$value = $this->lang->getLangError('isphonenum');
			}
			if($tval != ''){
				$this->userinfo[$ldapp[5]][$spid] = $tval;                //phone1
			}
		}
 		
 		if(($tname == 'phone2') ||
			($tname == 'phone3') ||
			($tname == 'phone4')){
			if(($tval != '') && !($this->mediator->isPhoneNumber($tval))){
				$value = $this->lang->getLangError('isphonenum') . $this->lang->getLangError('orempty');
			}
			if($tval != ''){
				switch($tname){
					case 'phone2':
						$this->userinfo[$ldapp[5]][$spid] = $tval;          //phone2
						break;
					case 'phone3':
						$this->userinfo[$ldapp[5]][$spid] = $tval;          //phone3
						break;
					case 'phone4':
						$this->userinfo[$ldapp[5]][$spid] = $tval;          //phone4
						break;
				}
			}
		}
		
		if($tname == 'password1'){
			if(!($this->mediator->isPassword($tval))){
				$value = $this->lang->getLangError('ispassword');
			}
			if($tval != ''){
				$this->userinfo[$ldapp[4]] = '{md5}' . base64_encode(pack('H*', md5($tval)));//password
			}
		}
		
		if($tname == 'password2'){
			if(($this->config->getPost('password1')) != ($this->config->getPost('password2'))){
				$value = $this->lang->getLangError('notsame');
			}
		}
		
		if(($tname == 'fax1') ||
			($tname == 'fax2') ||
			($tname == 'homephone')){
			if(($tval != '') && !($this->mediator->isPhoneNumber($tval))){
				$value = $this->lang->getLangError('isphonenum') . $this->lang->getLangError('orempty');
			}
			if($tval != ''){
				$sfid = @count($this->userinfo[$ldapp[6]]);
				switch($tname){
					case 'fax1':
						$this->userinfo[$ldapp[6]][$sfid] = $tval;          //fax1
						break;
					case 'fax2':
						$this->userinfo[$ldapp[6]][$sfid] = $tval;          //fax2
						break;
					case 'homephone':
						$this->userinfo[$ldapp[7]] = $tval;             //homephone
						break;
				}
			}
		}
 		
 		if($tname == 'mail'){
			if(($tval != '') && !($this->mediator->isMail($tval))){
				$value = $this->lang->getLangError('ismail') . $this->lang->getLangError('orempty');
			}
			if($tval != ''){
				$this->userinfo[$ldapp[8]] = $tval;             //mail
			}
		}
		
		if($tname == 'mobile'){
			if(($tval != '') && !($this->mediator->isMobile($tval))){
				$value = $this->lang->getLangError('ismobile') . $this->lang->getLangError('orempty');
			}
			if($tval != ''){
				$this->userinfo[$ldapp[9]] = $tval;             //mobile
			}
		}
		
		if($tname == 'roomnum'){
			if(($tval != '') && !($this->mediator->isRoomnum($tval))){
				$value = $this->lang->getLangError('isroomnum') . $this->lang->getLangError('orempty');
			}
			if($tval != ''){
				$this->userinfo[$ldapp[10]] = $tval;             //roomnumber
			}
		}
 		
 		if($tname == 'address'){
 			if($tval != ''){
 				$this->userinfo[$ldapp[11]] = $tval;          //address
 			}
 		}
 		
 		if($tname == 'postalcode'){
			if(($tval != '') && !($this->mediator->isPostalcode($tval))){
				$value = $this->lang->getLangError('ispostalcode') . $this->lang->getLangError('orempty');
			}
			if($tval != ''){
				$this->userinfo[$ldapp[12]] = $tval;             //postcode
			}
		}
 		
 		if($tname == 'homeaddress'){
 			if($tval != ''){
				$this->userinfo[$ldapp[13]] = $tval;             //home address
			}
 		}
 		
 		if($tname == 'employtype'){
 			if($tval != ''){
				$this->userinfo[$ldapp[14]] = $tval;             //employee type
			}
 		}
 		//$this->userinfo[$ldapp[15]] = $tval;             //photo
 		if($tname == 'description'){
 			if($tval != ''){
				$this->userinfo[$ldapp[16]] = $tval;             //description
			}
 		}
 		
 		if($value != ''){
			return '<li class="end">' . $value . '</li>';
 		}
 		return $value;
	}
	
	protected function checkOuValues($tname,$tval){
		$value = '';
		$ldapou = $this->config->getLdapOuAttr();
		$torg = $this->getOrgValuesOrg();
		
		if(strlen($tval) > $this->config->getPhpcoGlobal('maxvaluelen')){ //if values too long,return false
			$value = $this->lang->getLangError('toolongvalue');
		}
		
		if($tname == 'orgname'){
			if( $tval == ''){
				$value = $this->lang->getLangError('notempty');
			}
			if( $this->mediator->invalidName($tval)){
				$value = $this->lang->getLangError('incilligal');
			}
			if($this->config->getGet('target') == 'registouok'){
				if(($this->existInLDAP($tval,true,true)) != null){
					$value = $this->lang->getLangError('oualreadyexisterr');
				}
			}
			if($this->config->getGet('target') == 'editouok'){
				if(($this->existInLDAP($tval,true,true)) != null){
					$temp = $this->existInLDAP($tval,true,true);
					if($temp['dn'] != $this->orgValues['dn']){ //if org already exist and not itself
						$value = $this->lang->getLangError('oualreadyexisterr');
					}
				}
			}
			$this->userinfo[$ldapou[0]] = $tval;                //orgname
		}
		
		if($tname == 'porgname'){ //guest can regist any orgname under any parent org
			if(($tval == '')){
				$value = $this->lang->getLangError('notempty');    //porgname
			}
			if( $this->mediator->invalidName($tval)){
				$value = $this->lang->getLangError('incilligal');
			}
		}
		
		$spid = @count($this->userinfo[$ldapou[1]]);  //phone number must in order 0,1,2,3 etc,,
		if($tname == 'phone1'){
			if(!($this->mediator->isPhoneNumber($tval))){
				$value = $this->lang->getLangError('isphonenum');
			}else{
				$this->userinfo[$ldapou[1]][$spid] = $tval;                //phone1
			}
		}
 		
 		if(($tname == 'phone2') ||
			($tname == 'phone3') ||
			($tname == 'phone4')){
			if(($tval != '') && !($this->mediator->isPhoneNumber($tval))){
				$value = $this->lang->getLangError('isphonenum') . $this->lang->getLangError('orempty');
			}
			if($tval != ''){
				switch($tname){
					case 'phone2':
						$this->userinfo[$ldapou[1]][$spid] = $tval;          //phone2
						break;
					case 'phone3':
						$this->userinfo[$ldapou[1]][$spid] = $tval;          //phone3
						break;
					case 'phone4':
						$this->userinfo[$ldapou[1]][$spid] = $tval;          //phone4
						break;
				}
			}
		}
		
		if(($tname == 'fax1') ||
			($tname == 'fax2')){
			if(($tval != '') && !($this->mediator->isPhoneNumber($tval))){
				$value = $this->lang->getLangError('isphonenum') . $this->lang->getLangError('orempty');
			}
			if($tval != ''){
				$sfid = @count($this->userinfo[$ldapou[2]]);
				switch($tname){
					case 'fax1':
						$this->userinfo[$ldapou[2]][$sfid] = $tval;          //fax1
						break;
					case 'fax2':
						$this->userinfo[$ldapou[2]][$sfid] = $tval;          //fax2
						break;
				}
			}
		}
 		
 		if($tname == 'address'){
 			if($tval != ''){
 				$this->userinfo[$ldapou[3]] = $tval;             //address
 			}
 		}
 		
 		if($tname == 'postalcode'){
			if(($tval != '') && !($this->mediator->isPostalcode($tval))){
				$value = $this->lang->getLangError('ispostalcode') . $this->lang->getLangError('orempty');
			}else{
				if($tval != ''){
					$this->userinfo[$ldapou[4]] = $tval;         //postcode
				}
			}
		}
 		
 		if($tname == 'description'){
 			if($tval != ''){
 				$this->userinfo[$ldapou[5]] = $tval;             //description
 			}
 		}
 		
		if($value != ''){
			return '<li class="end">' . $value . '</li>';
 		}
 		return $value;
	}

}

?>
