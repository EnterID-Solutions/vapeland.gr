<?php
class ModelExtensionModuleOldimage extends Model {
	
	public function install() {
		$this->db->query('DROP VIEW IF EXISTS `' . DB_PREFIX . 'oldimage`');
		$sql = 'CREATE VIEW `' . DB_PREFIX . 'oldimage` AS ';
		
		$tables_with_images = array(
		'banner_image' => 'image', 'category' => 'image', 'download' => 'filename', 'location' => 'image', 'manufacturer' => 'image', 'option_value' => 'image', 
		'product' => 'image', 'product_image' => 'image', 'setting' => 'value', 'user' => 'image', 'voucher_theme' => 'image', 
		'journal3_blog_category' => 'image', 'journal3_blog_post' => 'image', 
		);
		
		$tables = array();
			
		foreach ($tables_with_images as $name => $column) {
			$tables[] = 'SELECT DISTINCT `' . $column . '` image FROM `' . DB_PREFIX . $name . '` WHERE `' . $column . "` LIKE 'catalog/%'";
		}
		
		if (file_exists(DIR_IMAGE . 'data/')) {
			foreach ($tables_with_images as $name => $column) {
				$tables[] = 'SELECT DISTINCT `' . $column . '` image FROM `' . DB_PREFIX . $name . '` WHERE `' . $column . "` LIKE 'data/%'";
			}
		}
		
		$sql .= implode(' UNION ', $tables);

		$this->db->query($sql);
	}
	
	public function uninstall() {
		$this->db->query('DROP VIEW IF EXISTS `' . DB_PREFIX . 'oldimage`');
	}
	
