<?php
class MultipleImages_model extends CI_Model {

	function getAllMultipleImages() {
		$this->load->database();
		$query = $this->db->query("select t.id_title, t.name, i.id_image, i.filename from title t, image i where t.id_title = i.id_title");
		$result = $query->result();
		return $result;
	}

	function addTitle($name) {
		$this->load->database();
		$this->db->set("name", $name);
		$this->db->insert("title");
		if($this->db->affected_rows() > 0) {
			return $id = $this->db->insert_id();
		}
		else {
			return 0;
		}
		
	}

	function addImage($filename, $id_title) {
		$this->load->database();
		$this->db->set("filename", $filename);
		$this->db->set("id_title", $id_title);
		$this->db->insert("image");
		if($this->db->affected_rows() > 0) {
			return $id = $this->db->insert_id();
		}
		else {
			return 0;
		}
	}
	
}
?>