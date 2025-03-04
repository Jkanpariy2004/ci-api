<?php

class Crud_controller extends RestApi_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('api_model');
		$this->load->library('form_validation');
		$this->load->library('upload');
		$this->load->model('Blog_model');
		$this->load->helper('image_upload');
	}

	public function insert_blog()
	{
		$title = $this->input->post('title');
		$content = $this->input->post('content');

		$this->form_validation->set_rules('title', 'title', 'required');
		$this->form_validation->set_rules('content', 'content', 'required');

		if ($this->form_validation->run() == FALSE) {
			$responseData = array(
				'status' => false,
				'message' => 'Please fix the validation errors.',
				'errors' => $this->form_validation->error_array()
			);
			return $this->response($responseData);
		} else {
			$upload_result = upload_image();

			if ($upload_result['status'] === false) {
				$responseData = array(
					'status' => false,
					'message' => $upload_result['message']
				);
				return $this->response($responseData);
			}

			$image_name = $upload_result['file_name'];
			$image_url = base_url('uploads/' . $image_name);

			$data = array(
				'title' => $title,
				'content' => $content,
				'image' => $image_name,
			);

			$this->Blog_model->insert($data);

			$responseData = array(
				'status' => true,
				'message' => 'Blog post inserted successfully',
				'data' => array_merge($data, ['image_url' => $image_url])
			);

			return $this->response($responseData, 200);
		}
	}

	public function update_blog($id)
	{
		if (empty($id)) {
			$responseData = array(
				'status' => false,
				'message' => 'Invalid blog ID'
			);
			return $this->response($responseData);
		}

		$title = $this->input->post('title');
		$content = $this->input->post('content');

		$this->form_validation->set_rules('title', 'title', 'required');
		$this->form_validation->set_rules('content', 'content', 'required');

		if ($this->form_validation->run() == FALSE) {
			$responseData = array(
				'status' => false,
				'message' => 'Please fix the validation errors.',
				'errors' => $this->form_validation->error_array()
			);
			return $this->response($responseData);
		} else {
			$blog = $this->Blog_model->get_by_id($id);

			if (!$blog) {
				$responseData = array(
					'status' => false,
					'message' => 'Blog post not found'
				);
				return $this->response($responseData);
			}

			$image_name = $blog->image;
			$image_url = base_url('uploads/' . $image_name);

			if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
				$upload_result = upload_image();

				if ($upload_result['status'] === false) {
					$responseData = array(
						'status' => false,
						'message' => $upload_result['message']
					);
					return $this->response($responseData);
				}

				if (file_exists('uploads/' . basename($blog->image))) {
					unlink('uploads/' . basename($blog->image));
				}

				$image_name = $upload_result['file_name'];
				$image_url = base_url('uploads/' . $image_name);
			}

			$data = array(
				'title' => $title,
				'content' => $content,
				'image' => $image_name
			);

			$this->Blog_model->update($id, $data);

			$responseData = array(
				'status' => true,
				'message' => 'Blog post updated successfully',
				'data' => array_merge($data, ['image_url' => $image_url])
			);

			return $this->response($responseData, 200);
		}
	}

	public function delete_blog($id)
	{
		if (empty($id)) {
			$responseData = array(
				'status' => false,
				'message' => 'Invalid blog ID'
			);
			return $this->response($responseData);
		}

		$blog = $this->Blog_model->get_by_id($id);

		if (!$blog) {
			$responseData = array(
				'status' => false,
				'message' => 'Blog post not found'
			);
			return $this->response($responseData);
		}

		if (file_exists('uploads/' . basename($blog->image))) {
			unlink('uploads/' . basename($blog->image));
		}

		$this->Blog_model->delete($id);

		$responseData = array(
			'status' => true,
			'message' => 'Blog post deleted successfully'
		);

		return $this->response($responseData, 200);
	}
}
