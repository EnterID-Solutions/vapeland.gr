<?php
class ControllerExtensionModuleOldimage extends Controller {
	private $error = array();
	
	public function index() {   
		$this->load->language('extension/module/oldimage');
		$this->load->model('extension/module/oldimage');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('module_oldimage', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}
		
		$data['heading_title'] = $this->language->get('heading_title');
						
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
		$data['breadcrumbs'] = array();
		
		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], true)
		);
		
		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);
		
		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('extension/module/oldimage', 'user_token=' . $this->session->data['user_token'], true)
		);
		
		$data['action'] = $this->url->link('extension/module/oldimage', 'user_token=' . $this->session->data['user_token'], true);
		$data['delete'] = $this->url->link('extension/module/oldimage/delete', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
		
		
		if (isset($this->request->post['module_oldimage_directory'])) {
			$data['module_oldimage_directory'] = $this->request->post['module_oldimage_directory'];
		} else {
			$data['module_oldimage_directory'] = $this->config->get('module_oldimage_directory');
		}

		if (file_exists(DIR_IMAGE . 'data/')) {
			$dl_catalog = $this->model_extension_module_oldimage->getDirectoryList('catalog');
			$dl_data = $this->model_extension_module_oldimage->getDirectoryList('data');
			$data['directory_list'] = array_merge($dl_catalog, $dl_data);
		} else {
			$data['directory_list'] = $this->model_extension_module_oldimage->getDirectoryList('catalog');
		}
		
		$data['user_token'] = $this->session->data['user_token'];
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/oldimage', $data));
	}
	
	public function install() {
		$this->load->model('extension/module/oldimage');
		$this->model_extension_module_oldimage->install();
	}
	
	public function uninstall() {
		$this->load->model('extension/module/oldimage');
		$this->model_extension_module_oldimage->uninstall();
	}
	
	public function check() {
		$old_files = array();

		if (isset($this->request->post['module_oldimage_directory']) && is_array($this->request->post['module_oldimage_directory']) ) {
			foreach ($this->request->post['module_oldimage_directory'] as $key => $directory) {
				if ($directory['path']) {
					$old_files[$key] = $this->getOldImages($directory['path'], $directory['recursive'] ? true : false);
				} else {
					$old_files[$key] = array();
				}
			}
		}
		$this->response->setOutput(json_encode(array_values($old_files)));
	}
	
	private function getOldImages($directory, $recursive) {
		
		$this->load->model('extension/module/oldimage');
		
		if ($this->request->server['HTTPS']) {
			$base = HTTPS_CATALOG . 'image/';
		} else {
			$base = HTTP_CATALOG . 'image/';
		}
		
		$used_files = $this->model_extension_module_oldimage->getActiveImages();
		$images = $this->model_extension_module_oldimage->getImages($directory, $recursive);
		$orphan_images = array();
		
		foreach ($images as $image) {
			if ( !in_array($image, $used_files) ) {
				$orphan_images[] = array(
					'name' => '&nbsp;&nbsp; <img src="' . $base . $image . '" height="42" width="42">&nbsp;&nbsp;<a href="' . $base . $image . '" target="_blank">' . $image . '</a>', 
					'path' => $image,
				);
			}
		}
		
		return $orphan_images;
	}
	
	public function delete() {
		$this->load->language('extension/module/oldimage');
		$this->load->model('extension/module/oldimage');
		
		$deleted = 0;
		
		$json = array(
			'message' => '',
			'data' => array(),
		);
		
		if ($this->validate()) {
			if (isset($this->request->post['path']) && $this->request->post['path']) {
				$recursive = isset($this->request->post['recursive']) && $this->request->post['recursive'] ? true : false;

				if (isset($this->request->post['delete']) && is_array($this->request->post['delete'])) {
					$dir_files = $this->getOldImages($this->request->post['path'], $recursive);
					
					foreach ($dir_files as $file) {
						$dir_files_flat[] = $file['path'];
					}
					
					foreach ($this->request->post['delete'] as $image) {
						if (in_array($image, $dir_files_flat) ) {
							if (unlink(rtrim(DIR_IMAGE . str_replace('../', '', $image), '/'))) {
								$deleted ++;
							}
						}
					}
				}
				
				$json['message'] = '<div class="' . ($deleted ? 'alert alert-success' : 'alert alert-danger') . '">' . sprintf($this->language->get('text_deleted'), $deleted) . '</div>';
				$json['data'] = $this->getOldImages($this->request->post['path'], $recursive);
			} else {
				$json['message'] = '<div class="alert alert-danger">' . $this->language->get('error_directory') . '</div>';
			}
		} else {
			$json['message'] = '<div class="alert alert-danger">' . $this->language->get('error_delete') . '</div>';
		}

		$this->response->setOutput(json_encode($json));
	}
	
	public function moveFiles() {
		$this->load->language('extension/module/oldimage');
		$this->load->model('extension/module/oldimage');
		
		$moved = 0;
		
		$json = array(
			'message' => '',
			'data' => array(),
		);
		
		if (!file_exists(DIR_IMAGE . 'backup')) {
			mkdir(DIR_IMAGE . 'backup', 0755, true);
			touch(DIR_IMAGE . 'backup/index.html');
		}
		
		if ($this->validate()) {
			if (isset($this->request->post['path']) && $this->request->post['path']) {
				$recursive = isset($this->request->post['recursive']) && $this->request->post['recursive'] ? true : false;

				if (isset($this->request->post['delete']) && is_array($this->request->post['delete'])) {
					$dir_files = $this->getOldImages($this->request->post['path'], $recursive);

					foreach ($dir_files as $file) {
						$dir_files_flat[] = $file['path'];
					}
					
					$date = date('Y-m-d');

					foreach ($this->request->post['delete'] as $image) {
					
						if (!file_exists(DIR_IMAGE . 'backup/' . dirname($image))) {
							mkdir(DIR_IMAGE . 'backup/' . dirname($image), 0755, true);
						}

						if (in_array($image, $dir_files_flat) ) {
							if (rename(rtrim(DIR_IMAGE . str_replace('../', '', $image), '/'), DIR_IMAGE . 'backup/' . $image)) {
								$moved ++;
							}	
						}
					}
				}
				
				$json['message'] = '<div class="' . ($moved ? 'alert alert-success' : 'alert alert-danger') . '">' . sprintf($this->language->get('text_moved'), $moved) . '</div>';
				$json['data'] = $this->getOldImages($this->request->post['path'], $recursive);
			} else {
				$json['message'] = '<div class="alert alert-danger">' . $this->language->get('error_directory') . '</div>';
			}
		} else {
			$json['message'] = '<div class="alert alert-danger">' . $this->language->get('error_delete') . '</div>';
		}

		$this->response->setOutput(json_encode($json));
	}
	
	public function clear_cache() {
		$this->load->language('extension/module/oldimage');
	
		if (!$this->validate()) {
			$return = $this->language->get('error_delete');
		}else{
			$cachefiles = glob(DIR_CACHE . '*');
			$imgfiles = glob(DIR_IMAGE . 'cache/*');
			
			// Clear image cache
			foreach($imgfiles as $imgfile) {
				$this->deleteCacheFiles($imgfile);
			}
			
			// Clear data cache
			foreach($cachefiles as $cachefile) {
				$this->deleteCacheFiles($cachefile);
			}
			
			$return = $this->language->get('text_success_del');	
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($return));
	}
	
	public function deleteCacheFiles($file){         
		if (file_exists($file)) {
			if (is_dir($file)){
				$directory = opendir($file);
				while($filename = readdir($directory)) {
					if($filename != "." && $filename != "..") { // GLOB does not return hidden files and folders so this is not needed. But why not.
						$this->deleteCacheFiles($file . "/" . $filename); 
					}
				}
				
				closedir($directory);  
				rmdir($file);
		    } else if (basename($file) != 'index.html') {
				@unlink($file);
			}			
		}
	}
	

	public function delete_backup() { 
	
		$this->load->language('extension/module/oldimage');
	
		if (!$this->validate()) {
			$return = $this->language->get('error_delete');
		} else {
			$imgfiles = glob(DIR_IMAGE . 'backup/*');
			
			foreach($imgfiles as $imgfile) {
				$this->deleteCacheFiles($imgfile);
			}
						
			$return = $this->language->get('text_success_bu');	
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($return));
	}
	
	private function validate() {
	
		if (!$this->user->hasPermission('modify', 'extension/module/oldimage')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}
?>