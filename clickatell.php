<?php
/**
 * Clickatell class
 *
 * This source file can be used to communicate with Clickatell (http://clickatell.be)
 *
 * The class is documented in the file itself. If you find any bugs help me out and report them. Reporting can be done by sending an email to php-clickatell-bugs[at]verkoyen[dot]eu.
 * If you report a bug, make sure you give me enough information (include your code).
 *
 * License
 * Copyright (c) 2008, Tijs Verkoyen. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
 * 3. The name of the author may not be used to endorse or promote products derived from this software without specific prior written permission.
 *
 * This software is provided by the author "as is" and any express or implied warranties, including, but not limited to, the implied warranties of merchantability and fitness for a particular purpose are disclaimed. In no event shall the author be liable for any direct, indirect, incidental, special, exemplary, or consequential damages (including, but not limited to, procurement of substitute goods or services; loss of use, data, or profits; or business interruption) however caused and on any theory of liability, whether in contract, strict liability, or tort (including negligence or otherwise) arising in any way out of the use of this software, even if advised of the possibility of such damage.
 *
 * @author		Tijs Verkoyen <php-clickatell@verkoyen.eu>
 * @version		1.0
 *
 * @copyright	Copyright (c) 2008, Tijs Verkoyen. All rights reserved.
 * @license		BSD License
 */
class Clickatell
{
	// internal constant to enable/disable debugging
	const DEBUG = false;

	// url for the api
	const API_URL = 'http://api.clickatell.com/http';

	// port for the api
	const API_PORT = 80;

	// current version
	const VERSION = '1.0';


	/**
	 * The API-id
	 *
	 * @var	string
	 */
	private $apiId;


	/**
	 * The API-password
	 *
	 * @var	string
	 */
	private $password;


	/**
	 * The timeout
	 *
	 * @var	int
	 */
	private $timeOut = 60;


	/**
	 * The API-username
	 *
	 * @var	string
	 */
	private $user;


	/**
	 * The UserAgent
	 *
	 * @var	string
	 */
	private $userAgent;


// class methods
	/**
	 * Default constructor
	 *
	 * @return	void
	 * @param	string[optional] $key
	 */
	public function __construct($apiId, $username, $password)
	{
		// set properties
		$this->setApiId($apiId);
		$this->setUser($username);
		$this->setPassword($password);
	}


	/**
	 * Make the call
	 *
	 * @return	string
	 * @param	string $method
	 * @param	array[optional] $aParameters
	 */
	private function doCall($method, $aParameters = array())
	{
		// redefine
		$method = (string) $method;
		$aParameters = (array) $aParameters;

		// build url
		$url = self::API_URL .'/'. $method;

		// rebuild url if we don't use post
		if(!empty($aParameters))
		{
			// init var
			$queryString = '?';

			// loop parameters and add them to the queryString
			foreach($aParameters as $key => $value) $queryString .= $key .'='. urlencode($value) .'&';

			// append to url
			$url .= trim($queryString, '&');
		}

		// set options
		$options[CURLOPT_URL] = $url;
		$options[CURLOPT_PORT] = self::API_PORT;
		$options[CURLOPT_USERAGENT] = $this->getUserAgent();
		$options[CURLOPT_FOLLOWLOCATION] = true;
		$options[CURLOPT_RETURNTRANSFER] = true;
		$options[CURLOPT_TIMEOUT] = (int) $this->getTimeOut();

		// init
		$curl = curl_init();

		// set options
		curl_setopt_array($curl, $options);

		// execute
		$response = curl_exec($curl);
		$headers = curl_getinfo($curl);

		// close
		curl_close($curl);

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

			// throw error
			throw new ClickatellException('Invalid response-headers ('. $headers['http_code'] .')', (int) $headers['http_code']);
		}

		// validate response
		if(substr($response, 0, 3) == 'ERR')
		{
			// split
			$chunks = explode(',', substr($response, 4));

			// throw exceptions
			if(isset($chunks[0], $chunks[1])) throw new ClickatellException(trim($chunks[1], trim($chunks[0])));
			throw new ClickatellException('Invalid response');
		}

