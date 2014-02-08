<?php
	/***************************************************************************
		               adminlang.php
		           -------------------
		begin         : Jul 5, 2006
		copyright     : (C) 2006 The nm114.net brgd
	    email         : brgd@nm114.net
		
		$Id: adminlang.php, Jul 5, 2006 1:29:19 AM brgd 
		
	 ***************************************************************************/
require_once("userlang.php");

class adminLang extends userLang{
	public static function makeObject($med){
		if(self::makeObj(__CLASS__)){
			return new adminLang($med);
		}
		return false;
	}
	
	//Constructor
	private function adminLang($med){
		parent::__construct($med);
		
		$this->lang['checkpertitle'] =  '验证' .  $this->lang['ouperson'];
		
		$this->action['logout']  = '退出';
		$this->action['search'] = $this->lang['search'];
		$this->action['registou']  = $this->lang['addou'];
		$this->action['registper'] = $this->lang['addper'];
		$this->action['deleteou']    = $this->lang['delete'] . $this->lang['organization'];
		$this->action['deleteper']   = $this->lang['delete'] . $this->lang['ouperson'];
		$this->action['checkpertitle']   = $this->lang['checkpertitle'] ;
		$this->action['checkoutitle']    = '验证' .  $this->lang['organization'];
		
		$this->lang['perregistok']     = 'Ok！' . $this->lang['ouperson'] . '输入成功完成！';
		$this->lang['ouregistok']       = 'Ok！' . $this->lang['organization'] . '输入成功完成！';
		
		$this->langError['peralreadyexisterr'] = '错误！此' . $this->lang['ouperson'] . '已存在！';
		$this->langError['ounotexist']               = '此' . $this->lang['organization'] . '不存在！';
		$this->langError['notparentorg']          = '错误！' . $this->langError['ounotexist'];
		$this->langError['porgnotexist']           = '错误！此' . $this->lang['organization'] . '上级' . $this->lang['organization'] . '不存在' . '！';
		
		
	}
}
?>