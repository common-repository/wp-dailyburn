<?php
class DailyBurn
{
	// internal constant to enable/disable debugging
	const DEBUG = false;
	// url for the dailyburn api
	const DAILYBURN_API_URL = 'https://dailyburn.com/api';
	// port for the dailyburn api
	const DAILYBURN_API_PORT = 443;	
	// api key
	const DAILYBURN_API_KEY = 'gxMYxfqX1FaUTBCRvoEaA';
	// api timeout
	const TIMEOUT = 60;	
	// user agent
	const USER_AGENT = 'WP-DailyBurn/1.0.2';
	
	private $username;
	private $password;

// class methods
	
	/**
	 * Default constructor
	 *
	 * @return	void
	 * @param	string $username
	 * @param	string $password
	 */
	public function __construct($username, $password)
	{
		$this->username = $username;
		$this->password = $password;
	}

	/**
	 * Make the call
	 *
	 * @return	string
	 * @param	string $url
	 * @param	array[optiona] $aParameters
	 * @param	bool[optional] $authenticate
	 */
	private function doCall($url, $aParameters = array(), $authenticate = false, $usePost = false)
	{
		// redefine
		$url = (string) $url;
		$aParameters = (array) $aParameters;
		$authenticate = (bool) $authenticate;
		
		// build url
		$url = self::DAILYBURN_API_URL .'/'. $url;
		
		// add api key parameter
		$aParameters['key'] = self::DAILYBURN_API_KEY;

		// validate needed authentication
		if($authenticate && ($this->username == '' || $this->password == ''))
			return null;

		// rebuild url if we don't use post
		if(!empty($aParameters) && !$usePost)
		{
			// init var
			$queryString = '';

			// loop parameters and add them to the queryString
			foreach($aParameters as $key => $value) 
				$queryString .= '&'. $key .'='. urlencode(utf8_encode($value));

			// cleanup querystring
			$queryString = trim($queryString, '&');

			// append to url
			$url .= '?'. $queryString;
		}
		
		// set options
		$options[CURLOPT_URL] = $url;
		$options[CURLOPT_PORT] = self::DAILYBURN_API_PORT;
		$options[CURLOPT_USERAGENT] = self::USER_AGENT;
		$options[CURLOPT_SSL_VERIFYPEER] = false;
		$options[CURLOPT_FOLLOWLOCATION] = false;
		$options[CURLOPT_RETURNTRANSFER] = true;
		$options[CURLOPT_TIMEOUT] = (int) self::TIMEOUT;		
		
		// should we authenticate?
		if($authenticate)
		{
			$options[CURLOPT_HTTPAUTH] = CURLAUTH_ANY;
			$options[CURLOPT_USERPWD] = $this->username . ':' . $this->password;
		}

		// are there any parameters?
		if(!empty($aParameters) && $usePost)
		{
			// rebuild parameters
			foreach($aParameters as $key => $value) 
				$aParameters[$key] = utf8_encode($value);

			// set extra options
			$options[CURLOPT_POST] = true;
			$options[CURLOPT_POSTFIELDS] = $aParameters;
		}

		// init
		$curl = curl_init();

		// set options
		curl_setopt_array($curl, $options);

		// execute
		$response = curl_exec($curl);
		$headers = curl_getinfo($curl);
		
		// fetch errors
		$errorNumber = curl_errno($curl);
		$errorMessage = curl_error($curl);

		// close
		curl_close($curl);

		// first fix the field names so they can be translated to object properties properly
		$response = str_replace('-', '_', $response);
		$response = str_replace('UTF_8', 'UTF-8', $response);
		
		// validate body
		$xml = @simplexml_load_string($response);
		if($xml !== false && isset($xml->error))
			return null;

		// invalid headers
		if(!in_array($headers['http_code'], array(0, 200)))
		{
			// should we provide debug information
			if(self::DEBUG)
			{
				// make it output proper
				echo '<pre>';

				// dump the header-information
				var_dump($headers);

				// dump the raw response
				var_dump($response);

				// end proper format
				echo '</pre>';

				// stop the script
				exit;
			}

			return null;
		}

		if($errorNumber != '')
			return null;

		return $response;
	}
	
	public function getUserProfile()
	{
		// do the call
		$response = $this->doCall('users/current.xml', null, true);

		// convert into xml-object
		$xml = @simplexml_load_string($response);

		if ($xml == false)
			return null;
			
		return $xml;
	}
	
	private function debugMessage($msg)
	{
		if (self::DEBUG)
		{
			echo $msg;
		}
	}
}
?>
