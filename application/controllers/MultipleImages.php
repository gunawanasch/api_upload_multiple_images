<?php
class MultipleImages extends CI_Controller {

	function __construct() { 
			parent::__construct();
			date_default_timezone_set("Asia/Jakarta");
			$this->load->helper(array("form", "url"));
	}

	function getAllMultipleImages() {
		//http://localhost/api_upload_multiple_images/MultipleImages/getAllMultipleImages
		$this->load->model("MultipleImages_model");
		$data = $this->MultipleImages_model->getAllMultipleImages();
		$temp_id_title = 0;
		$temp_name;
		$loop = 0;
		$loop_title = 0;
		$loop_image = 0;
		$title_array = array();
		$image_array = array();
		foreach ($data as $row) {
			if (sizeof($data) == 1) {
				$image_array[$loop_image] = array('id_image'	=> $row->id_image, 
											  	  'filename'	=> $row->filename);

				$title_array[$loop_title] = array('id_title' 	=> $row->id_title, 
											  	  'name' 		=> $row->name,
											  	  'list_image' 	=> $image_array);
			} else {
				if ($loop < sizeof($data)-1) {
					if ($loop == 0) {
						$temp_id_title = $row->id_title;
						$temp_name = $row->name;
						$image_array[$loop_image] = array('id_image'	=> $row->id_image, 
														  'filename'	=> $row->filename);
						$loop_image++;
					} else {
						if ($temp_id_title == $row->id_title) {
							$image_array[$loop_image] = array('id_image'	=> $row->id_image, 
														  	  'filename'	=> $row->filename);
							$loop_image++;
						} else {
							$title_array[$loop_title] = array('id_title' 	=> $temp_id_title, 
														  	  'name' 		=> $temp_name,
														  	  'list_image' 	=> $image_array);

							$temp_id_title = $row->id_title;
							$temp_name = $row->name;
							$image_array = array();
							$loop_image = 0;
							$image_array[$loop_image] = array('id_image'	=> $row->id_image, 
														  	  'filename'	=> $row->filename);
							$loop_image++;
							$loop_title++;
						}
					}	
				} else {
					if ($temp_id_title == $row->id_title) {
						$image_array[$loop_image] = array('id_image'	=> $row->id_image, 
													  	  'filename'	=> $row->filename);

						$title_array[$loop_title] = array('id_title' 	=> $temp_id_title, 
													  	  'name' 		=> $temp_name,
													  	  'list_image' 	=> $image_array);
					} else {
						$title_array[$loop_title] = array('id_title' 	=> $temp_id_title, 
														  'name' 		=> $temp_name,
														  'list_image' 	=> $image_array);

						$loop_title++;
						$temp_id_title = $row->id_title;
						$temp_name = $row->name;
						$image_array = array();
						$loop_image = 0;
						$image_array[$loop_image] = array('id_image'	=> $row->id_image, 
													  	  'filename'	=> $row->filename);

						$title_array[$loop_title] = array('id_title' 	=> $temp_id_title, 
													  	  'name' 		=> $temp_name,
													  	  'list_image' 	=> $image_array);
						
					}
				}

			}
			$loop++;
		}
		echo json_encode($title_array);
	}
	
	function getExtension($str) {
		$i = strrpos($str,".");
		if (!$i) { return ""; } 
		$l = strlen($str) - $i;
		$ext = substr($str,$i+1,$l);
		return $ext;
	}
	
	function compressImage($ext, $target_file, $path, $actual_image_name, $new_width) {
		if($ext == "jpg" || $ext == "jpeg" ){
			$src = imagecreatefromjpeg($target_file);
		}
		else if($ext == "png"){
			$src = imagecreatefrompng($target_file);
		}
		else if($ext == "gif"){
			$src = imagecreatefromgif($target_file);
		}
		else{
			$src = imagecreatefrombmp($target_file);
		}
																		
		list($width, $height) = getimagesize($target_file);
		$new_height = ($height/$width)*$new_width;
		$tmp = imagecreatetruecolor($new_width,$new_height);
		imagecopyresampled($tmp, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
		$filename = $path.$actual_image_name;
		imagejpeg($tmp, $filename, 100);
		imagedestroy($tmp);
		return $filename;
	}

	function addMultipleImages() {
		//http://localhost/api_upload_multiple_images/MultiImages/addMultipleImages
		$name = $this->input->post("name");
		$this->load->model("MultipleImages_model");
		$id_title = $this->MultipleImages_model->addTitle($name);
		if($id_title > 0) {
			$path = "../api_upload_multiple_images/assets/images/";
			if (!empty($_FILES['image']['name'])) {
				$image_count = count($_FILES['image']['name']);
				for($i=0; $i<$image_count; $i++) {
					$filename = $_FILES['image']['name'][$i];
					$mod_filename = date("YmdHis")."_".$i.".".strtolower($this->getExtension($filename));
					$target_file = $path.$mod_filename;
					$valid_formats = array("jpg", "png", "gif", "bmp","jpeg","PNG","JPG","JPEG","GIF","BMP");
					$maxsize = 3000*2000;
					list($txt, $extension) = explode(".", $filename);
					$extension = strtolower($this->getExtension($filename));
					if(in_array($extension,$valid_formats)) {
						move_uploaded_file($_FILES['image']['tmp_name'][$i], $target_file);
						$width_array = 800;
						$this->compressImage($extension, $target_file, $path, $mod_filename, $width_array);
						$this->load->model("MultipleImages_model");
						$data = $this->MultipleImages_model->addImage($mod_filename, $id_title);
					}
				}
				$array = array("status" => 1, "message" => "Succeeded");
				echo json_encode($array);
			} else {
				$array = array("status" => 0, "message" => "Image empty");
				echo json_encode($array);
			}

		} else {
			$result = array("status" => 0, "message" => "Insert data failed");
		}
	}

}
?>