<?php
require_once dirname(__FILE__).'/../../libs/jQuery-File-Upload-9.17.0/server/php/UploadHandler.php';

class Fileupload_UploadHandler_Cadenza extends UploadHandler {
	
	var $vars;
	
	function __construct() {
		if (!Login::isLoggedIn()) {
			trigger_error('File upload not permitted without valid login.', E_USER_ERROR);
		}
		parent::__construct(
			array(
				'upload_dir' => Core::filestoreRootDir().'/',
				'user_dirs' => true,
				'download_via_php' => 1,
				'readfile_chunk_size' => Core::fileuploadReadfileChunkSize(),
				'print_response' => false
			),
			false
		);
	}
	
	public function initiate_upload($lesson_id, $task_id, $practice_id, $category, $uid) {
		$this->vars = array();
		$this->vars['lesson_id'] = $lesson_id;
		$this->vars['task_id'] = $task_id;
		$this->vars['practice_id'] = $practice_id;
		$this->vars['category'] = $category;
		$this->vars['uid'] = $uid;
		$this->initialize();
	}
	public function initiate_delete($lesson_id, $task_id, $practice_id, $category, $uid, $file_id, $filename, $force_delete=false) {
		$this->vars = array();
		$this->vars['lesson_id'] = $lesson_id;
		$this->vars['task_id'] = $task_id;
		$this->vars['practice_id'] = $practice_id;
		$this->vars['category'] = $category;
		$this->vars['uid'] = $uid;
		$this->vars['file_id'] = $file_id;
		$this->vars['filename'] = $filename;
		$this->vars['delete'] = true;
		$this->vars['force_delete'] = $force_delete;
		$this->initialize();
	}
	public function initiate_download($lesson_id, $task_id, $practice_id, $category, $uid, $file_id, $filename, $filetype) {
		$this->vars = array();
		$this->vars['lesson_id'] = $lesson_id;
		$this->vars['task_id'] = $task_id;
		$this->vars['practice_id'] = $practice_id;
		$this->vars['category'] = $category;
		$this->vars['uid'] = $uid;
		$this->vars['file_id'] = $file_id;
		$this->vars['filename'] = $filename;
		$this->vars['filetype'] = $filetype;
		$this->vars['delete'] = false;
		$this->initialize();
	}
	
	protected function initialize() {
		if ($this->get_var('force_delete')) {
			$this->delete($this->options['print_response']);
		}
		else {
			parent::initialize();
		}
    }
	
	public function get_options() {
		return $this->options;
	}
	
	protected function get_var($vars_key) {
		return (isset($this->vars[$vars_key]) ? $this->vars[$vars_key] : null);
	}
	
	protected function get_user_path() {
		if ($this->get_var('file_id') !== null) {
			return $this->get_var('file_id').'/';
		}
		return parent::get_user_path();
    }
	
	protected function handle_file_upload($uploaded_file, $name, $size, $type, $error, $index = null, $content_range = null) {
		$file = parent::handle_file_upload($uploaded_file, $name, $size, $type, $error, $index, $content_range);
		
		// ensure chunk uploads are complete
		$fully_uploaded = true;
		if ($content_range) {
        	$file_size = $this->fix_integer_overflow((int)$content_range[3]);
			if ($file_size != $file->size) {
				$fully_uploaded = false;
			}
		}
		
		if (empty($file->error) && $fully_uploaded) {
			if ($this->get_var('category') == 'attachment') {
				$file->id = UserFileGateway::insertAttachment($this->get_var('uid'), $this->get_var('lesson_id'), $this->get_var('task_id'), $this->get_var('practice_id'), $file->name, $file->type, $file->size);
				
				$current_file_path = $this->get_upload_path($file->name);
				$this->vars['file_id'] = $file->id;
				
				$new_file_dir = $this->get_upload_path();
				$new_file_path = $this->get_upload_path($file->name);
				
	            if (!is_dir($new_file_dir)) {
	                mkdir($new_file_dir, $this->options['mkdir_mode'], true);
	            }
				rename($current_file_path, $new_file_path);
			}
			else {
				trigger_error("Invalid file category '".$this->get_var('category')."'.", E_USER_ERROR);
			}
		}
		return $file;
	}
	
	public function get($print_response = true) {
		if (!$this->get_var('delete') && $this->get_var('file_id') !== null && $this->get_var('filename') !== null) {
			return $this->download();
		}
		return parent::get($print_response);
	}
	
	public function post($print_response = true) {
		if ($this->get_var('delete')) {
			return $this->delete($print_response);
		}
		return parent::post($print_response);
	}
	
	public function delete($print_response = true) {
        $response = array();
		$file_name = $this->get_var('filename');
        $upload_path = $this->get_upload_path();
		UserFileGateway::delete($this->get_var('file_id'));
        $success = is_dir($upload_path) && $file_name[0] !== '.' && $this->rrmdir($upload_path);
        $response[$file_name] = $success;
        return $this->generate_response($response, $print_response);
    }
	
