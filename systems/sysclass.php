<?php
	/***************************************************************************
		               debug.php
		           -------------------
		begin         : 11-Apr-06
		copyright     : (C) 2006 The nm114.net brgd
	    email         : brgd@nm114.net
		
		$Id: debug.php, 11-Apr-06 3:39:35 PM brgd 
		
	 ***************************************************************************/
abstract class sysClass{  //include functions use in all classes,like debug
		
	//Begin get methods
	//End get methods
	
	//Begin control every class's instance number
	protected static $instanceNum = array(); //instance number of classes
	
	protected static function makeObj($aname){
		if($aname == NULL){
			exit('Create object need class name! --> sysclass 22');
		}
		if(self::state($aname)){
			return true;
		}else{
			exit('Just can make only one ' . $aname . ' object! --> sysclass 27' );
		}
	}
	
	private static function state($obname){
		$snum = self::$instanceNum;
		$arraynum = count($snum);
		
		for($i = 0; $i < $arraynum; $i++){
			if($snum[$i] == $obname){
				return false;
			}
		}
		$snum[$arraynum] = $obname;
		self::$instanceNum = $snum;		
		return true;
	}
	//End control every class's instance number
	
	protected function debug($obj){
		$oname = get_class($obj);
		$cvars = get_object_vars($obj);
	
		echo "<br /><br /><hr width=\"100%\"><font size=\"4\" color=\"#ff0000\">$oname</font><br />" .
				"&nbsp; Variables :";
		$i = 0;
		foreach ($cvars as $name => $value) {
			if(is_object($value)){
				echo "<br />&nbsp; &nbsp; $i -- <font color=\"#ff0000\">$name</font> => Object - " . get_class($value);
			}else{
				if(is_array($value)){
					$j = 0;
					echo "<br />&nbsp; &nbsp; $i -- <font color=\"#ff0000\">$name</font> => <br />&nbsp; &nbsp; &nbsp; &nbsp; Array -> ";
					foreach ($value as $vname => $vvalue) {
						if(is_object($vvalue)){
							echo "$j -- $vname:" . get_class($vvalue) . "&nbsp; ";
						}else{
							echo("$j- $vname:$vvalue&nbsp; ");
						}
						$j++;
					}
				}else{
					echo "<br />&nbsp; &nbsp; $i -- <font color=\"#ff0000\">$name</font> => $value ";
				}					
			}
    		$i++;
		}
	}
	
}

?>
