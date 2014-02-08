<?php
	/***************************************************************************
		               ldap.php
		           -------------------
		begin         : 19-Apr-06
		copyright     : (C) 2006 The nm114.net brgd
	    email         : brgd@nm114.net
		
		$Id: ldap.php, 19-Apr-06 11:36:50 AM brgd 
		
	 ***************************************************************************/
//Created by brgd
class ldap extends sysClass{
	
	public static function makeObject($med){
		if(self::makeObj(__CLASS__)){
			return new ldap($med);
		}
		return false;
	}
	
	//Constructor
	private $mediator;
	private $ldapObject;
	private $ldapBind;	
	
	public function getLdapObject(){
		$this->ldapObject;
	}
	
	private function ldap($med){
		$this->mediator = $med;
		$this->mediator->registerObject($this);
		
		$this->ldapObject = $this->mediator->connectLdap();
		$this->mediator->rootBindLdap($this->ldapObject);
	}
	
	public function setRootBind($dn,$pass){
		ldap_set_option($this->ldapObject, LDAP_OPT_PROTOCOL_VERSION, 3);
		$lb = ldap_bind($this->ldapObject,$dn,$pass);
		if(!$lb){
			die("Can't bind to ldap server!" . ' -- ' . __METHOD__ . ' ' .  __LINE__);
		}
		$this->ldapBind = $lb;
	}
	
	public function search($dn,$val,$these=null){
		$val = $this->mediator->delsymbol($val);
		
		if($these != null){
			$sr = ldap_search($this->ldapObject,$dn,$val,$these);
		}else{
			$sr = ldap_search($this->ldapObject,$dn,$val);
		}
	
	    $en = ldap_get_entries($this->ldapObject,$sr);
		return $en;
	}
	
	public function ldapList($dn,$val,$these=null){
		$val = $this->mediator->delsymbol($val);
		
		if($these != null){
			$sr = ldap_list($this->ldapObject,$dn,$val,$these);
		}else{
			$sr = ldap_list($this->ldapObject,$dn,$val);	
		}
	
	    $en = ldap_get_entries($this->ldapObject,$sr);
		return $en;
	}
	
	public function getAnEntryOuList($dn){
		$tcon = $this->mediator->getObject($this->mediator->getConfig());
		
		$attri = $this->mediator->getObject($this->mediator->getLang())->getLdapo(0);
		$these = array($attri);
		$sr = ldap_list($this->ldapObject, $dn, "$attri=*", $these);
		$info = ldap_get_entries($this->ldapObject, $sr);
		$tsuben = array();
		for ($i=0; $i<$info["count"]; $i++) {
    		$tsuben[$i] = '<a href="?org=' . urlencode($info[$i][$attri][0]) . '">' . $info[$i][$attri][0] .
    			' (' . $this->getAnSubEntryOuListCount($info[$i][$attri][0]) .  ') </a>';
		}
		return $tsuben;
	}
	
	public function getAnEntryPerList($dn){
		$tcon = $this->mediator->getObject($this->mediator->getConfig());
		
		$disatt = $this->mediator->getObject($this->mediator->getLang())->getLdapp(0);
		$attri  = $this->mediator->getObject($this->mediator->getLang())->getLdapp(3);
		$these = array($disatt,$attri);
		$sr = ldap_list($this->ldapObject, $dn, "$attri=*", $these);
		$info = ldap_get_entries($this->ldapObject, $sr);

		return $info;
	}
	
	public function getAnSubEntryOuListCount($org){
		$tcon = $this->mediator->getObject($this->mediator->getConfig());
		
		$tens   = $this->mediator->searchALdapOu($org);
		if(($tens['count'] > 1) || ($tens['count'] == 0)){
			die("Fotal error!The organization name \"$org\" is not exist or multiple exist!" . ' -- ' . __METHOD__ . ' ' .  __LINE__);
		}
		
		$attri = $this->mediator->getObject($this->mediator->getLang())->getLdapo(0);
		$these = array($attri);
		$sr=ldap_list($this->ldapObject, $tens[0]['dn'], "$attri=*", $these);
		$info = ldap_get_entries($this->ldapObject, $sr);
		
		return count($info) - 1;
	}
	
	public function ifCanBind($dn,$pass){
		ldap_set_option($this->ldapObject, LDAP_OPT_PROTOCOL_VERSION, 3);
		if(@ldap_bind($this->ldapObject,$dn,$pass)){
			$this->mediator->rootBindLdap($this->ldapObject);
			return true;
		}
		return false;
	}
	
	public function addAnEntry($dn,$info){
		if(isset($info['cn'])){
			$info["objectclass"] = "inetorgperson";
		}
		if(isset($info['ou'])){
			$info["objectclass"] = "organizationalunit";
		}
		if($this->ifDnExist($dn)){
			exit("DN already exist!!" . ' -- ' . __METHOD__ . ' ' .  __LINE__);
		}
		if(!ldap_add($this->ldapObject, $dn, $info)){
			exit("ldap error!!" . ' -- ' . __METHOD__ . ' ' .  __LINE__);
		}
	}
	
