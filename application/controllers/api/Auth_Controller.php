<?php

class Auth_Controller extends RestApi_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->library('api_auth');
		$this->load->model('api_model');
	}

	function register()
	{
		$fullname = $this->input->post('fullname');
		$email = $this->input->post('email');
		$phone_no = $this->input->post('phone_no');
		$dob = $this->input->post('dob');
		$password = $this->input->post('password');

		$this->form_validation->set_rules('fullname', 'fullname', 'required');
		$this->form_validation->set_rules('email', 'email', 'required|valid_email');
		$this->form_validation->set_rules('phone_no', 'phone_no', 'required');
		$this->form_validation->set_rules('dob', 'dob', 'required');
		$this->form_validation->set_rules('password', 'password', 'required');
		$this->form_validation->set_rules('confirm_password', 'confirm_password', 'required|matches[password]');

		if ($this->form_validation->run() == FALSE) {
			$responseData = array(
				'status' => true,
				'message' => 'please fix the validation errors.',
				'errors' => $this->form_validation->error_array()
			);
			return $this->response($responseData);
		} else {
			$data = array(
				'fullname' => $fullname,
				'email' => $email,
				'phone_no' => $phone_no,
				'dob' => $dob,
				'password' => password_hash($password, PASSWORD_DEFAULT)
			);

			$this->api_model->registerUser($data);
			$responseData = array(
				'status' => true,
				'message' => 'User registered successfully',
				'data' => $data
			);
			return $this->response($responseData, 200);
		}
	}

	function login()
	{
		$email = $this->input->post('email');
		$password = $this->input->post('password');

		$this->form_validation->set_rules('email', 'email', 'required|valid_email');
		$this->form_validation->set_rules('password', 'password', 'required');

		if ($this->form_validation->run() == FALSE) {
			$responseData = array(
				'status' => true,
				'message' => 'please fix the validation errors.',
				'errors' => $this->form_validation->error_array()
			);
			return $this->response($responseData);
		} else {
			$user = $this->api_model->getUserByEmail($email);
			if ($user && password_verify($password, $user->password)) {
				$loginStatus = $user;
			} else {
				$loginStatus = false;
			}
			if($loginStatus != false){
				$userId = $loginStatus->id;
				$token = bin2hex(random_bytes(32));
				$expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
				$this->api_model->storeToken($userId, $token, $expiry);

				$responseData = array(
					'status' => true,
					'message' => 'Login successful',
					'data' => $loginStatus,
					'token' => $token,
					'token_expiry' => $expiry
				);
				return $this->response($responseData, 200);
			}
			else {
				$responseData = array(
					'status' => false,
					'message' => 'Invalid login credentials'
				);
				return $this->response($responseData, 401);
			}
		}
	}
}
