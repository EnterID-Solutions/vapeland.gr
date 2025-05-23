<?php
//error_reporting(0);

// Uncomment the line below if you have the error on sitemap: "XML Parsing Error: XML or text declaration not at start of entity" 
// This is just a workaround, you should find the module or file which is inserting the unwanted new line on your website.
//if (ob_get_contents()) ob_clean();

class ControllerFeedAdvancedSitemap extends Controller {
  
  private $final_file;
  private $temp_file;
  public $output;
  public $OC_V2;
  private $isSSL;
  
  public function __construct($registry) {
		$this->OC_V2 = substr(VERSION, 0, 1) == 2;
		parent::__construct($registry);
    $this->isSSL = (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1')));
	}
  
	public function index() {
    $limit = $this->config->get('advanced_sitemap_limit') ? $this->config->get('advanced_sitemap_limit') : 500;
    
    $config = $this->config->get('advanced_sitemap_cfg');
    
    $this->load->model('catalog/product');
    $this->load->model('catalog/category');
    
    $output = '<?xml version="1.0" encoding="utf-8"?>';
    $output .= '<?xml-stylesheet type="text/xsl" href="'.$this->url->link('feed/advanced_sitemap/xslindex', '', $this->isSSL).'"?>';
    $output .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    
    $types = array();
    
    // count items
    if (!empty($config['product']['status'])) {
      $types['product'] = $this->get_product('', true);
    }
    
    if (!empty($config['category']['status'])) {
      $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE c2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND c.status = '1'")->row;
      $types['category'] = $query['total'];
    }
    
    if (!empty($config['information']['status'])) {
      $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "information c LEFT JOIN " . DB_PREFIX . "information_to_store c2s ON (c.information_id = c2s.information_id) WHERE c2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND c.status = '1'")->row;
      $types['information'] = $query['total']+1;
    }
    
    if (!empty($config['manufacturer']['status'])) {
      $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "manufacturer m LEFT JOIN " . DB_PREFIX . "manufacturer_to_store m2s ON (m.manufacturer_id = m2s.manufacturer_id) WHERE m2s.store_id = '" . (int)$this->config->get('config_store_id') . "'")->row;
      $types['brand'] = $query['total'];
    }
    
