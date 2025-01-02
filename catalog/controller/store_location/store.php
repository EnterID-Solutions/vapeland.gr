<?php
class ControllerStoreLocationStore extends Controller {

  public function index(){
    $this->load->model('store_location/store');
    $this->load->model('tool/image');
    $this->load->language('store_location/store');
    $this->document->addStyle('catalog/view/stylesheet/store_location.css');
    $data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('store_location/store')
		);


    $results = $this->model_store_location_store->getStores();
    $data['stores'] = array();
    foreach($results as $result){
      $data['stores'][] = array(
        'store_location_id' => $result['store_location_id'],
        'name'              => $result['name'],
        'store_tips'        => $this->model_store_location_store->getStoreTips($result['store_location_id']),
        'description'       => html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'),
        'email'             => $result['email'],
        'phone'             => $result['phone'],
        'address'           => $result['address'],
        'image'             => $this->model_tool_image->resize($result['image'], 1200, 800),
        'url'               => $this->url->link('store_location/store/view', 'store_location_id='.$result['store_location_id'], true)
      );
    }


    $data['column_left'] = $this->load->controller('common/column_left');
    $data['column_right'] = $this->load->controller('common/column_right');
    $data['content_top'] = $this->load->controller('common/content_top');
    $data['content_bottom'] = $this->load->controller('common/content_bottom');
    $data['footer'] = $this->load->controller('common/footer');
    $data['header'] = $this->load->controller('common/header');

    $this->response->setOutput($this->load->view('store_location/store_list', $data));
  }

  public function view(){
    if(!isset($this->request->get['store_location_id'])){
      $this->response->redirect($this->url->link('store_location/store', '', true));
    }
    $store_location_id = $this->request->get['store_location_id'];
    $this->load->model('store_location/store');
    $this->load->model('tool/image');
    $this->load->language('store_location/store');
    $this->document->addStyle('catalog/view/stylesheet/store_view.css');
    $this->document->addStyle('catalog/view/stylesheet/tiny-slider.css');
    //$this->document->addScript('catalog/view/javascript/store_location.js');
    $this->document->addScript('catalog/view/javascript/tiny-slider.js');
    $data['heading_title'] = $this->language->get('heading_title_view');
    $data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('store_location/store')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_store_location_view'),
			'href' => $this->url->link('store_location/store/view', 'store_location_id='.$store_location_id)
		);
    $store_location_info = $this->model_store_location_store->getStoreLocation($store_location_id);
    $data['name'] = $store_location_info['name'];
    $data['description'] = html_entity_decode($store_location_info['description']);
    $data['address'] = $store_location_info['address'];
    $data['phone'] = $store_location_info['phone'];
    $data['email'] = $store_location_info['email'];
    $data['image'] = $this->model_tool_image->resize($store_location_info['image'], 1200, 800);
    $data['google_map'] = html_entity_decode($store_location_info['google_map']);
    $data['images'] = array();

    $results = $this->model_store_location_store->getStoreImages($this->request->get['store_location_id']);

    foreach ($results as $result) {
      $data['images'][] = array(
        'item' => $this->model_tool_image->resize($result['image'], 1200, 800)
      );
    }

    $days=array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
    foreach ($days as $day) {
      $opening_times = $this->model_store_location_store->getOpeningTimes($store_location_id, $day);
      if ($opening_times){
        $string=array();
        foreach ($opening_times as $ot) {
          $string[] = date('h:i A', strtotime($ot['start'])).' - '.date('h:i A', strtotime($ot['end']));
        }
        $data['times'][$day] = implode(" <br>\n ", $string);
      } else {
        $data['times'][$day] = $this->language->get('text_store_closed');
      }
    }
    $data['tips'] = $this->model_store_location_store->getStoreTips($store_location_id);

    $data['column_left'] = $this->load->controller('common/column_left');
    $data['column_right'] = $this->load->controller('common/column_right');
    $data['content_top'] = $this->load->controller('common/content_top');
    $data['content_bottom'] = $this->load->controller('common/content_bottom');
    $data['footer'] = $this->load->controller('common/footer');
    $data['header'] = $this->load->controller('common/header');

    $this->response->setOutput($this->load->view('store_location/store_view', $data));
  }

}

?>
