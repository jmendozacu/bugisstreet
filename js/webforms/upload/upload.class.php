<?php
/*
 * jQuery File Upload Plugin PHP Class 5.7
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

class UploadHandler
{
	protected $options;
	
	function __construct($options=null) {
		
		// clear cache
		$cache_time = 30; //minutes
		$dir = dirname($_SERVER['SCRIPT_FILENAME']).'/files';
		$dir_contents = scandir($dir);
		foreach($dir_contents as $entry){
			if(is_dir($dir.'/'.$entry) && $entry != "." && $entry != ".."){
				$time = filemtime($dir.'/'.$entry);
				if((@strtotime('now') - $time) > 60 * $cache_time){
					rrmdir($dir.'/'.$entry);
				}
			}
		}
		
		// clear thumbnails
		$cache_time = 30; //minutes
		$dir = dirname($_SERVER['SCRIPT_FILENAME']).'/thumbnails';
		$dir_contents = scandir($dir);
		foreach($dir_contents as $entry){
			if(is_dir($dir.'/'.$entry) && $entry != "." && $entry != ".."){
				$time = filemtime($dir.'/'.$entry);
				if((@strtotime('now') - $time) > 60 * $cache_time){
					rrmdir($dir.'/'.$entry);
				}
			}
		}
		
		empty($_REQUEST['param_name']) ? $param_name = 'files' : $param_name = $_REQUEST['param_name'];
		$rand = randomAlphaNum(5);
		while(is_dir(dirname($_SERVER['SCRIPT_FILENAME']).'/files/'.$rand.'/')){
			$rand = randomAlphaNum(5);
		}
		mkdir(dirname($_SERVER['SCRIPT_FILENAME']).'/files/'.$rand.'/',0777);
		
		$this->options = array(
			'rand' => $rand,
			'script_url' => $this->getFullUrl().'/',
			'upload_dir' => dirname($_SERVER['SCRIPT_FILENAME']).'/files/'.$rand.'/',
			'upload_url' => $this->getFullUrl().'/files/'.$rand.'/',
			//'param_name' => 'files',
			'param_name' => $param_name,
			// Set the following option to 'POST', if your server does not support
			// DELETE requests. This is a parameter sent to the client:
			'delete_type' => 'DELETE',
			// The php.ini settings upload_max_filesize and post_max_size
			// take precedence over the following max_file_size setting:
			'max_file_size' => null,
			'min_file_size' => 1,
			'accept_file_types' => '/.+$/i',
			'max_number_of_files' => null,
			// Set the following option to false to enable non-multipart uploads:
			'discard_aborted_uploads' => true,
			// Set to true to rotate images based on EXIF meta data, if available:
			'orient_image' => false,
			'image_versions' => array(
				// Uncomment the following version to restrict the size of
				// uploaded images. You can also add additional versions with
				// their own upload directories:
				/*
				'large' => array(
					'upload_dir' => dirname($_SERVER['SCRIPT_FILENAME']).'/files/',
					'upload_url' => $this->getFullUrl().'/files/',
					'max_width' => 1920,
					'max_height' => 1200
				),
				*/
				'thumbnail' => array(
					'upload_dir' => dirname($_SERVER['SCRIPT_FILENAME']).'/thumbnails/',
					'upload_url' => $this->getFullUrl().'/thumbnails/'.$rand.'/',
					'max_width' => 80,
					'max_height' => 80
				)
			)
		);
		if ($options) {
			$this->options = array_replace_recursive($this->options, $options);
		}
	}

	protected function getFullUrl() {
		return
				(isset($_SERVER['HTTPS']) ? 'https://' : 'http://').
				(isset($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'].'@' : '').
				(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ($_SERVER['SERVER_NAME'].
				(isset($_SERVER['HTTPS']) && $_SERVER['SERVER_PORT'] === 443 ||
				$_SERVER['SERVER_PORT'] === 80 ? '' : ':'.$_SERVER['SERVER_PORT']))).
				substr($_SERVER['SCRIPT_NAME'],0, strrpos($_SERVER['SCRIPT_NAME'], '/'));
	}
	
	protected function set_file_delete_url($file) {
		$file->delete_url = $this->options['script_url']
			.'?file='.rawurlencode($file->name);
		$file->delete_type = $this->options['delete_type'];
		if ($file->delete_type !== 'DELETE') {
			$file->delete_url .= '&_method=DELETE';
		}
	}
	
	protected function get_file_object($file_name) {
		$file_path = $this->options['upload_dir'].$file_name;
		if (is_file($file_path) && $file_name[0] !== '.') {
			$file = new stdClass();
			$file->name = $file_name;
			$file->size = filesize($file_path);
			$file->url = $this->options['upload_url'].rawurlencode($file->name);
			foreach($this->options['image_versions'] as $version => $options) {
				if (is_file($options['upload_dir'].$file_name)) {
					$file->{$version.'_url'} = $options['upload_url']
						.rawurlencode($file->name);
				}
			}
			$this->set_file_delete_url($file);
			return $file;
		}
		return null;
	}
	
	protected function get_file_objects() {
		return array_values(array_filter(array_map(
			array($this, 'get_file_object'),
			scandir($this->options['upload_dir'])
		)));
	}

	protected function create_scaled_image($file_name, $options) {
		$file_path = $this->options['upload_dir'].$file_name;
		$new_file_path = $options['upload_dir'].'/'.$this->options['rand'].'/'.$file_name;
		@mkdir(dirname($_SERVER['SCRIPT_FILENAME']).'/thumbnails/'.$this->options['rand'].'/',0777);
		list($img_width, $img_height) = @getimagesize($file_path);
		if (!$img_width || !$img_height) {
			return false;
		}
		$scale = min(
			$options['max_width'] / $img_width,
			$options['max_height'] / $img_height
		);
		if ($scale > 1) {
			$scale = 1;
		}
		$new_width = $img_width * $scale;
		$new_height = $img_height * $scale;
		$new_img = @imagecreatetruecolor($new_width, $new_height);
		switch (strtolower(substr(strrchr($file_name, '.'), 1))) {
			case 'jpg':
			case 'jpeg':
				$src_img = @imagecreatefromjpeg($file_path);
				$write_image = 'imagejpeg';
				break;
			case 'gif':
				@imagecolortransparent($new_img, @imagecolorallocate($new_img, 0, 0, 0));
				$src_img = @imagecreatefromgif($file_path);
				$write_image = 'imagegif';
				break;
			case 'png':
				@imagecolortransparent($new_img, @imagecolorallocate($new_img, 0, 0, 0));
				@imagealphablending($new_img, false);
				@imagesavealpha($new_img, true);
				$src_img = @imagecreatefrompng($file_path);
				$write_image = 'imagepng';
				break;
			default:
				$src_img = $image_method = null;
		}
		$success = $src_img && @imagecopyresampled(
			$new_img,
			$src_img,
			0, 0, 0, 0,
			$new_width,
			$new_height,
			$img_width,
			$img_height
		) && $write_image($new_img, $new_file_path);
		// Free up memory (imagedestroy does not delete files):
		@imagedestroy($src_img);
		@imagedestroy($new_img);
		return $success;
	}
	
	protected function has_error($uploaded_file, $file, $error) {
		if ($error) {
			return $error;
		}
		if (!preg_match($this->options['accept_file_types'], $file->name)) {
			return 'acceptFileTypes';
		}
		if ($uploaded_file && is_uploaded_file($uploaded_file)) {
			$file_size = filesize($uploaded_file);
		} else {
			$file_size = $_SERVER['CONTENT_LENGTH'];
		}
		if ($this->options['max_file_size'] && (
				$file_size > $this->options['max_file_size'] ||
				$file->size > $this->options['max_file_size'])
			) {
			return 'maxFileSize';
		}
		if ($this->options['min_file_size'] &&
			$file_size < $this->options['min_file_size']) {
			return 'minFileSize';
		}
		if (is_int($this->options['max_number_of_files']) && (
				count($this->get_file_objects()) >= $this->options['max_number_of_files'])
			) {
			return 'maxNumberOfFiles';
		}
		return $error;
	}
	
	protected function trim_file_name($name, $type) {
		// Remove path information and dots around the filename, to prevent uploading
		// into different directories or replacing hidden system files.
		// Also remove control characters and spaces (\x00..\x20) around the filename:
		$file_name = trim(basename(stripslashes($name)), ".\x00..\x20");
		// Add missing file extension for known image types:
		if (strpos($file_name, '.') === false &&
			preg_match('/^image\/(gif|jpe?g|png)/', $type, $matches)) {
			$file_name .= '.'.$matches[1];
		}
		return $file_name;
	}

	protected function orient_image($file_path) {
		$exif = exif_read_data($file_path);
		$orientation = intval(@$exif['Orientation']);
		if (!in_array($orientation, array(3, 6, 8))) { 
			return false;
		}
		$image = @imagecreatefromjpeg($file_path);
		switch ($orientation) {
			  case 3:
				$image = @imagerotate($image, 180, 0);
				break;
			  case 6:
				$image = @imagerotate($image, 270, 0);
				break;
			  case 8:
				$image = @imagerotate($image, 90, 0);
				break;
			default:
				return false;
		}
		$success = imagejpeg($image, $file_path);
		// Free up memory (imagedestroy does not delete files):
		@imagedestroy($image);
		return $success;
	}
	
	protected function handle_file_upload($uploaded_file, $name, $size, $type, $error) {
		$file = new stdClass();
		$file->name = $this->trim_file_name($name, $type);
		$file->size = intval($size);
		$file->type = $type;
		$error = $this->has_error($uploaded_file, $file, $error);
		
		//var_dump($uploaded_file,$name,$size,$type,$error);
		
		if (!$error && $file->name) {
			$file_path = $this->options['upload_dir'].$file->name;
			$file->rand = $this->options['rand'];
			$append_file = !$this->options['discard_aborted_uploads'] &&
				is_file($file_path) && $file->size > filesize($file_path);
			clearstatcache();
			if ($uploaded_file && is_uploaded_file($uploaded_file)) {
				// multipart/formdata uploads (POST method uploads)
				if ($append_file) {
					file_put_contents(
						$file_path,
						fopen($uploaded_file, 'r'),
						FILE_APPEND
					);
				} else {
					move_uploaded_file($uploaded_file, $file_path);
				}
			} else {
				// Non-multipart uploads (PUT method support)
				file_put_contents(
					$file_path,
					fopen('php://input', 'r'),
					$append_file ? FILE_APPEND : 0
				);
			}
			$file_size = filesize($file_path);
			if ($file_size === $file->size) {
					if ($this->options['orient_image']) {
						$this->orient_image($file_path);
					}
				$file->url = $this->options['upload_url'].rawurlencode($file->name);
				foreach($this->options['image_versions'] as $version => $options) {
					if ($this->create_scaled_image($file->name, $options)) {
						$file->{$version.'_url'} = $options['upload_url']
							.rawurlencode($file->name);
					}
				}
			} else if ($this->options['discard_aborted_uploads']) {
				unlink($file_path);
				$file->error = 'abort';
			}
			$file->size = $file_size;
			$this->set_file_delete_url($file);
		} else {
			$file->error = $error;
		}
		return $file;
	}
	
	public function get() {
		$file_name = isset($_REQUEST['file']) ?
			basename(stripslashes($_REQUEST['file'])) : null;
		if ($file_name) {
			$info = $this->get_file_object($file_name);
		} else {
			$info = $this->get_file_objects();
		}
		header('Content-type: application/json');
		echo json_encode($info);
	}
	
	public function post() {
		if (isset($_REQUEST['_method']) && $_REQUEST['_method'] === 'DELETE') {
			return $this->delete();
		}
		$upload = isset($_FILES[$this->options['param_name']]) ?
			$_FILES[$this->options['param_name']] : null;
			
		$info = array();

		if ($upload && is_array($upload['tmp_name'])) {
			foreach ($upload['tmp_name'] as $index => $value) {
				$info[] = $this->handle_file_upload(
					$upload['tmp_name'][$index],
					isset($_SERVER['HTTP_X_FILE_NAME']) ?
						$_SERVER['HTTP_X_FILE_NAME'] : $upload['name'][$index],
					isset($_SERVER['HTTP_X_FILE_SIZE']) ?
						$_SERVER['HTTP_X_FILE_SIZE'] : $upload['size'][$index],
					isset($_SERVER['HTTP_X_FILE_TYPE']) ?
						$_SERVER['HTTP_X_FILE_TYPE'] : $upload['type'][$index],
					$upload['error'][$index]
				);
			}
		} elseif ($upload || isset($_SERVER['HTTP_X_FILE_NAME'])) {
			$info[] = $this->handle_file_upload(
				isset($upload['tmp_name']) ? $upload['tmp_name'] : null,
				isset($_SERVER['HTTP_X_FILE_NAME']) ?
					$_SERVER['HTTP_X_FILE_NAME'] : (isset($upload['name']) ?
						$upload['name'] : null),
				isset($_SERVER['HTTP_X_FILE_SIZE']) ?
					$_SERVER['HTTP_X_FILE_SIZE'] : (isset($upload['size']) ?
						$upload['size'] : null),
				isset($_SERVER['HTTP_X_FILE_TYPE']) ?
					$_SERVER['HTTP_X_FILE_TYPE'] : (isset($upload['type']) ?
						$upload['type'] : null),
				isset($upload['error']) ? $upload['error'] : null
			);
		}
		header('Vary: Accept');
		$json = json_encode($info);
		$redirect = isset($_REQUEST['redirect']) ?
			stripslashes($_REQUEST['redirect']) : null;
		if ($redirect) {
			header('Location: '.sprintf($redirect, rawurlencode($json)));
			return;
		}
		if (isset($_SERVER['HTTP_ACCEPT']) &&
			(strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
			header('Content-type: application/json');
		} else {
			header('Content-type: text/plain');
		}
		echo $json;
	}
	
	public function delete() {
		$file_name = isset($_REQUEST['file']) ?
			basename(stripslashes($_REQUEST['file'])) : null;
		$file_path = $this->options['upload_dir'].$file_name;
		$success = is_file($file_path) && $file_name[0] !== '.' && unlink($file_path);
		if ($success) {
			foreach($this->options['image_versions'] as $version => $options) {
				$file = $options['upload_dir'].$file_name;
				if (is_file($file)) {
					unlink($file);
				}
			}
		}
		header('Content-type: application/json');
		echo json_encode($success);
	}

}

function rrmdir($dir) { 
	if (is_dir($dir)) { 
		$objects = scandir($dir); 
		foreach ($objects as $object) { 
			if ($object != "." && $object != "..") { 
				if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object); 
		} 
	} 
	reset($objects); 
	@rmdir($dir); 
	} 
} 
   
function randomAlphaNum($length){ 

	$rangeMin = pow(36, $length-1); //smallest number to give length digits in base 36 
	$rangeMax = pow(36, $length)-1; //largest number to give length digits in base 36 
	$base10Rand = mt_rand($rangeMin, $rangeMax); //get the random number 
	$newRand = base_convert($base10Rand, 10, 36); //convert it 
	
	return $newRand; //spit it out 
} 