    if (!empty($config['journal']['status'])) {
      $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "manufacturer m LEFT JOIN " . DB_PREFIX . "manufacturer_to_store m2s ON (m.manufacturer_id = m2s.manufacturer_id) WHERE m2s.store_id = '" . (int)$this->config->get('config_store_id') . "'")->row;
      //$types['journal'] = $query['total'];
      $types['journal'] = 1;
      //$output .= '<journal>'.$types['brand'].'</journal>';
    }

    # extra_sitemap_type

    foreach($types as $type => $items) {
      $page = ($items > $limit) ? 1 : '';
      
      if ($items) {
        while ($items > 0) {
          $output .= '  <sitemap>';
          if (!empty($this->request->get['lang'])) {
            $output .= '    <loc>' .htmlspecialchars($this->url->link('feed/advanced_sitemap/'.$type, 'lang='.$this->request->get['lang'].($page ? '&page='.$page++ : ''), $this->isSSL)).'</loc>';
          } else {
            $output .= '    <loc>' .htmlspecialchars($this->url->link('feed/advanced_sitemap/'.$type, ($page ? 'page='.$page++ : ''), $this->isSSL)).'</loc>';
          }
          $output .= '    <lastmod>'. date('Y-m-d') .'</lastmod>';
          $output .= '  </sitemap>';
          
          $items -= $limit;
        }
      }
    }
    
    $output .= '</sitemapindex>';
    
    $this->renderFeed($output);
  }
  
  public function product() {
    $this->generate('product');
  }
  
	public function category() {
    $this->generate('category');
  }
  
  public function information() {
    $this->generate('information');
  }
  
  public function brand() {
    $this->generate('brand');
  }
  
  public function journal() {
    $this->generate('journal');
  }
  
  # extra_sitemap_method

	public function generate($type) {
    ini_set('memory_limit', -1);
    
    if (isset($this->request->get['grid'])) {
      $grid = '_grid';
    } else {
      $grid = '';
    }
    
    $page = 1;
    if (!empty($this->request->get['page'])) {
      $page = (int) $this->request->get['page'];
    }
    
    $file = DIR_CACHE . 'sitemaps/' . $type . $grid . '-' . $page . '.xml';
    
    if($type == 'brand') {
      $type = 'manufacturer';
    }
    
    $this->start_time = microtime(true)*1000;
    
    $config = $this->config->get('advanced_sitemap_cfg');
    
    $fullsize_img = !empty($config['fullsize_img']);
    $display_img = !empty($config['display_img']);
    $additional_img = !empty($config['additional_img']);
    $main_img = !empty($config['img']);
    
    $config = isset($config[$type]) ? $config[$type] : array();
    $limit = $this->config->get('advanced_sitemap_limit') ? $this->config->get('advanced_sitemap_limit') : 500;
    
		if (empty($config['status'])) {
      die('This feed is not active');
    }
    
    if(!is_dir(DIR_CACHE . 'sitemaps')) {
      mkdir(DIR_CACHE . 'sitemaps');
    }
    
    if (empty($config['cache_delay'])) {
      switch ($config['freq']) {
        case 'always':
          $config['cache_delay'] = 0;
          $config['cache_unit'] = 'minute';
        break;
        case 'hourly':
          $config['cache_delay'] = 50;
          $config['cache_unit'] = 'minute';
        break;
        case 'daily':
          $config['cache_delay'] = 23;
          $config['cache_unit'] = 'hour';
        break;
        case 'weekly':
          $config['cache_delay'] = 6;
          $config['cache_unit'] = 'day';
        break;
        case 'monthly':
          $config['cache_delay'] = 26;
          $config['cache_unit'] = 'day';
        break;
        case 'yearly':
          $config['cache_delay'] = 11;
          $config['cache_unit'] = 'month';
        break;
        case 'never':
          $config['cache_delay'] = 1337;
          $config['cache_unit'] = 'year';
        break;
      }
    }
    
    if (file_exists($file) && (filemtime($file) > strtotime('-'. $config['cache_delay'] .' ' . $config['cache_unit']))) {
      $this->display($file);
    }
    
    
    //$this->load->model('catalog/product');
    
    $this->load->model('tool/image');

    $this->load->model('localisation/language');
    $languages = array();
    $results = $this->model_localisation_language->getLanguages();
    
    $fullcode = (strlen($this->request->get['lang']) == 2) ? false : true;
    
    foreach ($results as $result) {
      if ($result['status']) {
        $languages[($fullcode ? $result['code'] : substr($result['code'], 0, 2))] = array(
          'language_id' => $result['language_id'],
          'code' => $result['code']
        );
        
        // set current language to default
        //if ($this->config->get('advanced_sitemap_default_lang') == $result['code']) {
        if ($this->config->get('config_language') == $result['code']) {
          $this->config->set('config_language_id', $result['language_id']);
          $this->session->data['language'] = $result['code'];
        }
      }
    }
    
    $this->languages = $languages;
    
    // save current language id
    $current_lang_id = $this->config->get('config_language_id');
    $current_lang_code = $this->session->data['language'];
    
    
    $feed_lang = false;

    if (!empty($this->request->get['lang']) && in_array($this->request->get['lang'], array_keys($languages))) {
      $feed_lang = $this->request->get['lang'];
      $this->config->set('config_language_id', $languages[$feed_lang]['language_id']);
      $this->session->data['language'] = $languages[$feed_lang]['code'];
      $hreflang = false;
    } else {
      //$hreflang = $this->config->get('mlseo_hreflang');
      if (count($this->languages) > 1) {
        $hreflang = true;
      } else {
        $hreflang = false;
      }
    }
      
    if (!empty($config['language'])) {
      $this->config->set('config_language_id', $config['language']);
    }
    
    $config['hreflang'] = $hreflang;
    
    // set file header
    $output = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
    $output .= '<?xml-stylesheet type="text/xsl" href="'.$this->url->link('feed/advanced_sitemap/xsl'.$grid, 'type='.$type . ($feed_lang ? '&lang='.$feed_lang:''), $this->isSSL).'"?>' . "\n";
    $output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";
    
    $items = array();
    
    if (in_array($type, array('journal'))) {
      $output .= $this->{'generate_'.$type}(array('start' => ($page-1)*$limit, 'limit' => $limit), $config);
    } else {
      $items = $this->{'get_'.$type}(array('start' => ($page-1)*$limit, 'limit' => $limit));
    }
    
    //$products = $this->model_catalog_product->getProducts(array('start' => $offset, 'limit' => $limit));
    
    if ($type == 'product') {
      if ($this->config->get($this->config->get('config_theme') . '_image_popup_width')) {
        $img_width = $this->config->get($this->config->get('config_theme') . '_image_popup_width');
        $img_height = $this->config->get($this->config->get('config_theme') . '_image_popup_height');
      } else if ($this->config->get('config_image_popup_width')) {
        $img_width = $this->config->get('config_image_popup_width');
        $img_height = $this->config->get('config_image_popup_height');
      } else {
        $img_width = $img_height = 500;
      }
    }
      
    if ($type == 'information') {
      $output .= '<url>';
      $output .= '<loc>' . htmlspecialchars($this->url->link('common/home')) . '</loc>';
      $output .= '<changefreq>'.$config['freq'].'</changefreq>';
      $output .= '<priority>'.$config['priority'].'</priority>';
      $output .= '</url>';
    }
    
    foreach ($items as $item) {
      if (isset($item['meta_robots']) && ($item['meta_robots'] == 'none' || $item['meta_robots'] == 'noindex')) continue;
      
      $img_loc = false;
      if ($type == 'product' && !empty($item['image']) && ($main_img || $display_img)) {
        if ($fullsize_img) {
          $img_loc = HTTP_SERVER.'image/'.$item['image'];
        } else {
          $img_loc = $this->model_tool_image->resize($item['image'], $img_width, $img_height);
        }
      }
      
      if ($img_loc && $display_img) {
        //$output .= '<thumb>' . htmlspecialchars($this->model_tool_image->resize($item['image'], 50, 50)) . '</thumb>';
        $output .= '<url thumb="' . htmlspecialchars($this->model_tool_image->resize($item['image'], 50, 50)) . '">';
      } else {
        $output .= '<url>';
      }
      
      if ($type == 'category') {
        $path = $this->fullCategoryPath($item);
        $output .= '<loc>' . htmlspecialchars($this->url->link('product/category', 'path='. ($path ? $path . '_' : '') . $item['category_id'])) . '</loc>';
      } else if ($type == 'manufacturer') {
        $output .= '<loc>' . htmlspecialchars($this->url->link('product/manufacturer/info', 'manufacturer_id=' . $item['manufacturer_id'])) . '</loc>';
      } else if ($type == 'information') {
        $output .= '<loc>' . htmlspecialchars($this->url->link('information/information', 'information_id=' . $item['information_id'])) . '</loc>';
      } else {
        $output .= '<loc>' . htmlspecialchars($this->url->link('product/'.$type, $type.'_id=' . $item[$type.'_id'])) . '</loc>';
      }
      
      if (!empty($hreflang)) {
        foreach ($languages as $lang) {
          $this->config->set('config_language_id', $lang['language_id']);
          $this->session->data['language'] = $lang['code'];
          
          if ($type == 'category') {
            //$path = $this->fullCategoryPath($item);
            $output .= '<xhtml:link rel="alternate" hreflang="' . $lang['code'] . '" href="' . $this->url->link('product/category', 'path='. ($path ? $path . '_' : '') . $item['category_id']) . '"/>';
          } else if ($type == 'manufacturer') {
            $output .= '<xhtml:link rel="alternate" hreflang="' . $lang['code'] . '" href="' . $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $item['manufacturer_id']) . '"/>';
          } else if ($type == 'information') {
            $output .= '<xhtml:link rel="alternate" hreflang="' . $lang['code'] . '" href="' . $this->url->link('information/information', 'information_id=' . $item['information_id']) . '"/>';
          } else {
            $output .= '<xhtml:link rel="alternate" hreflang="' . $lang['code'] . '" href="' . $this->url->link('product/'.$type, $type.'_id=' . $item[$type.'_id']) . '"/>';
          }
        }
        
        // restore current language id
        $this->config->set('config_language_id', $current_lang_id);
        $this->session->data['language'] = $current_lang_code;
      }
      
      if ($img_loc) {
        if (isset($this->request->get['grid'])) {
          $output .= '<thumb>' . htmlspecialchars($this->model_tool_image->resize($item['image'], 160, 120)) . '</thumb>';
        }
        
        if ($main_img) {
          $output .= '<image:image>';
          $output .= '<image:loc>' . htmlspecialchars($img_loc) . '</image:loc>';
          $output .= '<image:caption>' . htmlspecialchars($item['name']) . '</image:caption>';
          $output .= '<image:title>' . htmlspecialchars($item['name']) . '</image:title>';
          $output .= '</image:image>';
        }
      }
      
      if ($additional_img && $type == 'product' && !empty($item['additional_images']) && $img_loc = $this->model_tool_image->resize($item['image'], $img_width, $img_height)) {
        foreach ($item['additional_images'] as $image) {
          $img_loc = false;
          if ($fullsize_img) {
            $img_loc = HTTP_SERVER.'image/'.$image;
          } else {
            $img_loc = $this->model_tool_image->resize($image, $img_width, $img_height);
          }
          
          if ($img_loc) {
            $output .= '<image:image>';
            $output .= '<image:loc>' . htmlspecialchars($img_loc) . '</image:loc>';
            $output .= '<image:caption>' . htmlspecialchars($item['name']) . '</image:caption>';
            $output .= '<image:title>' . htmlspecialchars($item['name']) . '</image:title>';
            $output .= '</image:image>';
          }
        }
      }
      
      $output .= '<changefreq>'.$config['freq'].'</changefreq>';
      $output .= '<priority>'.$config['priority'].'</priority>';
      
      if (!empty($item['date_modified']) && !(strpos($item['date_modified'], '0000-00-00') !== false)) {
        $output .= '<lastmod>'. substr($item['date_modified'], 0, strpos($item['date_modified'], ' ')) .'</lastmod>';
      } else if (!empty($item['date_added']) && !(strpos($item['date_added'], '0000-00-00') !== false)) {
        $output .= '<lastmod>'. substr($item['date_added'], 0, strpos($item['date_added'], ' ')) .'</lastmod>';
      } else {
        $output .= '<lastmod>'. date('Y-m-d') .'</lastmod>';
      }
      
      $output .= '</url>' . "\n";
    }

    $output .= '</urlset>';
    
    // $end_time = microtime(true)*1000;
    // var_dump('time: '. ((int)($end_time - $this->start_time) /1000). 'ms');
    // var_dump('mem peak: ' . memory_get_peak_usage());
    
    if (!$config['cache_delay'] && $config['freq'] != 'always') {
      file_put_contents($file, $output);
    }
    
    $this->renderFeed($output);
	}
  
  // Callback
  public function callback() {
    //file_put_contents('system/cache/met.log', "\n callback called \n", FILE_APPEND);
    file_put_contents($this->temp_file, $this->output, FILE_APPEND);
  }
  
  // Output file
  private function display($file) {
    header('Content-Type: application/xml');
    header('Cache-Control: must-revalidate');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    exit;
  }
  
  private function renderFeed($output) {
    $this->response->addHeader('Content-Type: application/xml');
		$this->response->setOutput($output);
  }
  
  private function get_product($data, $count = false) {
    // save current language id
		$current_lang_id = $this->config->get('config_language_id');
		$current_lang_code = $this->session->data['language'];
    $config = $this->config->get('advanced_sitemap_cfg');
    
    $extra_where = '';
    
    if (!empty($config['in_stock'])) {
      $extra_where = " AND p.quantity > 0";
    }
    
    if ($count) {
      $total = $this->db->query("SELECT COUNT(DISTINCT p.product_id) AS total FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'" . $extra_where)->row;
      return $total['total'];
    }
    
    if (1) {
      $rows = $this->db->query("SELECT DISTINCT *, pd.name AS name, p.image, m.name AS manufacturer, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special, (SELECT points FROM " . DB_PREFIX . "product_reward pr WHERE pr.product_id = p.product_id AND customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "') AS reward, (SELECT ss.name FROM " . DB_PREFIX . "stock_status ss WHERE ss.stock_status_id = p.stock_status_id AND ss.language_id = '" . (int)$this->config->get('config_language_id') . "') AS stock_status, (SELECT wcd.unit FROM " . DB_PREFIX . "weight_class_description wcd WHERE p.weight_class_id = wcd.weight_class_id AND wcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS weight_class, (SELECT lcd.unit FROM " . DB_PREFIX . "length_class_description lcd WHERE p.length_class_id = lcd.length_class_id AND lcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS length_class, (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating, (SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review r2 WHERE r2.product_id = p.product_id AND r2.status = '1' GROUP BY r2.product_id) AS reviews, p.sort_order FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'" . $extra_where . " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'])->rows;
      
      foreach ($rows as $k => $row) {
        $rows[$k]['additional_images'] = array();
        $images = $this->db->query("SELECT image FROM " . DB_PREFIX . "product_image WHERE product_id = '" . $row['product_id'] . "' ORDER BY sort_order ASC")->rows;
        
        foreach ($images as $img) {
          if (!empty($img['image'])) {
            $rows[$k]['additional_images'][] = $img['image'];
          }
        }
      }
    }
    
    return $rows;
  }
  
  private function get_category($data) {
    $rows = $this->db->query("SELECT * FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'  AND c.status = '1' ORDER BY c.sort_order, LCASE(cd.name) LIMIT " . (int)$data['start'] . "," . (int)$data['limit'])->rows;
    return $rows;
  }
  
  private function get_information($data) {
    $this->load->model('catalog/information');

    $rows = $this->model_catalog_information->getInformations($data);
    
    return $rows;
  }
  
  private function get_manufacturer($data) {
    $this->load->model('catalog/manufacturer');

    $rows = $this->model_catalog_manufacturer->getManufacturers($data);
    
    return $rows;
  }

  private function generate_journal($data, $config) {
    $output = '';
    
    if (!file_exists(DIR_APPLICATION . 'model/journal2/blog.php')) return '';
    
    // save current language id
    $current_lang_id = $this->config->get('config_language_id');
    $current_lang_code = $this->session->data['language'];

    $output .= '<url>';
    $output .= '<loc>' . htmlspecialchars($this->url->link('journal2/blog', '')) . '</loc>';
    
    if (!empty($config['hreflang'])) {
      foreach ($this->languages as $lang) {
        $this->config->set('config_language_id', $lang['language_id']);
        $this->session->data['language'] = $lang['code'];
        $this->load->model('journal2/blog');
        $output .= '<xhtml:link rel="alternate" hreflang="' . $lang['code'] . '" href="' . $this->url->link('journal2/blog') . '"/>';
      }
      
      // restore current language id
      $this->config->set('config_language_id', $current_lang_id);
      $this->session->data['language'] = $current_lang_code;
    }
    
    $blog_date = $this->db->query("SELECT date_created FROM `".DB_PREFIX."journal2_blog_post` WHERE status = 1 ORDER BY date_created DESC LIMIT 1")->row;
    
    $output .= '<changefreq>'.$config['freq'].'</changefreq>';
    $output .= '<priority>'.$config['priority'].'</priority>';
    $output .= '<lastmod>'. (!empty($blog_date['date_created']) ? substr($blog_date['date_created'], 0, strpos($blog_date['date_created'], ' ')) : date('Y-m-d')) .'</lastmod>';
    $output .= '</url>' . "\n";
      
    foreach ($this->model_journal2_blog->getCategories() as $category) {
      $output .= '<url>';
      $output .= '<loc>' . htmlspecialchars($this->url->link('journal2/blog', 'journal_blog_category_id=' . $category['category_id'])) . '</loc>';
      
      if (!empty($config['hreflang'])) {
        foreach ($this->languages as $lang) {
          $this->config->set('config_language_id', $lang['language_id']);
          $this->session->data['language'] = $lang['code'];

          $output .= '<xhtml:link rel="alternate" hreflang="' . $lang['code'] . '" href="' . $this->url->link('journal2/blog', 'journal_blog_category_id=' . $category['category_id']) . '"/>';
        }
        
        // restore current language id
        $this->config->set('config_language_id', $current_lang_id);
        $this->session->data['language'] = $current_lang_code;
      }
      
      $blog_date = $this->db->query("SELECT p.date_created FROM `".DB_PREFIX."journal2_blog_post` p LEFT JOIN `".DB_PREFIX."journal2_blog_post_to_category` p2c ON p.post_id = p2c.post_id WHERE status = 1 AND p2c.category_id = " . (int)$category['category_id'] . " ORDER BY date_created DESC LIMIT 1")->row;
      
      $output .= '<changefreq>'.$config['freq'].'</changefreq>';
      $output .= '<priority>'.$config['priority'].'</priority>';
      $output .= '<lastmod>'. (!empty($blog_date['date_created']) ? substr($blog_date['date_created'], 0, strpos($blog_date['date_created'], ' ')) : date('Y-m-d')) .'</lastmod>';
      $output .= '</url>' . "\n";
    }

    foreach ($this->model_journal2_blog->getPosts() as $post) {
      $output .= '<url>';
      $output .= '<loc>' . htmlspecialchars($this->url->link('journal2/blog/post', 'journal_blog_post_id=' . $post['post_id'])) . '</loc>';
      
      if (!empty($config['hreflang'])) {
        foreach ($this->languages as $lang) {
          $this->config->set('config_language_id', $lang['language_id']);
          $this->session->data['language'] = $lang['code'];

          $output .= '<xhtml:link rel="alternate" hreflang="' . $lang['code'] . '" href="' . $this->url->link('journal2/blog/post', 'journal_blog_post_id=' . $post['post_id']) . '"/>';
        }
        
        // restore current language id
        $this->config->set('config_language_id', $current_lang_id);
        $this->session->data['language'] = $current_lang_code;
      }
    
      $output .= '<changefreq>'.$config['freq'].'</changefreq>';
      $output .= '<priority>'.$config['priority'].'</priority>';
      $output .= '<lastmod>'. substr($post['date'], 0, strpos($post['date'], ' ')) .'</lastmod>';
      $output .= '</url>' . "\n";
    }
    
    return $output;
  }
  
  private function fullCategoryPath($category) {
    $path = '';
    
    while (!empty($category['parent_id'])) {
      $path = $category['parent_id'] . '_' . $path;
      $category = $this->db->query("SELECT parent_id FROM " . DB_PREFIX . "category WHERE category_id = '" . $category['parent_id']. "'")->row;
    }
    
    return rtrim($path, '_');
  }
  
  public function xslindex() {
    $this->xsl('index');
  }
  
  public function xsl_grid() {
    $this->xsl('grid');
  }
  
  public function xsl($xsl = '') {
    $this->response->addHeader('Content-Type: application/xml; charset=utf-8');
    $data['title'] = $xsl == 'index' ? 'Sitemap Index' : 'XML Sitemap';
    $data['index'] = $xsl == 'index' ? true : false;
    $data['type'] = isset($this->request->get['type']) ? $this->request->get['type'] : '';
    $data['index_link'] = $this->url->link('feed/advanced_sitemap', (!empty($this->request->get['lang']) ? 'lang='.$this->request->get['lang']:''), $this->isSSL);
    
    $data['cfg'] = $this->config->get('advanced_sitemap_cfg');
    
    if ($xsl == 'index') {
      $config = $this->config->get('advanced_sitemap_cfg');
      
      $data['count'] = array();
      
      // count items
      if (!empty($config['product']['status'])) {
        $data['count']['product'] = $this->get_product('', true);
      }
      
      if (!empty($config['category']['status'])) {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_to_store c2s ON (c.category_id = c2s.category_id) WHERE c2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND c.status = '1'")->row;
        $data['count']['category'] = $query['total'];
      }
      
      if (!empty($config['information']['status'])) {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "information c LEFT JOIN " . DB_PREFIX . "information_to_store c2s ON (c.information_id = c2s.information_id) WHERE c2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND c.status = '1'")->row;
        $data['count']['information'] = $query['total']+1;
      }
      
      if (!empty($config['manufacturer']['status'])) {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "manufacturer m LEFT JOIN " . DB_PREFIX . "manufacturer_to_store m2s ON (m.manufacturer_id = m2s.manufacturer_id) WHERE m2s.store_id = '" . (int)$this->config->get('config_store_id') . "'")->row;
        $data['count']['brand'] = $query['total'];
      }
      
      if (!empty($config['journal']['status'])) {
        //$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "manufacturer m LEFT JOIN " . DB_PREFIX . "manufacturer_to_store m2s ON (m.manufacturer_id = m2s.manufacturer_id) WHERE m2s.store_id = '" . (int)$this->config->get('config_store_id') . "'")->row;
        //$data['count']['journal'] = 1;
      }
    
    }

    if ($xsl == 'grid') {
      $grid = '_grid';
    } else {
      $grid = '';
    }
    
    $template = 'default/template/module/advanced_sitemap'.$grid.'.tpl';
    
    if (version_compare(VERSION, '3', '>=')) {
      $this->config->set('template_directory', 'default/template/'); // fix issue of undefined template dir
      $this->config->set('template_engine', 'template');
      
      $this->response->setOutput($this->load->view('module/advanced_sitemap'.$grid, $data));
    } else if (version_compare(VERSION, '2.2', '>=')) {
			$this->response->setOutput($this->load->view('module/advanced_sitemap'.$grid, $data));
		} else if ($this->OC_V2) {
			$this->response->setOutput($this->load->view($template, $data));
		} else {
			$this->data = $data;
			$this->template = $template;
			$this->response->setOutput($this->render());
		}
  }
}