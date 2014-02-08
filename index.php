<?php
	/***************************************************************************
		               index.php
		           -------------------
		begin         : 9-Apr-06
		copyright     : (C) 2006 The nm114.net brgd
	    email         : brgd@nm114.net
		
		$Id: index.php, 9-Apr-06 10:21:16 PM brgd 
		
	 ***************************************************************************/
require_once("systems/sysclass.php");
	
class phpCo extends sysClass{
	//Begin constructor
	private $mediator; 
	private $globalConfig; //new GlobalConfig()
	 
	public function phpCo(){
		$sysDir = "systems/";
		$fm = $sysDir . "mediator.php";
		$fc = $sysDir . "config.php";
		
		$this->incfile($fm);
		$this->incfile($fc);
		
		$this->mediator = Mediator::makeObject();
		$this->globalConfig = globalConfig::makeObject($this->mediator,$sysDir);
		
		$this->mediator->displayHtml();
		
		$this->mediator->debugClass();
	}
	//End constructor
	
	private function incfile($fname){ //include necessary files
		if(file_exists($fname)){
			require_once($fname);
		}else{
			exit('<font color="#ff0000" size="6">Can\'t find the file ' . $fname . '!</font>');
		}
	}
}

$index = new phpCo();	

?>
