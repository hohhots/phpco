<?php
	/***************************************************************************
		               userlang.php
		           -------------------
		begin         : 20-Apr-06
		copyright     : (C) 2006 The nm114.net brgd
	    email         : brgd@nm114.net
		
		$Id: userlang.php, 20-Apr-06 11:55:48 AM brgd 
		
	 ***************************************************************************/
require_once("guestlang.php");

class userLang extends guestLang{
	public static function makeObject($med){
		if(self::makeObj(__CLASS__)){
			return new userLang($med);
		}
		return false;
	}
	
	//Constructor
	protected function userLang($med){
		parent::__construct($med);
		
		$this->lang['checkpertitle'] = '激活' .  $this->lang['regcode'];
		
		//set all action name
		$this->action['home']      = $this->lang['home'];     //home page //name
		$this->action['logout']    = $this->lang['logout'];
		$this->action['search']    = $this->lang['search'];
		$this->action['create']     = '创建';
		$this->action['editou']      = '编辑'  . $this->lang['organization'];
		$this->action['editper']     = '编辑'  . $this->lang['ouperson'];
		$this->action['checkper']  = $this->lang['checkpertitle'] ;
		
		$this->langError['ouediterr']                 = '错误！无法编辑此' . $this->lang['organization'] . '！';
		$this->langError['oualreadyediterr']   = '错误！此' . $this->lang['organization'] . '最近已被编辑！';
		$this->langError['idnotexist']                = '错误！此' . $this->lang['regcode'] . '不存在' . '！';
	}
}

?>