	protected function rrmdir($dir) {
		// Recursive rmdir based on http://php.net/rmdir#98622
		if (is_dir($dir)) {
			$files = scandir($dir);
			foreach ($files as $file) {
				if ($file != '.' && $file != '..') {
					if (filetype($dir.'/'.$file) == 'dir') {
						$this->rrmdir($dir.'/'.$file);
					}
					else {
						unlink($dir.'/'.$file);
					}
				}
			}
			reset($files);
			rmdir($dir);
			return true;
		}
		return false;
	}
	
	protected function download() {
        switch ($this->options['download_via_php']) {
            case 1:
                $redirect_header = null;
                break;
            case 2:
                $redirect_header = 'X-Sendfile';
                break;
            case 3:
                $redirect_header = 'X-Accel-Redirect';
                break;
            default:
                return $this->header('HTTP/1.1 403 Forbidden');
        }
        $file_name = $this->get_var('filename');
        if (!$this->is_valid_file_object($file_name)) {
            return $this->header('HTTP/1.1 404 Not Found');
        }
        if ($redirect_header) {
            return $this->header(
                $redirect_header.': '.$this->get_download_url(
                    $file_name,
                    $this->get_version_param(),
                    true
                )
            );
        }
        $file_path = $this->get_upload_path($file_name, $this->get_version_param());
        // Prevent browsers from MIME-sniffing the content-type:
        $this->header('X-Content-Type-Options: nosniff');
		
		// if is image, display inline
        if (preg_match($this->options['inline_file_types'], $file_name)) {
        	$this->header('Content-Type: '.$this->get_var('filetype'));
            $this->header('Content-Disposition: inline; filename="'.$file_name.'"');
        }
		// else if is audio or video, set attachment with proper content type
        else if (preg_match('/^(video|audio)\//i', $this->get_var('filetype'))) {
        	$this->header('Content-Type: '.$this->get_var('filetype'));
            $this->header('Content-Disposition: attachment; filename="'.$file_name.'"');
        }
		// else, set attachment with as byte stream
        else {
            $this->header('Content-Type: application/octet-stream');
            $this->header('Content-Disposition: attachment; filename="'.$file_name.'"');
        }
        $this->header('Content-Length: '.$this->get_file_size($file_path));
        $this->header('Last-Modified: '.gmdate('D, d M Y H:i:s T', filemtime($file_path)));
        $this->readfile($file_path);
    }

	// need to support HTTP_RANGE for iPads
	// reference: http://stackoverflow.com/questions/31887262/html-for-playing-mov-files-in-safari-8-what-changed
    protected function readfile($file_path) {
        $handle = fopen($file_path, 'rb');
		
        $file_size = $this->get_file_size($file_path);
		
		$length_to_send = $file_size;
		$start_byte = 0;
		$end_byte = $file_size - 1;
		
  		$this->header("Accept-Ranges: 0-".$file_size);
  		if (isset($_SERVER['HTTP_RANGE'])) {
  			$chunk_start_byte = $start_byte;
      		$chunk_end_byte   = $end_byte;
      		
      		list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
      		if (strpos($range, ',') !== false) {
      			$this->header('HTTP/1.1 416 Requested Range Not Satisfiable');
      			$this->header("Content-Range: bytes ".$start_byte."-".$end_byte."/".$file_size);
      			exit;
			}
			if ($range == '-') {
				$chunk_start_byte = $file_size - substr($range, 1);
			}
			else {
				$range  = explode('-', $range);
				$chunk_start_byte = $range[0];
				$chunk_end_byte = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $file_size;
			}
			$chunk_end_byte = ($chunk_end_byte > $end_byte) ? $end_byte : $chunk_end_byte;
			
			if ($chunk_start_byte > $chunk_end_byte || $chunk_start_byte > $file_size - 1 || $chunk_end_byte >= $file_size) {
				$this->header('HTTP/1.1 416 Requested Range Not Satisfiable');
      			$this->header("Content-Range: bytes ".$start_byte."-".$end_byte."/".$file_size);
				exit;
			}
			$start_byte = $chunk_start_byte;
			$end_byte = $chunk_end_byte;
			$length_to_send = $end_byte - $start_byte + 1;

			fseek($handle, $start_byte);
			$this->header('HTTP/1.1 206 Partial Content');
		}
		
		$this->header("Content-Range: bytes $start_byte-$end_byte/$file_size");
		$this->header("Content-Length: ".$length_to_send);
		
        $buffer_size = $this->options['readfile_chunk_size'] ? $this->options['readfile_chunk_size'] : 1024 * 8;
        
        while (!feof($handle) && ($p = ftell($handle)) <= $end_byte) {
        	if ($p + $buffer_size > $end_byte) {
        		$buffer_size = $end_byte - $p + 1;
			}
			set_time_limit(0);
			echo fread($handle, $buffer_size);
			flush();
		}
		
		fclose($handle);
    }

	// override is_valid_image_file to prevent image processing
	// can easily cause memory errors in PHP when to many images are uploaded at the same time
    protected function is_valid_image_file($file_path) {
    	return false;
    }
}
