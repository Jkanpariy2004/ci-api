<?php

class Blog_model extends CI_Model
{
    public function __construct()
    {
        $this->load->database();
    }

    public function insert($data)
    {
        return $this->db->insert('blog', $data);
    }

    public function get_by_id($id)
    {
        $query = $this->db->get_where('blog', array('id' => $id));
        return $query->row();
    }

	public function update($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('blog', $data);
    }

    public function delete($id)
    {
        return $this->db->delete('blog', array('id' => $id));
    }
}
