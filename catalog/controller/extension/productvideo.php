<?php
class ControllerExtensionProductvideo extends Controller {
	public function index() {
		$this->load->language('product/product');
		$this->load->model('catalog/product');
		$this->load->model('tool/image');
		
		$this->document->addStyle('catalog/view/theme/default/stylesheet/tmd_productyoutube.css');
		
		if (isset($this->request->get['product_id'])) {
			$product_id = (int)$this->request->get['product_id'];
		} else {
			$product_id = 0;
		}

		$data['productvideo_status'] = $this->config->get('tmdproductvideo_status');
		$data['productvideo_tabstatus'] = $this->config->get('tmdproductvideo_showintab');

		$videotitle =$this->config->get('tmdproductvideo_title');
		$productvideo_title   = $videotitle[$this->config->get('config_language_id')]['title'];

		if(!empty($productvideo_title)){
			$data['productvideo_title'] = $videotitle[$this->config->get('config_language_id')]['title'];
		}else{
			$data['productvideo_title'] = $this->language->get('text_videotitle');
		}

		if ($this->request->server['HTTPS']) {
			$server = $this->config->get('config_ssl');
		} else {
			$server = $this->config->get('config_url');
		}

		$data['videos'] = array();

		$products_videos = $this->model_catalog_product->getProductVideos($product_id);

		foreach ($products_videos as $result) {
			if (is_file(DIR_IMAGE.'video'.'/'.$result['upload_video'])) {
				$src_uploaded= $server.'image/video/'.$result['upload_video'];
			}else{
				$src_uploaded='';
			}

			if ($result['video_thumbimage']) {
				$tmdthumb =  $this->model_tool_image->resize($result['video_thumbimage'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_additional_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_additional_height'));
			} else {
				$tmdthumb =  $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_additional_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_additional_height'));
			}
			
			$data['videos'][] = array(
				'thumb' => $tmdthumb,
				'popup' => $this->model_tool_image->resize($result['video_thumbimage'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height')),
				'src_youtube' => $result['youtube_video'],
				'src_vimeo' => $result['vimeo_video'],
				'src_uploaded' => $src_uploaded
			);
		}

		return $this->load->view('extension/productvideo', $data);
	}
}