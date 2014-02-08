<?php
	/***************************************************************************
		               cookie.php
		           -------------------
		begin         : 12-Apr-06
		copyright     : (C) 2006 The nm114.net brgd
	    email         : brgd@nm114.net
		
		$Id: cookie.php, 12-Apr-06 7:36:30 PM brgd 
		
	 ***************************************************************************/
class cookie extends sysClass{
	public static function makeObject($med,$target){
		if(self::makeObj(__CLASS__)){
			return new cookie($med,$target);
		}
		return false;
	}
	
	//Constructor
	private $mediator;
	
	private function cookie($med,$target){
		$this->mediator = $med;
		$this->mediator->registerObject($this);
		
		$this->checkCookie($target);
	}
	
	//set variables
	private $userId;
	private $userType;
	private $cookieVal;
	private $browser;
	private $userIp;
	private $law;
	private $lastVisitTime;
	
	private function checkCookie($target)
	{
		//client has cookie - cookie exist in db - if not login     - login        - do something - logout;
		$tcookie = '';
		$tcookie = $this->mediator->hasCookieAndExistInDB();
		if($tcookie != ''){
			if($this->mediator->ifLogin()){
				$this->mediator->userLogin($tcookie);
			}
			if($this->mediator->alreadyLoginAndToLogout($tcookie)){
				$this->mediator->userLogout($tcookie);
			}
			
		}else{
			if(($target != 'home') && ($target != 'empty')){
				$this->mediator->redirector(null,false);
				exit;
			}
			$tcookie = $this->mediator->setFirstCookie();
		}
		$this->mediator->setCookieVal($tcookie);
	}

	public function setUserId($id){
		$this->userId = $id;
	}
	public function setUserType($type){
		$this->userType = $type;
	}
	public function setCookieVal($id){
		$this->cookieVal = $id;
	}
	public function setBrowser($b){
		$this->browser = $b;
	}
	public function setUserIp($ip){
		$this->userIp = $ip;
	}
	public function setLaw($law){
		$this->law = $law;
	}
	public function setLastVisitTime($time){
		$this->lastVisitTime= $time;
	}
	
	public function getUserId(){
		return $this->userId;
	}
	public function getUserType(){
		return $this->userType;
	}
	public function getCookieVal(){
		return $this->cookieVal;
	}
	public function getBrowser(){
		return $this->browser;
	}
	public function getUserIp(){
		return $this->decode_Ip($this->userIp);
	}
	public function getLaw(){
		return $this->law;
	}
	public function getLastVisitTime(){
		return $this->lastVisitTime;
	}
		
}

?>
