<?php

namespace Controller;

use \Manager\UserManager;
use \W\Controller\Controller;
use \W\Security\AuthentificationManager;
use \W\Security\StringUtils;



class UserController extends DefaultController
{

	public function register()
	{
		$userManager = new UserManager;
		$authentificationManager = new AuthentificationManager;

		$last_name = "";
		$first_name = "";
		$username = "";
		$email = "";
		$password = "";
		$confirmPassword = "";
		$zip_code = "";
		for ($i=75001; $i < 75021; $i++) { 
			$zip[] = $i;
		}
		for ($i=1; $i < 10; $i++) { 
			$zip[] = '75 00'.$i;
		}
		for ($i=10; $i < 21; $i++) { 
			$zip[] = '75 0'.$i;
		}

		$address = "";
		$phone_number = "";

		$usernameError = "";
		$emailError = "";
		$zip_codeError = "";
		$passwordError = "";
		
		
		if (!empty($_POST))
		{
			foreach ($_POST as $k => $v)
			{
				$$k = trim(strip_tags($v));
			}

			if (strlen($username) < 4)
			{
				$usernameError = "Pseudo trop court !";
			}

			if ($userManager->usernameExists($username))
			{
				$usernameError = "Pseudo déjà utilisé !";
			}

			if (!filter_var($email, FILTER_VALIDATE_EMAIL))
			{
				$emailError = "Email non valide";
			}

			if ($userManager->emailExists($email))
			{
				$emailError = "Email déjà utilisé !";
			}

			if (!in_array($zip_code, $zip)) {
				$zip_codeError = "Vous devez indiquer un code postal parisien !";
			}

			if ($password != $confirmPassword)
			{
				$passwordError = "le mot de passe ne correspond pas !";
			}
			else if (strlen($password) < 6)
			{
				$passwordError = "Veuillez saisir un mot de passe d'au moins 7 caractere !";
			}
			else
			{
				$containsLetter = preg_match('/[a-zA-Z]/', $password);
				$containsDigit = preg_match('/\d/', $password);
				$containsSpecial = preg_match('/[^a-zA-Z\d]/', $password);

				if (!$containsLetter || !$containsDigit || !$containsSpecial)
				{
					$passwordError = "Veuillez choisir un mot de passe avec au moins une lettre, un chiffre, un caractere special !";
				}
			}

			if (empty($usernameError) && empty($emailError) && empty($zip_codeError) && empty($passwordError)) {
		
				$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

				// Recherche des coordonnées de l'utilisateur

				$googleAddress = urlencode($address . ", " . $zip_code ." Paris");

				$response = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?address=".$googleAddress);
				$arrayResponse = json_decode($response, true);

				$lat = NULL;
				$lng = NULL;

				if(!empty($arrayResponse['results'][0])){

						$lat = $arrayResponse['results'][0]['geometry']['location']['lat'];
						$lng = $arrayResponse['results'][0]['geometry']['location']['lng'];

				}

				$newUser = [

					'last_name' 	=> $last_name,
					'first_name' 	=> $first_name,
					'username' 		=> $username,
					'email' 		=> $email,
					'password' 		=> $hashedPassword,
					'zip_code'      => $zip_code,
					'address'       => $address,
					'lat'			=> $lat,
					'lng'			=> $lng,
					'phone_number'  => $phone_number,
					'role' 			=> 'client',
					'date_created'  => date('Y-m-d H:i:s'),
					'date_modified' => date('Y-m-d H:i:s')
				];

				$id = $userManager->insert($newUser);

				if(!empty($id)){
					$newUser['id'] = $id;
					$authentificationManager->logUserIn($newUser);
					$this->redirectToRoute('catalog');
				}
			}
		}

		$data = [
			'last_name' => $last_name, 
			'first_name' => $first_name, 
			'username' => $username, 
			'email' => $email,
			'zip_code' => $zip_code,
			'address' => $address,
			'phone_number' => $phone_number,
			'usernameError' => $usernameError,
			'emailError' => $emailError,
			'passwordError' => $passwordError,
			'zip_codeError' => $zip_codeError,
			];
		$this->show('user/register', $data);
	}

