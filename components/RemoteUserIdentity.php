<?php

class RemoteUserIdentity extends CBaseUserIdentity {

	public $id;
	public $userData;
	public $username;
	public $loginProvider;
	public $loginProviderIdentity;
	private $_adapter;

	/**
	 * @param string the provider you are using
	 */
	public function __construct($provider) {
		$this->loginProvider = $provider;
	}

	/**
	 * Authenticates a user.
	 * @return boolean whether authentication succeeds.
	 */
	public function authenticate() {
		require dirname(__FILE__) . '/../Hybrid/Auth.php';
		
		if (strtolower($this->loginProvider) == 'openid') {
			if (!isset($_GET['openid-identity'])) {
				throw new Exception('You chose OpenID but didn\'t provide an OpenID identifier');
			} else {
				$params = array( "openid_identifier" => $_GET['openid-identity']);
			}
		} else {
			$params = array();
		}
		
		$hybridauth = new Hybrid_Auth($this->_getConfig());

		$adapter = $hybridauth->authenticate($this->loginProvider,$params);
		if ($adapter->isUserConnected()) {
			$this->_adapter = $adapter;
			$this->loginProviderIdentity = $this->_adapter->getUserProfile()->identifier;

			$user = User::model()->find(
				'loginProvider = ? AND loginProviderIdentity = ? ', array($this->loginProvider, $this->loginProviderIdentity)
			);

			if ($user == null) {
				$this->errorCode = self::ERROR_USERNAME_INVALID;
			} else {
				$this->id = $user->id;
				$this->username = $user->username;
				$this->errorCode = self::ERROR_NONE;
			}
			return $this->errorCode == self::ERROR_NONE;
		}
	}

	/**
	 * @return integer the ID of the user record
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return string the username of the user record
	 */
	public function getName() {
		return $this->username;
	}
	
	/**
	* Get config
	* @return string rewritten configuration
	*/
	private function _getConfig() {
		return Yii::app()->controller->module->getConfig();
	}

	/**
	 * Returns the Adapter provided by Hybrid_Auth.  See http://hybridauth.sourceforge.net
	 * for details on how to use this
	 * @return Hybrid_Provider_Adapter adapter
	 */
	public function getAdapter() {
		return $this->_adapter;
	}
}