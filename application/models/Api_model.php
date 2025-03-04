<?php

class Api_model extends CI_Model
{
	function registerUser($data)
	{
		$this->db->insert('registration', $data);
	}

	function checkLogin($data)
	{
		$this->db->where($data);
		$query = $this->db->get('registration');
		if ($query->num_rows() == 1) {
			return $query->row();
		} else {
			return false;
		}
	}

	function getProfile($userId)
	{
		$this->db->select('name, email');
		$this->db->where(['id' => $userId]);
		$query = $this->db->get('registration');
		return $query->row();
	}

	public function getUserByEmail($email)
	{
		$this->db->where('email', $email);
		$query = $this->db->get('registration');
		if ($query->num_rows() == 1) {
			return $query->row();
		} else {
			return false;
		}
	}

	function storeToken($userId, $token, $expiry)
	{
		$CI = &get_instance();
		$CI->load->database();
		if ($CI->db->query("SELECT * FROM auth_tokens WHERE user_id='$userId'")->num_rows() > 0) {
			$CI->db->where('user_id', $userId);
			$CI->db->update('auth_tokens', ['expiry' => $expiry, 'user_id' => $userId, 'token' => $token, 'created_at' => date('Y-m-d H:i')]);
		} else {
			$CI->db->insert('auth_tokens', ['expiry' => $expiry, 'user_id' => $userId, 'token' => $token, 'created_at' => date('Y-m-d H:i')]);
		}
	}

	function removeToken($userId, $token)
	{
		$this->api_model->removeToken($userId, $token);
		$responseData = array(
			'status' => true,
			'message' => 'Token removed successfully'
		);
		return $this->response($responseData, 200);
	}
}