	public function getActiveImages() {		
	
		$match = '';
		$extra_images = array();
		$images = array();
		
		$query = $this->db->query('SELECT * FROM `' . DB_PREFIX . 'oldimage`');

		foreach ($query->rows as $row) {
			$images[] = $row['image'];
		}
		
		$extras = $this->db->query("
			(SELECT description FROM `" . DB_PREFIX . "product_description` WHERE description LIKE '%catalog/%' OR description LIKE '%data/%') UNION 
			(SELECT description FROM `" . DB_PREFIX . "category_description` WHERE description LIKE '%catalog/%' OR description LIKE '%data/%') UNION 
			(SELECT description FROM `" . DB_PREFIX . "information_description` WHERE description LIKE '%catalog/%' OR description LIKE '%data/%') UNION 
			(SELECT description FROM `" . DB_PREFIX . "journal3_blog_category_description` WHERE description LIKE '%catalog/%' OR description LIKE '%data/%') UNION 
			(SELECT description FROM `" . DB_PREFIX . "journal3_blog_post_description` WHERE description LIKE '%catalog/%' OR description LIKE '%data/%') UNION 
			(SELECT description FROM `" . DB_PREFIX . "marketing` WHERE description LIKE '%catalog/%' OR description LIKE '%data/%') 
		");		
		
		if ($extras) {
			foreach ($extras->rows as $row) {
				if (preg_match_all('/catalog\/(.*?)[-]*\.(?:jpe?g|a?png|gif|bmp|tif?f|ico|svg|webp|webm|pdf|avi|flv|wmv|mov|wav|aac|qt|mpe?g|mp3|mp4)/i', stripslashes($row['description']), $match)) {
					
					$extra_images[] = $match[0];
				}
				
				if (preg_match_all('/data\/(.*?)[-]*\.(?:jpe?g|a?png|gif|bmp|tif?f|ico|svg|webp|webm|pdf|avi|flv|wmv|mov|wav|aac|qt|mpe?g|mp3|mp4)/i', stripslashes($row['description']), $match)) {
					$extra_images[] = $match[0];
				}
			}
		}

		$extras2 = $this->db->query("SELECT layout_data FROM `" . DB_PREFIX . "journal3_layout`");
		if ($extras2) {
			foreach ($extras2->rows as $row) {

				if (preg_match_all('/data\/(.*?)[-]*\.(?:jpe?g|a?png|gif|bmp|tif?f|ico|svg|webp|webm|pdf|avi|flv|wmv|mov|wav|aac|qt|mpe?g|mp3|mp4)/i', stripslashes($row['layout_data']), $match)) {
					$extra_images[] = $match[0];
				}
				
				if (preg_match_all('/catalog\/(.*?)[-]*\.(?:jpe?g|a?png|gif|bmp|tif?f|ico|svg|webp|webm|pdf|avi|flv|wmv|mov|wav|aac|qt|mpe?g|mp3|mp4)/i', stripslashes($row['layout_data']), $match)) {
					$extra_images[] = $match[0];
				}
			}
		}
				
		$extras2 = $this->db->query("SELECT module_data FROM `" . DB_PREFIX . "journal3_module`");
		if ($extras2) {
			foreach ($extras2->rows as $row) {

				if (preg_match_all('/data\/(.*?)[-]*\.(?:jpe?g|a?png|gif|bmp|tif?f|ico|svg|webp|webm|pdf|avi|flv|wmv|mov|wav|aac|qt|mpe?g|mp3|mp4)/i', stripslashes($row['module_data']), $match)) {
					$extra_images[] = $match[0];
				}
				
				if (preg_match_all('/catalog\/(.*?)[-]*\.(?:jpe?g|a?png|gif|bmp|tif?f|ico|svg|webp|webm|pdf|avi|flv|wmv|mov|wav|aac|qt|mpe?g|mp3|mp4)/i', stripslashes($row['module_data']), $match)) {
					$extra_images[] = $match[0];
				}
			}
		}
		
		$extras2 = $this->db->query("SELECT setting_value FROM `" . DB_PREFIX . "journal3_setting`");
		if ($extras2) {
			foreach ($extras2->rows as $row) {

				if (preg_match_all('/data\/(.*?)[-]*\.(?:jpe?g|a?png|gif|bmp|tif?f|ico|svg|webp|webm|pdf|avi|flv|wmv|mov|wav|aac|qt|mpe?g|mp3|mp4)/i', stripslashes($row['setting_value']), $match)) {
					$extra_images[] = $match[0];
				}
				
				if (preg_match_all('/catalog\/(.*?)[-]*\.(?:jpe?g|a?png|gif|bmp|tif?f|ico|svg|webp|webm|pdf|avi|flv|wmv|mov|wav|aac|qt|mpe?g|mp3|mp4)/i', stripslashes($row['setting_value']), $match)) {
					$extra_images[] = $match[0];
				}
			}
		}
		
		$extras2 = $this->db->query("SELECT setting_value FROM `" . DB_PREFIX . "journal3_skin_setting`");
		if ($extras2) {
			foreach ($extras2->rows as $row) {

				if (preg_match_all('/data\/(.*?)[-]*\.(?:jpe?g|a?png|gif|bmp|tif?f|ico|svg|webp|webm|pdf|avi|flv|wmv|mov|wav|aac|qt|mpe?g|mp3|mp4)/i', stripslashes($row['setting_value']), $match)) {
					$extra_images[] = $match[0];
				}
				
				if (preg_match_all('/catalog\/(.*?)[-]*\.(?:jpe?g|a?png|gif|bmp|tif?f|ico|svg|webp|webm|pdf|avi|flv|wmv|mov|wav|aac|qt|mpe?g|mp3|mp4)/i', stripslashes($row['setting_value']), $match)) {
					$extra_images[] = $match[0];
				}
			}
		}
		
		$extras2 = $this->db->query("SELECT style_value FROM `" . DB_PREFIX . "journal3_style`");
		if ($extras2) {
			foreach ($extras2->rows as $row) {

				if (preg_match_all('/data\/(.*?)[-]*\.(?:jpe?g|a?png|gif|bmp|tif?f|ico|svg|webp|webm|pdf|avi|flv|wmv|mov|wav|aac|qt|mpe?g|mp3|mp4)/i', stripslashes($row['style_value']), $match)) {
					$extra_images[] = $match[0];
				}
				
				if (preg_match_all('/catalog\/(.*?)[-]*\.(?:jpe?g|a?png|gif|bmp|tif?f|ico|svg|webp|webm|pdf|avi|flv|wmv|mov|wav|aac|qt|mpe?g|mp3|mp4)/i', stripslashes($row['style_value']), $match)) {
					$extra_images[] = $match[0];
				}
			}
		}

		$extras2 = $this->db->query("SELECT variable_value FROM `" . DB_PREFIX . "journal3_variable`");
		if ($extras2) {
			foreach ($extras2->rows as $row) {

				if (preg_match_all('/data\/(.*?)[-]*\.(?:jpe?g|a?png|gif|bmp|tif?f|ico|svg|webp|webm|pdf|avi|flv|wmv|mov|wav|aac|qt|mpe?g|mp3|mp4)/i', stripslashes($row['variable_value']), $match)) {
					$extra_images[] = $match[0];
				}
				
				if (preg_match_all('/catalog\/(.*?)[-]*\.(?:jpe?g|a?png|gif|bmp|tif?f|ico|svg|webp|webm|pdf|avi|flv|wmv|mov|wav|aac|qt|mpe?g|mp3|mp4)/i', stripslashes($row['variable_value']), $match)) {
					$extra_images[] = $match[0];
				}
			}
		}

		$extra_images = $this->array_flatter($extra_images);
		
		foreach ($extra_images as $value) {
			$images[] = $value;
		}
		
		return $images;
	}
	
	public function getImages($path, $recursive = false) {
		
		if ($recursive) {
			$directories = $this->getDirectoryList($path, true);
		} else {
			$directories = array($path);
		}
		
		$images = array();
		$finfo = finfo_open(FILEINFO_MIME_TYPE);

		foreach ($directories as $directory) {
			$files = glob(rtrim(DIR_IMAGE . str_replace('../', '', $directory), '/') . '/*');

			if (is_array($files) ) {
				$files = array_filter($files, 'is_file');
				$files = array_filter($files, 'filesize');
			} else {
				$files = array();
			}
			
			if (function_exists('exif_imagetype')) {
				foreach ($files as $file) {				
					if (exif_imagetype ($file)) {
						$images[] = utf8_substr($file, strlen(DIR_IMAGE));
					}
				}
			} elseif (function_exists('mime_content_type')) {
				foreach ($files as $file) {				
					if (explode("/", mime_content_type($file))[0] == "image") {
						$images[] = utf8_substr($file, strlen(DIR_IMAGE));
					}
				}
			} elseif (function_exists('finfo_file')) {
				foreach ($files as $file) {	
					if (explode("/", finfo_file($finfo, $file))[0] == "image") {
						$images[] = utf8_substr($file, strlen(DIR_IMAGE));
					}
				}
			} else {
				foreach ($files as $file) {	
					if ($this->is_image($file)) {
						$images[] = utf8_substr($file, strlen(DIR_IMAGE));
					}
				}
			}
		}

		return $images;
	}
				
	public function getDirectoryList($path, $recursive = true) {
		$directory_list = array($path);
		
		$directories = glob(rtrim(DIR_IMAGE . str_replace('../', '', $path), '/') . '/*', GLOB_ONLYDIR);
		
		if ($directories) {
			foreach ($directories as $directory) {
			
				if (glob($directory . "/*", GLOB_NOSORT)) {
					$directory = utf8_substr($directory, strlen(DIR_IMAGE));
					if ($directory && $recursive) { 
						$children = $this->getDirectoryList($directory);
						
						if ($children) {
							foreach ($children as $child) {
								$directory_list[] = $child;
							}
						} else {
							$directory_list[] = $path . $directory;
						}
					}
				}
			}
		}
		
		return $directory_list;
	}
	
	private function array_flatter($input) {
	  $output = array();
	  if (is_array($input)) {
		foreach ($input as $element) {
		  $output = array_merge($output, $this->array_flatter($element));
		}
	  }
	  else { 
		$output[] = $input; 
	  }
	  return $output;
	}
	
	private function is_image($file) {
		$info = getimagesize($file);
		$image_type = $info[2];

		if (explode("/", $info['mime'])[0] == "image") {
			return true;
		} 		
		return false;
	}	
	
}