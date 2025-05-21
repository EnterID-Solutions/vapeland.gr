<?php

use TagMaster\ProductPage;
use TagMaster\CheckoutPage;
use TagMaster\Search;
use TagMaster\CheckoutSuccess;
use TagMaster\TagMasterData;

class ControllerExtensionAnalyticsTagMaster extends Controller {
    public function index() {
        $key = $this->config->get('analytics_TagMaster_code');
        $script = "<!-- Google Tag Manager -->
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','". $key  ."');</script>
        <!-- End Google Tag Manager -->
        ";
        $this->document->addScript('admin/view/javascript/tagmaster/tagmaster.js?v=2.351');
        
        $Socital = $this->config->get('analytics_TagMaster_Socital');
        if ($Socital != null) {
            $script .= '<script async id="socital-script" src="https://plugin.socital.com/static/v1/socital.js" data-socital-user-id="'. $Socital .'"></script>';
        }


        if ($this->isProductPage()) {
            $this->load->model("catalog/product");
            $page = new ProductPage($this->request,$this->model_catalog_product, $this->tax, $this->config, $this->session);
        } elseif ($this->isCheckoutPage()) {
            $page = new CheckoutPage($this->request, $this->cart);
        }elseif ($this->isCheckoutSuccess()) {
            $this->load->model("checkout/order");
            $page = new CheckoutSuccess($this->request,TagMasterData::getInstance(), $this->model_checkout_order);
            if ($Socital){
                $page->setAdditionalServiceCode("socital", $Socital);
            }
        }elseif ($this->isSearchPage()) {
            $page = new Search($this->request);
        }

        if (isset($page)) {
            $script .= $page->generateScript();
        }


		return  $script;
	}


    public function isProductPage() {
        return isset($this->request->get['route']) && $this->request->get['route'] == "product/product" && isset($this->request->get['product_id']);
    }

    public function isCheckoutPage() {
        return isset($this->request->get['route']) && $this->request->get['route'] == "checkout/checkout";
    }

    public function isCheckoutSuccess() {
        return isset($this->request->get['route']) && $this->request->get['route'] == "checkout/success";
    }

    private function isSearchPage() {
        return isset($this->request->get['route']) && $this->request->get['route'] == "product/search";
    }


    public function addProductAfter(&$route, &$args, &$output) {
        $this->log->write("Event fired: cart_add_product_after");
        $this->log->write("Arguments: " . print_r($args, true));
        $this->log->write("Output: " . print_r($output, true));
        if (isset($output['success'])) {
            $product_info = $this->model_catalog_product->getProduct($args[0]);
            $output['success'] .= " Additional information about the product: " . $product_info['description'];
        }
    }


    public function checkoutSuccessBefore($route, &$data) {
        $this->log->write("Event fired: checkout_success_before");
        if (isset($this->session->data['order_id'])) {
            $this->log->write("Order ID: " . $this->session->data['order_id']);
        } else {
            $this->log->write("No order ID found in session.");
        }

        if (!empty($this->session->data['order_id'])) {
            TagMasterData::getInstance()->setOrderId($this->session->data['order_id']);
        }

        // $this->log->write("Event fired:facebook  checkout_success_before");
        // if (!empty($this->session->data['order_id'])) {
        //     $this->session->data['facebook_business_order_id'] = $this->session->data['order_id'];
        // }
    }


}