	public function delAnEntry($dn){
		if(!ldap_delete($this->ldapObject, $dn)){
			exit("ldap error!!" . ' -- ' . __METHOD__ . ' ' .  __LINE__);
		}
	}
	
	public function modifyAnEntry($dn,$info){
		//while( list($k, $v) = each($info)){
		//	echo($k . ' - ' . $v . '<br />');
		//}
		$tcon = $this->mediator->getObject($this->mediator->getConfig());
		@$tempp = $tcon->getLdapPAttr('0');
		if(isset($info[$tempp])){ //if cn attribute exist!
			$info["objectclass"] = $tcon->getPhpcoGlobal('perobjectclass');
		}
		@$tempo = $tcon->getLdapOuAttr('0');
		if(isset($info[$tempo])){  //if ou attribute exist!
			$info["objectclass"] = $tcon->getPhpcoGlobal('ouobjectclass');
		}
		if(is_array($dn)){ //modify an organization dn
			if($dn[0] == $dn[1]){
				if(!ldap_modify($this->ldapObject, $dn[0], $info)){
					exit("ldap error!!" . ' -- ' . __METHOD__ . ' ' .  __LINE__);
				}
			}else{
				$this->moveAllChildEntry($dn[0],$dn[1],$info); //(olddn,newdn,newdninfo)
			}
		}else{ //modify an person
			if(!ldap_modify($this->ldapObject, $dn, $info)){
				exit("ldap error!!" . ' -- ' . __METHOD__ . ' ' .  __LINE__);
			}
		}
		
		return true;
	}
	
	public function delAPerson($dn){
		if(!ldap_delete($this->ldapObject, $dn)){
			exit("ldap error!! ldap delAPerson");
		}
	}
	
	public function ifDnExist($dn){
		$search_result = @ldap_read($this->ldapObject,$dn,'objectClass=*',array('dn'));

		if ($search_result) {
			$num_entries = ldap_count_entries($this->ldapObject,$search_result);
			if ($num_entries > 0) {
				return  true;
			} 
		} 
		return  false;
	}
	
	public function moveAllChildEntry($olddn,$newdn,$info){ //get old entry,copy to new entry,delete old entry
		$movetate  = false; //if move successfull
		$delstate  = false; //if delsuccess successfull
		
		if($this->ifDnExist($newdn)){
			exit("DN already exist!!" . ' -- ' . __METHOD__ . ' ' .  __LINE__);
		}
		
		//get all child entry information
		$sr = ldap_search($this->ldapObject, $olddn, 'objectClass=*'); //get all dns, include $olddn
		
		$entry = ldap_first_entry($this->ldapObject, $sr);
		do{
			$colddn = ldap_get_dn($this->ldapObject,$entry);
			$cnewdn = str_replace($olddn,$newdn,$colddn); //set entry dn
			$corder = (count(explode(',',$colddn)) - count(explode(',',$newdn))); //set ldap add order
			$sid = count(@$child[$corder]);
			$child[$corder][$sid]['olddn'] = $colddn;
			$child[$corder][$sid]['newdn'] = $cnewdn;
			
			$cattri = ldap_get_attributes($this->ldapObject, $entry);; //set entry attributes
			for($i=0; $i<$cattri['count']; $i++){
				for($z=0; $z<$cattri[$cattri[$i]]['count']; $z++){
					$child[$corder][$sid]['attri'][$cattri[$i]][$z] = $cattri[$cattri[$i]][$z];
				}
			}
		}while($entry = ldap_next_entry($this->ldapObject, $entry));
		
		//insert entry into ldap position
		for($i=0; $i<count($child); $i++){
			$fto = $child[$i];
			for($j=0; $j<count($fto); $j++){
				if($i == 0){
					if(!ldap_add($this->ldapObject, $newdn, $info)){
						exit("ldap error!!" . ' -- ' . __METHOD__ . ' ' .  __LINE__);
					}
				}else{
					if(!ldap_add($this->ldapObject, $fto[$j]['newdn'], $fto[$j]['attri'])){
						exit("ldap error!!" . ' -- ' . __METHOD__ . ' ' .  __LINE__);
					}
				}
			}
		}
		
		//delete old entry
		for($i=(count($child)-1); $i>=0; $i--){
			$fto = $child[$i];
			for($j=0; $j<count($fto); $j++){
				if(!ldap_delete($this->ldapObject, $fto[$j]['olddn'])){
					exit("ldap error!!" . ' -- ' . __METHOD__ . ' ' .  __LINE__);
				}
			}
		}
	}
	
	public function ouHasSubEntry($dn){
		$sr = ldap_search($this->ldapObject, $dn, 'objectClass=*'); //get all entry
		$info = ldap_get_entries($this->ldapObject, $sr);
		if($info['count'] == 1){
			return false;
		}
		return true;
	}
	
	public function __destruct() {ldap_unbind($this->ldapObject);}
}

?>