		// return
		return $response;
	}


	/**
	 * Get the API-key
	 *
	 * @return	string
	 */
	private function getApiId()
	{
		return $this->apiId;
	}


	/**
	 * Get the API-password
	 *
	 * @return	string
	 */
	private function getPassword()
	{
		return $this->password;
	}


	/**
	 * Get the timeout
	 *
	 * @return	int
	 */
	public function getTimeOut()
	{
		return (int) $this->timeOut;
	}




	/**
	 * Get the API-user
	 *
	 * @return	string
	 */
	private function getUser()
	{
		return $this->user;
	}


	/**
	 * Get the useragent
	 *
	 * @return	string
	 */
	public function getUserAgent()
	{
		return (string) 'PHP Clickatell/'. self::VERSION .' '. $this->userAgent;
	}


	/**
	 * Set the API-id
	 *
	 * @return	void
	 * @param	string $apiId
	 */
	public function setApiId($apiId)
	{
		$this->apiId = (string) $apiId;
	}


	/**
	 * Set the API-password
	 *
	 * @return	void
	 * @param	string $password
	 */
	public function setPassword($password)
	{
		$this->password = (string) $password;
	}


	/**
	 * Set the timeout
	 *
	 * @return	void
	 * @param	int $seconds
	 */
	public function setTimeOut($seconds)
	{
		$this->timeOut = (int) $seconds;
	}


	/**
	 * Set the API-user
	 *
	 * @return	void
	 * @param	string $user
	 */
	public function setUser($user)
	{
		$this->user = (string) $user;
	}


	/**
	 * Set the user-agent for you application
	 * It will be appended to ours
	 *
	 * @return	void
	 * @param	string $userAgent
	 */
	public function setUserAgent($userAgent)
	{
		$this->userAgent = (string) $userAgent;
	}


