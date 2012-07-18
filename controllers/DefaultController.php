<?php

class DefaultController extends Controller {

	public function actionIndex() {
		$this->render('index');
	}

	/**
	 * Public login action.  It swallows exceptions from Hybrid_Auth. Comment try..catch to bubble exceptions up. 
	 */
	public function actionLogin() {
		try {
			Yii::app()->session['hybridauth-ref'] = Yii::app()->request->urlReferrer;
			$this->_doLogin();
		} catch (Exception $e) {
			Yii::app()->user->setFlash('hybridauth-error', "Something went wrong, did you cancel?");
			$this->redirect(Yii::app()->session['hybridauth-ref'], true);
		}
	}

	/**
	 * Main mehod to handle login attempts.  If the user passes authentication with their
	 * chosen provider then it displays a form for them to choose their username and email.
	 * The email address they choose is *not* verified.
	 * 
	 * @throws Exception if a provider isn't supplied, or it has non-alpha characters
	 */
	private function _doLogin() {
		if (!isset($_GET['provider']))
			throw new Exception("You haven't supplied a provider");

		if (!ctype_alpha($_GET['provider'])) {
			throw new Exception("Invalid characters in provider string");
		}
		

		$identity = new RemoteUserIdentity($_GET['provider']);
		
		if ($identity->authenticate()) {
			// They have authenticated AND we have a user record for them already => Log them straight in
			$adapter = $identity->getAdapter();
			$this->module->setAdapter($adapter);
			Yii::app()->user->login($identity, 0);
			$this->redirect(Yii::app()->user->returnUrl);
		} else if ($identity->errorCode == RemoteUserIdentity::ERROR_USERNAME_INVALID) {
			// They have authenticated but we haven't seen them before.  Create a user record for them.
			// and display a form to choose their username & email (we might not get it from the provider)
			$user = new User;

			if (isset($_POST['User'])) {
				//Save the form
				$user->attributes = $_POST['User'];
				$user->loginProvider = $identity->loginProvider;
				$user->loginProviderIdentity = $identity->loginProviderIdentity;

				if ($user->validate() && $user->save()) {
					$identity->id = $user->id;
					$identity->username = $user->username;
					$this->module->setAdapter($identity->getAdapter());
					Yii::app()->user->login($identity, 0);
					$this->redirect(Yii::app()->user->returnUrl);
				}
			} else {
				//Display the form with some entries prefilled if we have the info.
				if (isset($identity->userData->email)) {
					$user->email = $identity->userData->email;
					$email = explode('@', $user->email);
					$user->username = $email[0];
				}
			}

			$this->render('createUser', array(
				'user' => $user,
			));
		}
	}

	/** 
	 * Action for URL that Hybrid_Auth redirects to when coming back from providers.
	 * Calls Hybrid_Auth to process login. 
	 */
	public function actionCallback() {
		require dirname(__FILE__) . '/../Hybrid/Auth.php';
		require dirname(__FILE__) . '/../Hybrid/Endpoint.php';
		Hybrid_Endpoint::process();
	}

}