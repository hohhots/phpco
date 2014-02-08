<?php
	/***************************************************************************
		               language.php
		           -------------------
		begin         : 20-Apr-06
		copyright     : (C) 2006 The nm114.net brgd
	    email         : brgd@nm114.net
		
		$Id: language.php, 20-Apr-06 10:00:14 AM brgd 
		
	 ***************************************************************************/
abstract class language extends sysClass{
	protected $mediator;
	protected $config;
	protected $lang; 
	protected $langError;
	protected $ldapo;
	protected $ldapp;
	protected $ldap;
	
	protected $action; //menu action function name
	//Constructor
	protected function language($med){
		$this->setMediator($med);
		$this->config = $this->mediator->getObject($this->mediator->getConfig());
		
		$this->lang['waitfor'] = '正在处理 ....';	
		$this->lang['hhht'] = '呼和浩特市';
		$this->lang['addressbook'] = '通讯录';
		$this->lang['position']    = '您的位置：';
		$this->lang['sub']          = '下属';
		$this->lang['organization'] = '机构';
		$this->lang['descriptionguest'] = '<br />本系统上可以查找党政机关所有' . $this->lang['organization'] . '的对外联系方式。';
		$this->lang['descriptionuser'] = $this->lang['descriptionguest'] . '<br />您现在有权查看本单位同事的详细信息！';
		$this->lang['title']  = $this->lang['hhht'] . '党政机关 ' . $this->lang['addressbook'];
		$this->lang['home']   = '首页';     //home page //name
		$this->lang['regist'] = '注册';
		$this->lang['ouperson']  = '员工';
		$this->lang['registou'] = $this->lang['organization'] . $this->lang['regist'];
		$this->lang['registper'] = $this->lang['ouperson'] . $this->lang['regist'];
		$this->lang['login']  = '登录';
		$this->lang['userid']  = '用户';
		$this->lang['pass']    = '密码';
		$this->lang['fail']  = $this->lang['userid'] . '或' . $this->lang['pass'] . '错误！';
		$this->lang['search'] = '搜索';
		$this->lang['SearchTitle'] = $this->lang['search'] . '结果：';
		$this->lang['display']     = '显示';
		$this->lang['info']   = '信息';
		$this->lang['subou']  = $this->lang['sub'] . $this->lang['organization'];
		$this->lang['porgname']   = '上级' . $this->lang['organization'] . '：';
		
		$this->lang['delete']   = '删除';
		
		//user's language
		$this->lang['logout']      = '退出';
		$this->lang['displayuser'] = $this->lang['display'] . $this->lang['ouperson'];
		$this->lang['displayou']   = $this->lang['display'] . $this->lang['organization'];

		
		$this->lang['year']  = '年';
		$this->lang['month'] = '月';
		$this->lang['day']   = '日';
		
		$this->lang['date']         = date("Y" . $this->lang['year'] . "n" . $this->lang['month'] . "j" . $this->lang['day'] . "G:i");
		$this->lang['name']         = '姓名';
		$this->lang['nomatch']      = '没有找到相关' . $this->lang['organization'] . '名称!';
		$this->lang['usernomatch']  = '没有找到相关记录!';
		
		$this->lang['orgname']      = $this->lang['organization'] . '名称：';
		$this->lang['fillup']       = '（必添！）';
		$this->lang['unnecessary']  = '（以下为可选项！）';
		$this->lang['back']         = '返回';
		$this->lang['reset']        = '重写';
		$this->lang['submit']       = '确定';
		$this->lang['shuru']        = '输入';
		
		$this->lang['addper']          = '添加' . $this->lang['ouperson'];
		$this->lang['addou']            = '添加' . $this->lang['organization'];
		
		$this->lang['minpasslen']   = '至少' . $this->config->getPhpcoGlobal('minpasslen') . '位 ';
		
		$this->lang['regcode']          = '注册码';
						
		//ldap organization unit attributes name
		$tcon = $this->config;
		$this->ldapr = $tcon->getLdapOAttr(); //root dn name objectclass 'o'
		$this->ldapo = $tcon->getLdapOuAttr();
		$this->ldapp = $tcon->getLdapPAttr();
		
		//organization unit
		$this->oldap[$this->ldapo[0]] = '名称：';
		$this->oldap[$this->ldapo[1]] = '电话：';
		$this->oldap[$this->ldapo[2]] = '传真：';
		$this->oldap[$this->ldapo[3]] = '地址：';
		$this->oldap[$this->ldapo[4]] = '邮编：';
		$this->oldap[$this->ldapo[5]] = '简介：';
		
		//organization person
		$this->pldap[$this->ldapp[0]]  = '全名：';
		$this->pldap[$this->ldapp[5]]  = '办公电话：';
		$this->pldap[$this->ldapp[6]]  = '传真：';
		$this->pldap[$this->ldapp[7]]  = '家庭电话：';
		$this->pldap[$this->ldapp[8]]  = '电子邮箱：';
		$this->pldap[$this->ldapp[9]]  = '手机：';
		$this->pldap[$this->ldapp[10]] = '房间号码：';
		$this->pldap[$this->ldapp[11]] = $this->lang['organization'] . '地址：';
		$this->pldap[$this->ldapp[12]] = '邮编：';
		$this->pldap[$this->ldapp[13]] = '家庭住址：';
		$this->pldap[$this->ldapp[14]] = '科室：';
		$this->pldap[$this->ldapp[15]] = '照片：';
		$this->pldap[$this->ldapp[16]] = '自我' . $this->oldap[$this->ldapo[5]];
	}
	
	protected function setMediator($med){
		$this->mediator = $med;
		$this->mediator->registerObject($this);
	}
	
	public function getLang($val){
		if(isset($this->lang[$val])){
			return $this->lang[$val];
		}
	}
	
	public function getLangError($val){
		if(isset($this->langError[$val])){
			return $this->langError[$val];
		}
	}
	
	public function getOLdap($val){
		if(isset($this->oldap[$val])){
			return $this->oldap[$val];
		}
	}
	
	public function getPLdap($val){
		if(isset($this->pldap[$val])){
			return $this->pldap[$val];
		}
	}
	
	public function getLdapr($val){
		if(isset($this->ldapr[$val])){
			return $this->ldapr[$val];
		}
		return $this->ldapr;
	}
	
	public function getLdapo($val){
		if(isset($this->ldapo[$val])){
			return $this->ldapo[$val];
		}
		return $this->ldapo;
	}
	
	public function getLdapp($val){
		if(isset($this->ldapp[$val])){
			return $this->ldapp[$val];
		}
		return $this->ldapp;
	}
	
	public function getAllLdapo(){
		return $this->ldapo;
	}
	
	public function getActionName($target){return $this->action[$target];}
	
	public function getActionCount(){return count($this->action);}
	
}

?>