	public function login()
	{
		$authentificationManager = new AuthentificationManager;

		$username = "";
		$password = "";
		$error = "";
		$data = [];

		if (!empty($_POST))
		{
			$username = $_POST['username'];
			$password = $_POST['password'];

			$result = $authentificationManager->isValidLoginInfo($username, $password);

			if ($result > 0){
					$userId = $result;
					
					$userManager = new \Manager\UserManager();
					$user = $userManager->find($userId);
					
					$authentificationManager->logUserIn($user);

					$this->redirectToRoute('catalog');
				}
				else 
				{
					$error = "Mauvais identifiant !";
				}
		}

		$data['error'] = $error;
		$data['username'] = $username;

		$this->show('user/login', $data);
	}

	public function logout()
	{
		$authentificationManager = new AuthentificationManager;
		$authentificationManager->logUserOut();

		$this->redirectToRoute('home');
	}

	public function account()
	{
		$this->lock();

		$this->show('user/account');
	}

	public function editProfile()
	{
		$this->lock();

		$userManager = new UserManager;
		$authentificationManager = new AuthentificationManager;

		$last_name = "";
		$first_name = "";
		$username = "";
		$email = "";
		$zip_code = "";
		for ($i=75001; $i < 75021; $i++) { 
			$zip[] = $i;
		}
		$address = "";
		$phone_number = "";

		$usernameError = "";
		$emailError = "";
		$zip_codeError = "";
	
		if (!empty($_POST)) {
			
			foreach ($_POST as $k => $v)
			{
				$$k = trim(strip_tags($v));
			}

			// Validation des données

			if (strlen($username) < 4)
			{
				$usernameError = "Pseudo trop court !";
			}

			if (!filter_var($email, FILTER_VALIDATE_EMAIL))
			{
				$emailError = "Email non valide";
			}

			if (!in_array($zip_code, $zip)) {
				$zip_codeError = "Vous devez indiquer un code postal parisien !";
			}

			if (empty($usernameError) && empty($emailError) && empty($zip_codeError)) {
		
				// Si l'utilisateur décide de changer de username
				if ($username != $_SESSION['user']['username']) {
					// S'assurer que le nouveau username n'est pas déjà utilisé
					if ($userManager->usernameExists($username)) {
						$usernameError = "Pseudo déjà utilisé !";
					}
				}
				
				// Si l'utilisateur décide de changer d'email
				if ($email != $_SESSION['user']['email']) {
					// S'assurer que le nouvel email n'est pas déjà utilisé
					if ($userManager->emailExists($email)) {
						$emailError = "Email déjà utilisé !";
					}
				}
				
				$newUser = [
					'last_name' => $last_name,
					'first_name' => $first_name,
					'username' => $username,
					'email' => $email,
					'zip_code' => $zip_code,
					'address' => $address,
					'phone_number' => $phone_number,
					'date_modified' => date('Y-m-d H:i:s')
				];


				
				if ($userManager->update($newUser,$_SESSION['user']['id'])) {
					$refreshUser = $userManager->find($_SESSION['user']['id']);
					$_SESSION['user'] = $refreshUser;
				}
			}
					
		}

		$data = [
			'usernameError' => $usernameError,
			'emailError' => $emailError,
			'zip_codeError' => $zip_codeError,
			];

		$this->show('user/edit_profile', $data);
	}

