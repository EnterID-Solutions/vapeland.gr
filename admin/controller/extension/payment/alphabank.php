<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.2 & 7.3
 * @ Decoder version: 1.0.6
 * @ Release: 10/08/2022
 */

// Decoded file for php version 72.
class ControllerExtensionPaymentAlphabank extends Controller
{
    private $error = [];
    private $localLicense = "";
    private $remoteLicense = "";
    private $extension_name = "itc_alphabankv2";
    private $extension_full_name = "Alphabank with Masterpass";
    private $mydomains = ["daska.work", "daskalopoulos.info"];
    private $domain = "";
    // public function __construct($registry)
    // {
    //     parent::__construct($registry);
    //     $alphabank = new alphabank($this->registry);
    //     $alphabank->setName($this->extension_name);
    //     $alphabank->setFullName($this->extension_full_name);
    //     $this->localLicense = $alphabank->getLicense();
    //     if (isset($this->request->post["order_id"])) {
    //         $alphabank->checkOrderId($this->request->post["order_id"]);
    //     }
    //     $remote_license_data = $alphabank->getSavedLicense($this->localLicense);
    //     $this->remoteLicense = $remote_license_data["license"];
    //     $this->domain = $alphabank->getDomain();
    // }
    public function licenseFailed()
    {
        $this->load->library("alphabank");
        $alphabank->setName($this->extension_name);
        $alphabank->setFullName($this->extension_full_name);
        $this->response->setOutput($alphabank->licFailed());
    }
    public function index()
    {
        if ($this->remoteLicense == $this->localLicense || in_array($this->domain, $this->mydomains)) {
            $this->load->language("extension/payment/alphabank");
            $this->document->setTitle($this->language->get("heading_title"));
            $data["license"] = $this->localLicense;
            $this->load->model("setting/setting");
            $this->load->model("extension/payment/alphabank");
            $this->load->model("setting/extension");
            if ($this->request->server["REQUEST_METHOD"] == "POST" && $this->validate()) {
                $this->request->post["payment_alphabank_masterpass_status"] = $this->request->post["payment_alphabank_status"];
                if ($this->request->post["payment_alphabank_status"]) {
                    $this->model_setting_extension->install("payment", "alphabank_masterpass");
                } else {
                    $this->model_setting_extension->uninstall("payment", "alphabank_masterpass");
                }
                $this->model_setting_setting->editSetting("payment_alphabank", $this->request->post);
                $this->session->data["success"] = $this->language->get("text_success");
                $this->response->redirect($this->url->link("marketplace/extension", "user_token=" . $this->session->data["user_token"] . "&type=payment", true));
            }
            $text_strings = ["heading_title", "tab_settings", "entry_mid", "button_save", "tab_about", "error_mid", "button_cancel", "entry_status", "entry_secret", "button_add_module", "text_enabled", "entry_sale", "help_total", "button_remove", "text_disabled", "entry_order_status", "text_installments", "placeholder", "text_yes", "entry_geo_zone", "button_installment_row_add", "text_no", "entry_test", "text_all_zones", "heading_installments_table", "tab_installments_fees", "text_pre_authorization", "text_sale", "entry_sort_order", "heading_installments_epivarinsi", "entry_total", "tab_installments", "entry_from_amount", "heading_installments_number", "entry_final_installments", "heading_installments_amount_from", "heading_installments_amount_to"];
            foreach ($text_strings as $text) {
                $data[$text] = $this->language->get($text);
            }
            if (isset($this->error["warning"])) {
                $data["error_warning"] = $this->error["warning"];
            } else {
                $data["error_warning"] = "";
            }
            if (isset($this->error["error_mid"])) {
                $data["error_mid"] = $this->error["error_mid"];
            } else {
                $data["error_mid"] = "";
            }
            if (isset($this->error["error_secret"])) {
                $data["error_secret"] = $this->error["error_secret"];
            } else {
                $data["error_secret"] = "";
            }
            $data["breadcrumbs"] = [];
            $data["breadcrumbs"][] = ["text" => $this->language->get("text_home"), "href" => $this->url->link("common/dashboard", "user_token=" . $this->session->data["user_token"], true)];
            $data["breadcrumbs"][] = ["text" => $this->language->get("text_payment"), "href" => $this->url->link("marketplace/extension", "user_token=" . $this->session->data["user_token"] . "&type=payment", true)];
            $data["breadcrumbs"][] = ["text" => $this->language->get("heading_title"), "href" => $this->url->link("extension/payment/alphabank", "user_token=" . $this->session->data["user_token"], true)];
            $data["action"] = $this->url->link("extension/payment/alphabank", "user_token=" . $this->session->data["user_token"], true);
            $data["cancel"] = $this->url->link("marketplace/extension", "user_token=" . $this->session->data["user_token"] . "&type=payment", true);
            $data["modules"] = [];
            if (isset($this->request->post["payment_alphabank_module"])) {
                $data["modules"] = $this->request->post["payment_alphabank_module"];
            } else {
                if ($this->config->get("payment_alphabank_module")) {
                    $data["modules"] = $this->config->get("payment_alphabank_module");
                }
            }
            if (isset($this->request->post["payment_alphabank_status"])) {
                $data["payment_alphabank_status"] = $this->request->post["payment_alphabank_status"];
            } else {
                $data["payment_alphabank_status"] = $this->config->get("payment_alphabank_status");
            }
            if (isset($this->request->post["payment_alphabank_mid"])) {
                $data["payment_alphabank_mid"] = $this->request->post["payment_alphabank_mid"];
            } else {
                $data["payment_alphabank_mid"] = $this->config->get("payment_alphabank_mid");
            }
            if (isset($this->request->post["payment_alphabank_secret"])) {
                $data["payment_alphabank_secret"] = $this->request->post["payment_alphabank_secret"];
            } else {
                $data["payment_alphabank_secret"] = $this->config->get("payment_alphabank_secret");
            }
            if (isset($this->request->post["payment_alphabank_sale"])) {
                $data["payment_alphabank_sale"] = $this->request->post["payment_alphabank_sale"];
            } else {
                $data["payment_alphabank_sale"] = $this->config->get("payment_alphabank_sale");
            }
            if (isset($this->request->post["payment_alphabank_3ds"])) {
                $data["payment_alphabank_3ds"] = $this->request->post["payment_alphabank_3ds"];
            } else {
                $data["payment_alphabank_3ds"] = $this->config->get("payment_alphabank_3ds");
            }
            if (isset($this->request->post["payment_alphabank_language"])) {
                $data["payment_alphabank_language"] = $this->request->post["payment_alphabank_language"];
            } else {
                $data["payment_alphabank_language"] = $this->config->get("payment_alphabank_language");
            }
            if (isset($this->request->post["payment_alphabank_test"])) {
                $data["payment_alphabank_test"] = $this->request->post["payment_alphabank_test"];
            } else {
                $data["payment_alphabank_test"] = $this->config->get("payment_alphabank_test");
            }
            if (isset($this->request->post["payment_alphabank_debug"])) {
                $data["payment_alphabank_debug"] = $this->request->post["payment_alphabank_debug"];
            } else {
                $data["payment_alphabank_debug"] = $this->config->get("payment_alphabank_debug");
            }
            if (isset($this->request->post["payment_alphabank_total"])) {
                $data["payment_alphabank_total"] = $this->request->post["payment_alphabank_total"];
            } else {
                $data["payment_alphabank_total"] = $this->config->get("payment_alphabank_total");
            }
            if (isset($this->request->post["payment_alphabank_order_status_id"])) {
                $data["payment_alphabank_order_status_id"] = $this->request->post["payment_alphabank_order_status_id"];
            } else {
                if (NULL !== $this->config->get("payment_alphabank_order_status_id")) {
                    $data["payment_alphabank_order_status_id"] = $this->config->get("payment_alphabank_order_status_id");
                } else {
                    $data["payment_alphabank_order_status_id"] = "";
                }
            }
            if (isset($this->request->post["payment_alphabank_sort_order"])) {
                $data["payment_alphabank_sort_order"] = $this->request->post["payment_alphabank_sort_order"];
            } else {
                if ($this->config->get("payment_alphabank_sort_order")) {
                    $data["payment_alphabank_sort_order"] = $this->config->get("payment_alphabank_sort_order");
                } else {
                    $data["payment_alphabank_sort_order"] = "";
                }
            }
            if (isset($this->request->post["payment_alphabank_doseis"])) {
                $data["payment_alphabank_doseis"] = $this->request->post["payment_alphabank_doseis"];
            } else {
                if ($this->config->get("payment_alphabank_doseis")) {
                    $data["payment_alphabank_doseis"] = $this->config->get("payment_alphabank_doseis");
                } else {
                    $data["payment_alphabank_doseis"] = "";
                }
            }
            if (isset($this->request->post["payment_alphabank_installment_fees"])) {
                $data["payment_alphabank_installment_fees"] = $this->request->post["payment_alphabank_installment_fees"];
            } else {
                $data["payment_alphabank_installment_fees"] = $this->config->get("payment_alphabank_installment_fees");
            }
            $this->load->model("localisation/order_status");
            $data["order_statuses"] = $this->model_localisation_order_status->getOrderStatuses();
            if (isset($this->request->post["payment_alphabank_geo_zone_id"])) {
                $data["payment_alphabank_geo_zone_id"] = $this->request->post["payment_alphabank_geo_zone_id"];
            } else {
                if ($this->config->get("payment_alphabank_geo_zone_id")) {
                    $data["payment_alphabank_geo_zone_id"] = $this->config->get("payment_alphabank_geo_zone_id");
                } else {
                    $data["payment_alphabank_geo_zone_id"] = "";
                }
            }
            $this->load->model("localisation/geo_zone");
            $data["geo_zones"] = $this->model_localisation_geo_zone->getGeoZones();
            $this->load->model("design/layout");
            $data["header"] = $this->load->controller("common/header");
            $data["column_left"] = $this->load->controller("common/column_left");
            $data["footer"] = $this->load->controller("common/footer");
            $this->response->setOutput($this->load->view("extension/payment/alphabank", $data));
        } else {
            $alphabank = new alphabank($this->registry);
            $alphabank->setName($this->extension_name);
            $alphabank->setFullName($this->extension_full_name);
            $this->response->setOutput($alphabank->licFailed());
        }
    }
    private function validate()
    {
        if (!$this->user->hasPermission("modify", "extension/payment/alphabank")) {
            $this->error["warning"] = $this->language->get("error_permission");
        }
        if (!$this->request->post["payment_alphabank_mid"]) {
            $this->error["error_mid"] = $this->language->get("error_mid");
        }
        if (!$this->request->post["payment_alphabank_secret"]) {
            $this->error["error_secret"] = $this->language->get("error_secret");
        }
        if (!$this->error) {
            return true;
        }
        return false;
    }
    public function install()
    {
        $this->load->model("extension/payment/alphabank");
        $this->load->model("setting/event");
        $this->load->model("setting/extension");
        $this->model_user_user_group->addPermission($this->user->getGroupId(), "access", "extension/module/" . $this->request->get["extension"]);
        $this->model_user_user_group->addPermission($this->user->getGroupId(), "modify", "extension/module/" . $this->request->get["extension"]);
        $this->model_extension_payment_alphabank->install();
        $this->model_setting_event->addEvent("alphabank_menu", "admin/view/common/column_left/before", "extension/payment/alphabank/eventMenu");
        $this->model_setting_extension->install("payment", "alphabank_masterpass");
    }
    public function uninstall()
    {
        $this->load->model("extension/payment/alphabank");
        $this->load->model("setting/event");
        $this->load->model("setting/extension");
        $this->model_setting_event->deleteEventByCode("alphabank_menu");
        $this->model_extension_payment_alphabank->uninstall();
        $this->model_setting_extension->uninstall("payment", "alphabank_masterpass");
    }
    public function eventMenu($route, &$data)
    {
        $alphamenu = [];
        $this->load->language("extension/payment/alphabank");
        if ($this->user->hasPermission("access", "extension/payment/alphabank")) {
            $alphamenu[] = ["name" => $this->language->get("text_transaction_menu"), "href" => $this->url->link("extension/payment/alphabank/transactions", "user_token=" . $this->session->data["user_token"], true), "children" => []];
            if ($alphamenu) {
                $data["menus"][] = ["id" => "menu-alphabank", "icon" => "fa fa-credit-card fw", "name" => $this->language->get("text_parent_menu"), "href" => "", "children" => $alphamenu];
            }
        }
    }
    public function transactions()
    {
        if (isset($this->request->get["filter_order"])) {
            $filter_name["filter_order"] = $this->request->get["filter_order"];
            $data["filter_order"] = $this->request->get["filter_order"];
        } else {
            $filter_name["filter_order"] = NULL;
            $data["filter_order"] = NULL;
        }
        if (isset($this->request->get["page"])) {
            $page = $this->request->get["page"];
        } else {
            $page = 1;
        }
        $url = "";
        if (isset($this->request->get["filter_order"])) {
            $url .= "&filter_order=" . urlencode(html_entity_decode($this->request->get["filter_order"], ENT_QUOTES, "UTF-8"));
        }
        $this->load->language("extension/payment/alphabank");
        $this->load->model("extension/payment/alphabank");
        $this->document->setTitle($this->language->get("tr_heading_title"));
        $data["heading_title"] = $this->language->get("tr_heading_title");
        $data["text_list"] = $this->language->get("text_list");
        $data["text_no_results"] = $this->language->get("text_no_results");
        $data["entry_order"] = $this->language->get("entry_order");
        $data["button_filter"] = $this->language->get("button_filter");
        $data["column_order"] = $this->language->get("column_order");
        $data["column_trdate"] = $this->language->get("column_trdate");
        $data["column_trid"] = $this->language->get("column_trid");
        $data["column_StatusFlag"] = $this->language->get("column_StatusFlag");
        $data["column_action"] = $this->language->get("column_action");
        $data["user_token"] = $this->session->data["user_token"];
        $url = "";
        if (isset($this->request->get["filter_order"])) {
            $url .= "&filter_order=" . $this->request->get["filter_order"];
        }
        if (isset($this->request->get["page"])) {
            $url .= "&page=" . $this->request->get["page"];
        }
        if (isset($this->error["warning"])) {
            $data["error_warning"] = $this->error["warning"];
        } else {
            $data["error_warning"] = "";
        }
        $data["breadcrumbs"] = [];
        $data["breadcrumbs"][] = ["text" => $this->language->get("text_home"), "href" => $this->url->link("common/dashboard", "user_token=" . $this->session->data["user_token"], true)];
        $data["breadcrumbs"][] = ["text" => $this->language->get("tr_heading_title"), "href" => $this->url->link("extension/payment/alphabank/transactions", "user_token=" . $this->session->data["user_token"], true)];
        $data["transactions"] = $this->model_extension_payment_alphabank->getTransactions($filter_name);
        $transactions_total = $this->model_extension_payment_alphabank->getTotalTransactions($filter_name);
        $pagination = new Pagination();
        $pagination->total = $transactions_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get("config_limit_admin");
        $pagination->url = $this->url->link("extension/payment/alphabank/transactions", "user_token=" . $this->session->data["user_token"] . $url . "&page={page}", true);
        $data["pagination"] = $pagination->render();
        $data["results"] = sprintf($this->language->get("text_pagination"), $transactions_total ? ($page - 1) * $this->config->get("config_limit_admin") + 1 : 0, $transactions_total - $this->config->get("config_limit_admin") < ($page - 1) * $this->config->get("config_limit_admin") ? $transactions_total : ($page - 1) * $this->config->get("config_limit_admin") + $this->config->get("config_limit_admin"), $transactions_total, ceil($transactions_total / $this->config->get("config_limit_admin")));
        $data["header"] = $this->load->controller("common/header");
        $data["column_left"] = $this->load->controller("common/column_left");
        $data["footer"] = $this->load->controller("common/footer");
        $this->response->setOutput($this->load->view("extension/payment/alphabank_transactions", $data));
    }
    public function modal()
    {
        $this->load->language("extension/payment/alphabank");
        $this->load->model("extension/payment/alphabank");
        $trid = $this->model_extension_payment_alphabank->getTransaction($this->request->post["id"]);
        $html = "<div class=\"table-responsive\"><table class=\"table\">";
        foreach ($trid as $key => $value) {
            $html .= "<tr><th>" . $key . "</th><td>" . $value . "</td></tr>";
        }
        $html .= "</table></div>";
        $this->response->setOutput($html);
    }
}

?>