// API methods

	/**
	 * Get a session-id
	 *
	 * In order to deliver a message, the system needs to authenticate the request as coming from a valid source.
	 * You can have multiple sessions open, however the session ID will expire after fifteen minutes of inactivity.
	 * This session ID must be used with all future commands to the API, unless you authenticate each time within the command itself.
	 *
	 * @return	string
	 */
	public function authenticate()
	{
		// build parameters
		$aParameters['api_id'] = $this->getApiId();
		$aParameters['user'] = $this->getUser();
		$aParameters['password'] = $this->getPassword();

		// make the call
		$response = $this->doCall('auth', $aParameters);

		// process the response
		if(substr($response, 0, 2) == 'OK') return substr($response, 4);

		// fallback
		throw new ClickatellException('Invalid response');
	}


	/**
	 * Ping command
	 * This command prevents the session ID from expiring in periods of inactivity. The session ID is set to expire after 15 minutes of inactivity.
	 * You may have multiple concurrent sessions using the same session ID.
	 *
	 * @return	void
	 * @param	string $sessionId
	 */
	public function ping($sessionId)
	{
		// redefine
		$sessionId = (string) $sessionId;

		// build parameters
		$aParameters['session_id'] = $sessionId;

		// make the call
		$response = $this->doCall('ping', $aParameters);

		// process the response
		return (bool) (substr($response, 0, 2) == 'OK');
	}


	/**
	 * Send a message (simple)
	 *
	 * One can send to multiple destination addresses by using an array of addresses A maximum of 100 comma separated destination addresses per sendmsg,
	 * Each message returns a unique identifier in the form of an API message ID. This can be used to track and monitor any given message.
	 *
	 * @return	array
	 * @param	string $sessionId
	 * @param	array $aPhoneNumbers	The addresses to which the message must be delivered should be in international number format. (No 00 prefix or leading +symbol should be used.)
	 * @param	string $text			The text content of the message. Note that some characters take up two characters because of GSM encoding standards
	 *
	 *
	 */
	public function sendMessage($sessionId, $aPhoneNumbers, $text)
	{
		// redefine
		$sessionId = (string) $sessionId;
		$aPhoneNumbers = (array) $aPhoneNumbers;
		$text = (string) $text;

		// validate parameters
		foreach($aPhoneNumbers as $phoneNumber)
		{
			if(!preg_match("/^[0-9]+$/", $phoneNumber)) throw new ClickatellException('Invalid phonenumber ('. $phoneNumber .').');
		}
		if(count($aPhoneNumbers) > 100) throw new Clickatell('maximum 100 phonenumbers allowed.');

		// build parameters
		$aParameters['session_id'] = $sessionId;
		$aParameters['to'] = implode(',', $aPhoneNumbers);
		$aParameters['text'] = utf8_decode($text);

		// make the call
		$response = $this->doCall('sendmsg', $aParameters);

		// split response into lines
		$aLines = explode("\n", trim($response));

		// init var
		$aReturn = array();

		foreach($aLines as $line)
		{
			$line = str_replace(array('ID: ', 'To: '), '', $line);
			$chunks = explode(' ', $line);

			// add to return array
			if(isset($chunks[1])) $aReturn[$chunks[1]] = $chunks[0];
			else $aReturn[] = $chunks[0];
		}

		Spoon::dump($aReturn);
	}


	/**
	 * This will return the number of credits available on this particular account.
	 *
	 * @return	double
	 * @param	string $sessionId
	 */
	public function queryBalance($sessionId)
	{
		// redefine
		$sessionId = (string) $sessionId;

		// build parameters
		$aParameters['session_id'] = $sessionId;

		// make the call
		$response = $this->doCall('getbalance', $aParameters);

		// process the response
		return (double) substr($response, 8);
	}


	/**
	 * Returns the status of a message. You can query the status with either the messageId that was returned by sendMessage
	 *
	 * @return	array
	 * @param	string $sessionId
	 * @param	string $messageId
	 */
	public function queryMessage($sessionId, $messageId)
	{
		// init var
		$aMessageStatuses = array('001' => array('code' => '001', 'description' => 'Message unknown The message ID is incorrect or reporting is delayed.'),
									'002' => array('code' => '002', 'description' => 'Message queued', 'detail' => 'The message could not be delivered and has been queued for attempted redelivery.'),
									'003' => array('code' => '003', 'description' => 'Delivered to gateway', 'detail' => 'Delivered to the upstream gateway or network (delivered to the recipient).'),
									'004' => array('code' => '004', 'description' => 'Received by recipient', 'detail' => 'Confirmation of receipt on the handset of the recipient.'),
									'005' => array('code' => '005', 'description' => 'Error with message', 'detail' => 'There was an error with the message, probably caused by the content of the message itself.'),
									'006' => array('code' => '006', 'description' => 'User cancelled message delivery', 'detail' => 'The message was terminated by a user (stop message command) or by our staff.'),
									'007' => array('code' => '007', 'description' => 'Error delivering message', 'detail' => 'An error occurred delivering the message to the handset.'),
									'008' => array('code' => '008', 'description' => 'OK', 'detail' => 'Message received by gateway.'),
									'009' => array('code' => '009', 'description' => 'Routing error', 'detail' => 'The routing gateway or network has had an error routing the message.'),
									'010' => array('code' => '010', 'description' => 'Message expired', 'detail' => 'Message has expired before we were able to deliver it to the upstream gateway. No charge applies.'),
									'011' => array('code' => '011', 'description' => 'Message queued for later delivery', 'detail' => 'Message has been queued at the gateway for delivery at a later time (delayed delivery).'),
									'012' => array('code' => '012', 'description' => 'Out of credit', 'detail' => 'The message cannot be delivered due to a lack of funds in your account. Please re-purchase credits.'));

		// redefine
		$sessionId = (string) $sessionId;
		$messageId = (string) $messageId;

		// build parameters
		$aParameters['session_id'] = $sessionId;
		$aParameters['apimsgid'] = $messageId;

		// make the call
		$response = $this->doCall('querymsg', $aParameters);

		// process response
		$chunks = explode('Status: ', $response);

		// validate response
		if(!isset($chunks[1]) && !isset($aMessageStatuses[$chunks[1]])) throw new ClickatellException('Invalid response');

		// return the message
		return $aMessageStatuses[$chunks[1]];
	}
}


/**
 * Clickatell Exception class
 *
 * @author		Tijs Verkoyen <php-clickatell@verkoyen.eu>
 */
class ClickatellException extends Exception
{
}

?>