<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('upload_image')) {
    function upload_image($upload_path = 'uploads/', $allowed_types = 'gif|jpg|png', $max_size = 2048)
    {
        if (!is_dir($upload_path)) {
            if (!mkdir($upload_path, 0777, true)) {
                return ['status' => false, 'message' => 'Failed to create upload directory: ' . $upload_path];
            }
        }

        if (!is_writable($upload_path)) {
            return ['status' => false, 'message' => 'Upload directory is not writable: ' . $upload_path];
        }

        $CI =& get_instance();
        $CI->load->library('upload');

        $config['upload_path'] = $upload_path;
        $config['allowed_types'] = $allowed_types;
        $config['max_size'] = $max_size;

        $CI->upload->initialize($config);

        if (!$CI->upload->do_upload('image')) {
            return ['status' => false, 'message' => $CI->upload->display_errors()];
        } else {
            $upload_data = $CI->upload->data();
            return ['status' => true, 'file_name' => $upload_data['file_name']];
        }
    }
}
