<?php

defined('BASEPATH') or exit('No direct script access allowed');


class MY_Controller extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
	}
}

class RestApi_Controller extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('api_model');
	}

	public function response($response, $code = 401)
	{
		$this->output
			->set_status_header($code)
			->set_content_type('application/json', 'utf-8')
			->set_output(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
			->_display();
		exit;
	}

	protected function validateToken()
	{
		$token = $this->input->get_request_header('Authorization', TRUE);

		if (!$token) {
			$this->response(['status' => false, 'message' => 'Authorization token is required'], 401);
		}

		$token = str_replace('Bearer ', '', $token);

		$this->db->where('token', $token);
		$tokenData = $this->db->get('auth_tokens')->row();

		if (!$tokenData) {
			$this->response(['status' => false, 'message' => 'Invalid token'], 401);
		}

		if (strtotime($tokenData->expiry) < time()) {
			$this->db->where('token', $token);
			$this->db->delete('auth_tokens');

			$this->response(['status' => false, 'message' => 'Token has expired'], 401);
		}

		return $tokenData;
	}
}