	public function editPassword()
	{
		$this->lock();

		$authentificationManager = new AuthentificationManager;
		$userManager = new UserManager;

		$old_passwordError = "";
		$passwordError = "";

		if (!empty($_POST)) {
			foreach ($_POST as $k => $v)
			{
				$$k = trim(strip_tags($v));
			}

			// On s'assure que l'ancien mot de passe est valide
			$result = $authentificationManager->isValidLoginInfo($_SESSION['user']['username'], $old_password);

			// Si c'est valide, 
			if ($result > 0){
				// On vérifie que les nouveaux mots de passe sont bien identiques
				if ($password != $confirmPassword) {
				$passwordError = "le mot de passe ne correspond pas !";
				}
				// On hache le nouveau mot de passe
				$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

				if ($userManager->update(['password' => $hashedPassword],$_SESSION['user']['id'])) {
					$refreshUser = $userManager->find($_SESSION['user']['id']);
					$_SESSION['user'] = $refreshUser;
					
				}
				

				}


			else {
					$old_passwordError = "Mauvais mot de passe !";
				}

		}

		$data = [
			'old_passwordError' => $old_passwordError,
			'passwordError' => $passwordError,
			];

		$this->show('user/edit_password', $data);
	}

	public function forgotPassword()
	{
		$userManager = new UserManager;

		$email = "";
		$errorEmail = "";

		if (!empty($_POST)){

			
			$string = new StringUtils;
			$token = $string->randomString();
			$email = trim(strip_tags($_POST['email']));

			if ($userManager->emailExists($email)){

				$user = $userManager->getUserByUsernameOrEmail($email);
				$userManager->update(array("token"=>$token),$user['id']);

				$mail = new \PHPMailer;
					$mail->isSMTP();
					$mail->setLanguage('fr');
					$mail->CharSet = 'UTF-8';
					$mail->SMTPDebug = 2;	//0 pour désactiver les infos de débug
					$mail->Debugoutput = 'html';
					$mail->Host = 'smtp.gmail.com';
					$mail->Port = 587;
					$mail->SMTPSecure = 'tls';
					$mail->SMTPAuth = true;
					$mail->Username = "tony.wf3.nanterre@gmail.com";
					$mail->Password = "nanterre1";
					$mail->setFrom('jeandupont@example.com', 'Service de Messagerie BDloc');
					$mail->addAddress('tony.wf3.nanterre@gmail.com', 'tony SEPEROUMAL');
					$mail->isHTML(true); 	
					$mail->Subject = 'Envoyé par PHP !';					
    				
					$mail->Body = '<a href="www.bdloc.dev/change_password/token='.$token.'/">Cliquer ici pour créer un nouveau mot de passe</a>';

				if (!$mail->send()) {
						echo "Mailer Error: " . $mail->ErrorInfo;
					} else {
						echo "Message sent!";
					}
				$this->redirectToRoute('forgot_password');

			}
			else {

				$errorEmail = "Email non valide !";
			}			
		}


		$data['errorEmail'] = $errorEmail;
		$this->show('user/forgot_Password', $data);
		


	}

	public function changePassword()
	{
		$userManager = new UserManager;
		if (!empty($_GET)){
		$_SESSION['token']= $_GET['token'];		
		$token = $_SESSION['token'];
		
		}
		$user = $userManager->getToken($token);	
		$confirm_password = "";
		$errorConfirm_password = "";		

		if (!empty($_POST)){			
			

			$password = trim(strip_tags($_POST['password']));
			$confirm_password = trim(strip_tags($_POST['confirm_password']));
			

			if ($password != $confirm_password){
				$errorConfirm_password = "Vos mots de passe ne correspondent pas !";
			}
			else if (strlen($password) < 6){
				$errorConfirm_password = "Veuillez saisir un mot de passe d'au moins 7 caracteres !";
			}
			else{
				$containsLetter = preg_match('/[a-zA-Z]/', $password);
				$containsDigit = preg_match('/\d/', $password);
				$containsSpecial = preg_match('/[^a-zA-Z\d]/', $password);

				if (!$containsLetter || !$containsDigit || !$containsSpecial){
					$errorConfirm_password = "Veuillez choisir un mot de passe avec au moins une lettre, un chiffre et un caractere spécial.";
				}
			}

			if ($errorConfirm_password == ""){

				$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
				
				$id = $user['id'];
				$newPassword = [
				"password" => $hashedPassword,
				];	
				
				$userManager->update($newPassword, $id);				
											
			}
		}
		$data['errorConfirm_password'] = $errorConfirm_password;
		$this->show('user/change_password', $data);
	}
}
