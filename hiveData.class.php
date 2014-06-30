<?php

// Access to Hive Data

Class hiveData {
 
	var $username	= 'user';
	var $password 	= 'pass';
	var $url 		= 'https://www.hivehome.com';
	var $sessionCookie;
	
	function __construct() {
    	$this->_login();
    }
	
	function _objectifyJSON ($json) {
    	return json_decode($json, false);
    }
                
    function _login() {
    	$this->sessionCookie = tempnam ("/tmp", "CURLCOOKIE");
    	$curlHandle = curl_init("$this->url/login");  	
    	curl_setopt ($curlHandle, CURLOPT_COOKIEJAR, $this->sessionCookie);
    	curl_setopt ($curlHandle, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=UTF-8'));
		curl_setopt ($curlHandle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt ($curlHandle, CURLOPT_POST, true);
		curl_setopt ($curlHandle, CURLOPT_POSTFIELDS, '{"username":"'.$this->username.'","password":"'.$this->password.'"}');
		$data = curl_exec ($curlHandle);
		// checked we're logged in
		preg_match('!Moved Temporarily. Redirecting to /myhive/dashboard!', $data, $cap);
		if ($cap[0] == "Moved Temporarily. Redirecting to /myhive/dashboard") $loggedin = true;
		else $loggedin = false;

		return $loggedin;
    }
    
    function logout() {
  		$data = $this->getPage("$this->url/logout");
		// checked we're logged out
		preg_match('!Moved Temporarily. Redirecting to <a href="/">/</a>!', $data, $cap);
		if ($cap[0] == 'Moved Temporarily. Redirecting to <a href="/">/</a>') $loggedin = false;
		else $loggedin = true;

		return $loggedin;
    }

    // weather, inside and outside temp
    function getWeather() {
		$JSONresponse = $this->getPage("$this->url/myhive/weather");
		return $this->_objectifyJSON($JSONresponse);
    }
    // heating schedule
    function getSchedule() {
		$JSONresponse = $this->getPage("$this->url/myhive/heating/schedule");
		return $this->_objectifyJSON($JSONresponse);
    }
    // heating controls
    function getControls() {
		$JSONresponse = $this->getPage("$this->url/myhive/heating/controls");
		return $this->_objectifyJSON($JSONresponse);
    }
    // heating target
    function getTarget() {
		$JSONresponse = $this->getPage("$this->url/myhive/heating/target");
		return $this->_objectifyJSON($JSONresponse);
    }
    // temp history
    function getTempHistory($time = "today") {
    	// time can equal: today, yesterday, thisWeek, lastWeek, thisMonth, lastMonth, thisYear
		$JSONresponse = $this->getPage("$this->url/myhive/history/".$time);
		return $this->_objectifyJSON($JSONresponse);
    }
    // general get page handler (once session up)
    function getPage($url) {
    	$curlHandle = curl_init ($url);
		curl_setopt ($curlHandle, CURLOPT_COOKIEFILE, $this->sessionCookie);
		curl_setopt ($curlHandle, CURLOPT_HTTPHEADER, array('Accept: application/json, text/javascript, */*; q=0.01'));
		curl_setopt ($curlHandle, CURLOPT_HTTPHEADER, array('X-Requested-With: XMLHttpRequest'));
		curl_setopt ($curlHandle, CURLOPT_RETURNTRANSFER, true);
		$JSONresponse = curl_exec ($curlHandle);
		//print_r($JSONresponse);
		return $JSONresponse;
    }
} //end class
?>