<?php

use \Firebase\JWT\JWT;
// composer require firebase/php-jwt

class Auth_Controller extends RestApi_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->library('api_auth');
		$this->load->model('api_model');
	}

	public function register()
	{
		$fullname = $this->input->post('fullname');
		$email    = $this->input->post('email');
		$phone_no = $this->input->post('phone_no');
		$dob      = $this->input->post('dob');
		$password = $this->input->post('password');

		$this->form_validation->set_rules('fullname', 'fullname', 'required');
		$this->form_validation->set_rules('email', 'email', 'required|valid_email');
		$this->form_validation->set_rules('phone_no', 'phone_no', 'required');
		$this->form_validation->set_rules('dob', 'dob', 'required');
		$this->form_validation->set_rules('password', 'password', 'required');
		$this->form_validation->set_rules('confirm_password', 'confirm_password', 'required|matches[password]');

		if ($this->form_validation->run() == false) {
			$responseData = [
				'status'  => true,
				'message' => 'please fix the validation errors.',
				'errors'  => $this->form_validation->error_array(),
			];
			return $this->response($responseData);
		} else {
			$data = [
				'fullname' => $fullname,
				'email'    => $email,
				'phone_no' => $phone_no,
				'dob'      => $dob,
				'password' => password_hash($password, PASSWORD_DEFAULT),
			];

			$this->api_model->registerUser($data);
			$responseData = [
				'status'  => true,
				'message' => 'User registered successfully',
				'data'    => $data,
			];
			return $this->response($responseData, 200);
		}
	}

	public function login()
	{
		$email    = $this->input->post('email');
		$password = $this->input->post('password');

		$this->form_validation->set_rules('email', 'email', 'required|valid_email');
		$this->form_validation->set_rules('password', 'password', 'required');

		if ($this->form_validation->run() == false) {
			$responseData = [
				'status'  => true,
				'message' => 'Please fix the validation errors.',
				'errors'  => $this->form_validation->error_array(),
			];
			return $this->response($responseData);
		} else {
			$user = $this->api_model->getUserByEmail($email);
			if ($user && password_verify($password, $user->password)) {
				$loginStatus = $user;
			} else {
				$loginStatus = false;
			}
			if ($loginStatus != false) {
				$userId    = $loginStatus->id;
				$tokenData = [
					'id' => $user->id,
					"fullname" => $user->fullname,
					"email" => $user->email,
					"phone_no" => $user->phone_no,
					"dob" => $user->dob,
					"password" => $user->password,
					'exp' => time() + 3600,
				];
				$secretKey = bin2hex(random_bytes(64));

				$token = JWT::encode($tokenData, $secretKey, 'HS256');

				$this->api_model->storeToken($userId, $token, date('Y-m-d H:i:s', strtotime('+1 hour')));

				$responseData = [
					'status'       => true,
					'message'      => 'Login successful',
					'token'        => $token,
					'token_expiry' => date('Y-m-d H:i:s', strtotime('+1 hour')),
					'data'         => $loginStatus,
				];
				return $this->response($responseData, 200);
			} else {
				$responseData = [
					'status'  => false,
					'message' => 'Invalid login credentials',
				];
				return $this->response($responseData, 401);
			}
		}
	}
}
