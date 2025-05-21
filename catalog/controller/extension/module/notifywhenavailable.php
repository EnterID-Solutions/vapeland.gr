<?php
use \vendor\isenselabs\notifywhenavailable\config as Config;
use \vendor\isenselabs\notifywhenavailable\maillib as MailLib;

class ControllerExtensionModuleNotifyWhenAvailable extends Controller
{
    private $data = array();

    public function __construct($registry) {
        parent::__construct($registry);

        /* Module-specific declarations - Begin */
        $this->load->language(Config::notifywhenavailable_path);

        // Variables
        $this->data['moduleName'] = Config::notifywhenavailable_name;
        $this->data['modulePath'] = Config::notifywhenavailable_path;
        /* Module-specific loaders - End */

        $this->load->model('catalog/product');
        $this->load->model('setting/setting');
    }

    public function index($setting)
    {
        if ($this->config->get('config_theme') == 'theme_journal3') {
			$curent_template = 'journal3';
        } else {
            $curent_template = $this->config->get('config_theme');
        }

        if (file_exists('catalog/view/theme/' . $curent_template . '/stylesheet/notifywhenavailable.css')) {
            $this->document->addStyle('catalog/view/theme/' . $curent_template . '/stylesheet/notifywhenavailable.css');
        } else {
            $this->document->addStyle('catalog/view/theme/default/stylesheet/notifywhenavailable.css');
        }


        if (strcmp($curent_template, 'journal2') === 0) {
            if (file_exists('catalog/view/theme/' . $curent_template . '/stylesheet/notifywhenavailable_journal.css')) {
                $this->document->addStyle('catalog/view/theme/' . $curent_template . '/stylesheet/notifywhenavailable_journal.css');
            } else {
                $this->document->addStyle('catalog/view/theme/default/stylesheet/notifywhenavailable_journal.css');
            }
        }

        $this->data['NotifyWhenAvailable_Button'] = $this->language->get('NotifyWhenAvailable_Button');

        if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
            $this->data['nwa_data']['notifywhenavailable'] = str_replace('http', 'https', $this->config->get('notifywhenavailable'));
        } else {
            $this->data['nwa_data']['notifywhenavailable'] = $this->config->get('notifywhenavailable');
        }

        if (!isset($this->data['nwa_data']['notifywhenavailable']['CustomTitle'][$this->config->get('config_language')])) {
            $this->data['nwa_data']['notifywhenavailable']['CustomTitle'] = '';
        } else {
            $this->data['nwa_data']['notifywhenavailable']['CustomTitle'] = $this->data['nwa_data']['notifywhenavailable']['CustomTitle'][$this->session->data['language']];
        }

        if (!isset($this->data['nwa_data']['notifywhenavailable']['ButtonLabel'][$this->config->get('config_language')])) {
            $this->data['nwa_data']['notifywhenavailable']['ButtonLabel'] = $this->language->get('NotifyWhenAvailable_Button');
        } else {
            $this->data['nwa_data']['notifywhenavailable']['ButtonLabel'] = $this->data['nwa_data']['notifywhenavailable']['ButtonLabel'][$this->session->data['language']];
        }

        if (isset($this->request->get['product_id'])) {
            $this->data['product_id'] = $this->request->get['product_id'];
        } else {
            $this->data['product_id'] = 0;
        }

        if ($this->data['nwa_data']['notifywhenavailable']['Enabled'] == 'yes') {
            $this->document->addScript('catalog/view/javascript/notifywhenavailable/notifywhenavailable.js');
        }

        $this->data['button_cart'] = $this->language->get('button_cart');

        return $this->load->view(Config::notifywhenavailable_path, $this->data);
    }

    public function submitform()
    {
        $send_email       = false;
        $selected_options = NULL;
		$data['error']   = false;
		$data['success'] = false;

		$setting = $this->config->get('notifywhenavailable');

		if (!isset($setting['CustomText'][$this->config->get('config_language')])) {
            $string['CustomText'] = '';
        } else {
            $string['CustomText'] = $setting['CustomText'][$this->session->data['language']];
        }

		$strings = html_entity_decode($string['CustomText']);

        if (isset($this->session->data['nwa'][$this->request->post['NWAProductID']]['selected_options'])) {
            $selected_options = serialize($this->session->data['nwa'][$this->request->post['NWAProductID']]['selected_options']);
        }

        if(!empty($this->session->data['language'])) {
            $language = $this->session->data['language'];
        } else {
            $language = $this->config->get('config_language');
        }

        if (isset($this->request->post['NWAYourName']) && isset($this->request->post['NWAYourEmail']) && isset($this->request->post['NWAProductID'])) {
            $pass = true;
            if (!filter_var($this->request->post['NWAYourEmail'], FILTER_VALIDATE_EMAIL)) {
				$pass = false;
                $data['error']['email'] = $this->language->get('NotifyWhenAvailable_Error2');
            }
			if ($setting['Captcha'] == 'yes' && (strpos($strings, '{captcha}') !== false)) {
				if (empty($this->session->data['NWA_captcha']) || empty($this->request->post['NWACaptcha']) || $this->request->post['NWACaptcha'] != $this->session->data['NWA_captcha']) {
					$pass = false;
					$data['error']['captcha'] = $this->language->get('NotifyWhenAvailable_Error4');
				}
			}
			if ($pass) {
                $name = $this->db->escape($this->request->post['NWAYourName']);
                $email = $this->db->escape($this->request->post['NWAYourEmail']);
                $comment = $this->db->escape(!empty($this->request->post['NWAYourComment']) ? $this->request->post['NWAYourComment'] : '');
                $product_id = $this->db->escape($this->request->post['NWAProductID']);

                $privacyPolicy = 0;
                if (!empty($this->request->post['privacy_policy'])) {
                    $privacyPolicy = (int)$this->request->post['privacy_policy'];
                }

                // select only the last request by this customer for this combination of product+options - the assumption here is that the most recent one most accurately reflects whether he has already been notified about it
                $entry_exists_check = $this->db->query("SELECT * FROM " . DB_PREFIX . "notifywhenavailable WHERE customer_email = '" . $email . "' AND product_id = '" . $product_id . "' AND selected_options = '" . $selected_options . "' AND store_id = '" . $this->config->get('config_store_id') . "' ORDER BY notifywhenavailable_id DESC LIMIT 1");

                if ($entry_exists_check->num_rows && $entry_exists_check->row['customer_notified'] == 0) {
                    // an entry already exists and the customer has not been notified yet, update only not notified entries
                    $run_query = $this->db->query("UPDATE " . DB_PREFIX . "notifywhenavailable SET customer_name = '" . $name . "', customer_comment = '" . $comment . "', language = '" . $language . "' WHERE customer_email = '" . $email . "' AND product_id = '" . $product_id . "' AND selected_options = '" . $selected_options . "' AND store_id = '" . $this->config->get('config_store_id') . "' AND customer_notified = 0");
                    $send_email = false;
					$data['success'] = $this->language->get('NotifyWhenAvailable_AlreadySubscribed');
                } else {
                    // either entry doesn't exist or already notified, so create a new one to preserve all time statistics
                    $run_query = $this->db->query("INSERT INTO `" . DB_PREFIX . "notifywhenavailable`
                            (customer_email, customer_name, customer_comment, product_id, selected_options, date_created, store_id, language, privacy_policy)
                            VALUES ('" . $email . "', '" . $name . "', '" . $comment . "', '" . $product_id . "',  '" . $selected_options . "' ,NOW(), '" . $this->config->get('config_store_id') . "' ,'" . $language . "','" . $privacyPolicy . "')
                    ");
                    $send_email = true;
					$data['success'] = $this->language->get('NotifyWhenAvailable_Success');
                }

                // GDPR Compliance
                if ($privacyPolicy && is_file(DIR_SYSTEM . 'library' . DIRECTORY_SEPARATOR . 'gdpr.php') && $setting['Enabled'] == 'yes') {
                    $this->load->library('gdpr');
                    $this->gdpr->newOptin($this->config->get('config_account_id'), $email, 'NotifyWhenAvailable');
                }
            }
        } else {
            $data['error']['nodata'] = $this->language->get('NotifyWhenAvailable_Error');
        }

        if ($send_email == true) {

            if (isset($this->request->post['NWAProductID'])) {
                $product = $this->model_catalog_product->getProduct($this->request->post['NWAProductID']);
            }

            $this->data['nwa_data']['notifywhenavailable'] = $this->config->get('notifywhenavailable');

            /*
            Send confirmation to customer
            */
            if ($this->data['nwa_data']['notifywhenavailable']['CustomerNotification'] == 'yes') {

                if (isset($this->request->post['store_id'])) {
                    $store_id = $this->request->post['store_id'];
                } else if (isset($this->request->get['store_id'])) {
                    $store_id = $this->request->get['store_id'];
                } else {
                    $store_id = $this->config->get('config_store_id');
                }
                $store_data = $this->getStore($store_id);
                $store_config = $this->getStoreConfig($store_data['store_id']);
                if (!isset($this->data['nwa_data']['notifywhenavailable']['NotificationEmailText'])) {
                    $EmailText    = '';
                    $EmailSubject = '';
                } else {
                    $EmailText    = $this->data['nwa_data']['notifywhenavailable']['NotificationEmailText'][$this->config->get('config_language_id')];
                    $EmailSubject = $this->data['nwa_data']['notifywhenavailable']['NotificationEmailSubject'][$this->config->get('config_language_id')];
                }

                $customerEmail = $this->request->post['NWAYourEmail'];

                $string          = html_entity_decode($EmailText);
                $patterns        = array();
                $patterns[0]     = '/{name}/';
                $patterns[1]     = '/{product_name}/';
                $patterns[2]     = '/{product_model}/';
                $replacements    = array();
                $replacements[0] = $this->request->post['NWAYourName'];
                $replacements[1] = $product['name'];
                $replacements[2] = $product['model'];

                $text = preg_replace($patterns, $replacements, $string);

                /*$mailToUser                = new Mail($this->config->get('config_mail_engine'));
                $mailToUser->protocol      = $this->config->get('config_mail_protocol');
                $mailToUser->parameter     = $this->config->get('config_mail_parameter');
                $mailToUser->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
                $mailToUser->smtp_username = $this->config->get('config_mail_smtp_username');
                $mailToUser->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
                $mailToUser->smtp_port     = $this->config->get('config_mail_smtp_port');
                $mailToUser->smtp_timeout  = $this->config->get('config_mail_smtp_timeout');

                $mailToUser->setTo($customerEmail);
                $mailToUser->setFrom($this->config->get('config_email'));
                $mailToUser->setSender($store_data['name']);
                $mailToUser->setSubject($EmailSubject);
                $mailToUser->setHtml($text);
                $mailToUser->send();*/
				$senderMail = html_entity_decode($store_config['config_name'], ENT_QUOTES, 'UTF-8');
                MailLib::send($customerEmail, $senderMail, $EmailSubject, $text, $this->registry, $store_config);
            }

            /*
            Send notification to admin
            */
            if ($this->data['nwa_data']['notifywhenavailable']['Notifications'] == 'yes') {
                $text = "Someone wants to be notified about a product that currently is out of stock!<br /><br />
                User Name: " . $this->request->post['NWAYourName'] . "<br />
                User Email: " . $this->request->post['NWAYourEmail'] . "<br />";
                $text .= "Selected Product: " . $product['name'] . "<br /><br />";
                $text .= "You can find more information in your <a href='" . HTTP_SERVER . "admin/index.php?route=extension/module/notifywhenavailable'>admin panel</a>!";

                $mail = new Mail($this->config->get('config_mail_engine'));
                $mail->protocol = $this->config->get('config_mail_protocol');
                $mail->parameter = $this->config->get('config_mail_parameter');
                $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
                $mail->smtp_username = $this->config->get('config_mail_smtp_username');
                $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
                $mail->smtp_port = $this->config->get('config_mail_smtp_port');
                $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

                $mail->setTo($this->config->get('config_email'));
                $mail->setFrom($this->config->get('config_email'));
                $mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
                $mail->setSubject(html_entity_decode("Someone used the option NotifyWhenAvailable", ENT_QUOTES, 'UTF-8'));
                $mail->setHtml($text);
                $mail->send();
            }
        }

		$this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
    }

    public function shownotifywhenavailableform()
    {
        $this->data['nwa_heading_title']                    = $this->language->get('nwa_heading_title');
        $this->data['NotifyWhenAvailable_Title']        = $this->language->get('NotifyWhenAvailable_Title');
        $this->data['NotifyWhenAvailable_SubmitButton'] = $this->language->get('NotifyWhenAvailable_SubmitButton');
        $this->data['NotifyWhenAvailable_Error1']       = $this->language->get('NotifyWhenAvailable_Error1');
        $this->data['NotifyWhenAvailable_Error2']       = $this->language->get('NotifyWhenAvailable_Error2');
        $this->data['NotifyWhenAvailable_Error3']       = $this->language->get('NotifyWhenAvailable_Error3');
        $this->data['NotifyWhenAvailable_Success']      = $this->language->get('NotifyWhenAvailable_Success');
        $this->data['NWA_YourName']                     = $this->language->get('NWA_YourName');
        $this->data['NWA_YourEmail']                    = $this->language->get('NWA_YourEmail');
        $this->data['NWA_Comment']                      = $this->language->get('NWA_Comment');

        $curent_template = $this->config->get('config_template');

        if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
            $this->data['nwa_data']['notifywhenavailable'] = str_replace('http', 'https', $this->config->get('notifywhenavailable'));
        } else {
            $this->data['nwa_data']['notifywhenavailable'] = $this->config->get('notifywhenavailable');
        }

        if (!isset($this->data['nwa_data']['notifywhenavailable']['CustomText'][$this->config->get('config_language')])) {
            $this->data['nwa_data']['notifywhenavailable']['CustomText'] = '';
        } else {
            $this->data['nwa_data']['notifywhenavailable']['CustomText'] = $this->data['nwa_data']['notifywhenavailable']['CustomText'][$this->session->data['language']];
        }

        if ((isset($this->request->get['route'])) && ($this->request->get['route'] == "product/product")) {
            $this->data['NotifyWhenAvailableCurrentURL'] = $this->url->link("product/product", "product_id=" . $this->request->get['product_id'], "");
        } else {
            if (strpos(HTTP_SERVER, 'www.') && strpos(HTTPS_SERVER, 'www.')) {
                $siteName = $_SERVER["SERVER_NAME"];
            } else {
                $siteName = str_replace("www.", "", $_SERVER['SERVER_NAME']);
            }
            $this->data['NotifyWhenAvailableCurrentURL'] = "http://" . $siteName . $_SERVER["REQUEST_URI"];
        }

        $this->data['privacyPolicy'] = '';
        if (!empty($this->data['nwa_data']['notifywhenavailable']['PrivacyPolicy'])) {
            $this->load->model('catalog/information');
            $information = $this->model_catalog_information->getInformation($this->config->get('config_account_id'));
            $this->data['privacyPolicy'] = '<label id="NWAPrivacyPolicy"><input type="checkbox" name="privacy_policy" value="1"> ' . sprintf($this->language->get('NWA_privacy'), 'index.php?route=information/information&information_id=' . $information['information_id'], $information['title']) . '</label><br>';
        }

		$this->data['captcha_form'] = '';
        if (!empty($this->data['nwa_data']['notifywhenavailable']['Captcha']) && $this->data['nwa_data']['notifywhenavailable']['Captcha'] == 'yes') {
            $this->data['captcha_form'] = '<input type="text" class="form-control" name="NWACaptcha" id="NWACaptcha" placeholder=""/></p><p align="left"><img src="index.php?route='.Config::notifywhenavailable_path.'/captcha" id="NWACaptchaImage" alt="">';
        }

        if (isset($this->request->get['product_id'])) {
            $product_id = (int) $this->request->get['product_id'];
        } else {
            $product_id = 0;
        }

        $checker = $this->customer->getId();
        if (!empty($checker)) {
            $this->data['customer_email'] = $this->customer->getEmail();
            $this->data['customer_name']  = $this->customer->getFirstName() . ' ' . $this->customer->getLastName();
        } else {
            $this->data['customer_email'] = '';
            $this->data['customer_name']  = '';
        }

        $this->data['NotifyWhenAvailableProductID'] = $product_id;

        if ($this->data['nwa_data']['notifywhenavailable']['Enabled']) {
            $string = html_entity_decode($this->data['nwa_data']['notifywhenavailable']['CustomText']);
            $patterns = array();
            $patterns[0] = '/{name_field}/';
            $patterns[1] = '/{email_field}/';
            $patterns[2] = '/{comment_field}/';
            $patterns[3] = '/{captcha}/';
            $patterns[4] = '/{submit_button}/';
            $replacements = array();
            $replacements[0] = '<input type="text" class="form-control" name="NWAYourName" id="NWAYourName" placeholder="'.$this->data['NWA_YourName'].'" value="'.$this->data['customer_name'].'" />';
            $replacements[1] = '<input type="text" class="form-control" name="NWAYourEmail" id="NWAYourEmail" placeholder="'.$this->data['NWA_YourEmail'].'" value="'.$this->data['customer_email'].'" />';
            $replacements[2] = '<textarea id="NWAYourComment" name="NWAYourComment" class="form-control" rows="2"></textarea>';
            $replacements[3] = $this->data['captcha_form'];
            $replacements[4] = $this->data['privacyPolicy'] . '<a id="NotifyWhenAvailableSubmit" class="btn btn-default">'.$this->data['NotifyWhenAvailable_SubmitButton'].'</a>';
            $this->data['custom_text'] = preg_replace($patterns, $replacements, $string);
        }

        if (!empty($this->data['nwa_data']['notifywhenavailable']['CustomCSS'])) {
            $this->data['nwa_data']['notifywhenavailable']['CustomCSS'] = htmlspecialchars_decode($this->data['nwa_data']['notifywhenavailable']['CustomCSS']);
        }
        $this->response->setOutput($this->load->view(Config::notifywhenavailable_path . '_form', $this->data));
    }

    public function sendMails()
    {

        if (isset($this->request->post['store_id'])) {
            $store_id = $this->request->post['store_id'];
        } else if (isset($this->request->get['store_id'])) {
            $store_id = $this->request->get['store_id'];
        } else {
            $store_id = 0;
        }

        $store_data = $this->getStore($store_id);
        $store_config = $this->getStoreConfig($store_data['store_id']);

        $settings = $this->model_setting_setting->getSetting('notifywhenavailable', $store_id);
        $settings = (isset($settings['notifywhenavailable'])) ? $settings['notifywhenavailable'] : array();

        if (isset($settings['Enabled']) && $settings['Enabled'] == 'yes' && isset($settings['ScheduleEnabled']) && $settings['ScheduleEnabled'] = 'yes') {
            $query = $this->db->query("SELECT DISTINCT super.*, product.name as product_name FROM `" . DB_PREFIX . "notifywhenavailable` super
				JOIN `" . DB_PREFIX . "product_description` product on super.product_id = product.product_id
				WHERE customer_notified=0 and product.language_id = (SELECT language_id FROM `" . DB_PREFIX . "language` WHERE code = super.language LIMIT 1) and store_id = " . (int) $store_id . "
				ORDER BY `date_created` DESC");

            $counter = 0;
            $report  = array();
            foreach ($query->rows as $row) {
                $send                    = false;
                $row['selected_options'] = (!empty($row['selected_options'])) ? unserialize($row['selected_options']) : array();

                $product = $this->model_catalog_product->getProduct($row['product_id']);
                if (sizeof($row['selected_options']) == 0) {
                    if ($product['quantity'] > 0) {
                        $send = true;
                    }
                } else {
                    $product_options = $this->model_catalog_product->getProductOptions($row['product_id']);
                    $matchCount      = 0;
                    $userCount       = count($row['selected_options']);
                    foreach ($row['selected_options'] as $row_options) {
                        foreach ($product_options as $product_option) {
                            foreach ($product_option['product_option_value'] as $options) {
                                if ((($row_options['option_value_id'] == $options['option_value_id']) && ($row_options['product_option_value_id'] == $options['product_option_value_id']) && (($options['quantity'] > 0)))) {
                                    $matchCount++; // Do iteration if the quantity of selected option by user is > 0
                                }
                            }
                        }
                    }
                    if ($matchCount == $userCount){
                        $send = true;
                    }
                }

                if ($send) {
                    $this->sendEmailWhenAvailable($row, $store_data, $store_config);
                    $counter++;
                    $report[] = $row['customer_name'] . ' (' . $row['customer_email'] . ') - ' . $row['product_name'];
                }
            }

            $result = "Cron was executed successfully! A total of <strong>" . $counter . "</strong> emails were sent to the customers.<br />";

            if ($counter > 0 && sizeof($report) > 0) {
                $result .= "<br />Here is a list with the notified customers:<br /><ul>";
                foreach ($report as $rep) {
                    $result .= "<li>" . $rep . "</li>";
                }
                $result .= "</ul>";
            }

            if (isset($settings['CronNotify']) && $settings['CronNotify'] == 'yes') {
                $mailToUser                = new Mail($this->config->get('config_mail_engine'));
                $mailToUser->protocol      = $this->config->get('config_mail_protocol');
                $mailToUser->parameter     = $this->config->get('config_mail_parameter');
                $mailToUser->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
                $mailToUser->smtp_username = $this->config->get('config_mail_smtp_username');
                $mailToUser->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
                $mailToUser->smtp_port     = $this->config->get('config_mail_smtp_port');
                $mailToUser->smtp_timeout  = $this->config->get('config_mail_smtp_timeout');
                $mailToUser->setTo($this->config->get('config_email'));
                $mailToUser->setFrom($this->config->get('config_email'));
                $mailToUser->setSender(html_entity_decode($store_data['name'], ENT_QUOTES, 'UTF-8'));
                $mailToUser->setSubject(html_entity_decode('NotifyWhenAvailable Sheduled Task', ENT_QUOTES, 'UTF-8'));
                $mailToUser->setHtml($result);
                $mailToUser->setText(html_entity_decode($result, ENT_QUOTES, 'UTF-8'));
                $mailToUser->send();
            } else {
                echo $result;
            }
        }
    }

    public function sendEmailWhenAvailable($row, $store_data, $store_config)
    {
        $product_id   = $row['product_id'];
        $product_info = $this->model_catalog_product->getProduct($product_id);

        // find the localized name of the product
        $localized_product_name = $this->db->query('SELECT `'.DB_PREFIX.'product_description`.name FROM `'.DB_PREFIX.'product_description` ' .
            'LEFT JOIN `'.DB_PREFIX.'language` ON `'.DB_PREFIX.'language`.`language_id` = `'.DB_PREFIX.'product_description`.`language_id` ' .
            'WHERE `'.DB_PREFIX.'product_description`.`product_id` = '.$row['product_id'].' and `'.DB_PREFIX.'language`.`code` = "'.$row['language'].'"');
        if ($localized_product_name->num_rows == 1) {
            $product_info['name'] = $localized_product_name->row['name'];
            // else we have to fall back to the default language, already returned by model_catalog_product->getProduct()
        }

        if ($product_info['image']) {
            $this->load->model('tool/image');
            $image = $this->model_tool_image->resize($product_info['image'], 200, 200);
        } else {
            $image = false;
        }

        $NotifyWhenAvailable = $this->model_setting_setting->getSetting('notifywhenavailable', $row['store_id']);
        $NotifyWhenAvailable = (isset($NotifyWhenAvailable['notifywhenavailable'])) ? $NotifyWhenAvailable['notifywhenavailable'] : array();

        if (!isset($NotifyWhenAvailable['EmailText'][$row['language']])) {
            $EmailText    = '';
            $EmailSubject = '';
        } else {
            $EmailText    = $NotifyWhenAvailable['EmailText'][$row['language']];
            $EmailSubject = $NotifyWhenAvailable['EmailSubject'][$row['language']];
        }

        $string          = html_entity_decode($EmailText);
        $patterns        = array();
        $patterns[0]     = '/{c_name}/';
        $patterns[1]     = '/{p_name}/';
        $patterns[2]     = '/{p_model}/';
        $patterns[3]     = '/{p_image}/';
        $patterns[4]     = '/http:\/\/{p_link}/';
        $replacements    = array();
        $replacements[0] = $row['customer_name'];
        $replacements[1] = "<a href='" . $store_data['url'] . "index.php?route=product/product&product_id=" . $product_id . "' target='_blank'>" . $product_info['name'] . "</a>";
        $replacements[2] = $product_info['model'];
        $replacements[3] = "<img src='" . $image . "' />";
        $replacements[4] = $store_data['url'] . "index.php?route=product/product&product_id=" . $product_id;

        $text = preg_replace($patterns, $replacements, $string);

		$message  = '<html dir="ltr" lang="en">' . "\n";
		$message .= '  <head>' . "\n";
		$message .= '    <title>' . $EmailSubject . '</title>' . "\n";
		$message .= '    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">' . "\n";
		$message .= '  </head>' . "\n";
		$message .= '  <body>' . html_entity_decode($text, ENT_QUOTES, 'UTF-8') . '</body>' . "\n";
		$message .= '</html>' . "\n";

        $mailToUser                = new Mail($this->config->get('config_mail_engine'));
        $mailToUser->protocol      = $this->config->get('config_mail_protocol');
        $mailToUser->parameter     = $this->config->get('config_mail_parameter');
        $mailToUser->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
        $mailToUser->smtp_username = $this->config->get('config_mail_smtp_username');
        $mailToUser->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
        $mailToUser->smtp_port     = $this->config->get('config_mail_smtp_port');
        $mailToUser->smtp_timeout  = $this->config->get('config_mail_smtp_timeout');

        $mailToUser->setTo($row['customer_email']);
        $mailToUser->setFrom($store_config['config_email']);
        $mailToUser->setSender(html_entity_decode($store_config['name'], ENT_QUOTES, 'UTF-8'));
        $mailToUser->setSubject(html_entity_decode($EmailSubject, ENT_QUOTES, 'UTF-8'));
        $mailToUser->setHtml($message);
        $mailToUser->send();


        $update_customers = $this->db->query("UPDATE `" . DB_PREFIX . "notifywhenavailable` SET customer_notified=1 WHERE product_id = " . $product_id . " and store_id = " . $row['store_id']);
    }

    public function checkQuantityNWA()
    {

        $json = array();

        if (isset($this->request->post['product_ids'])) {
            $product_ids = $this->request->post['product_ids'];
        } elseif (isset($this->request->post['product_id'])) {
        	$product_ids[] = $this->request->post['product_id'];
        } else {
            $product_ids = array();
        }



        if (!empty($product_ids)) {
            foreach ($product_ids as $product_id) {
                $product_info = $this->model_catalog_product->getProduct($product_id);
                $product_data = array();
                try {
                    $preorder_settings = $this->config->get('preorder');

                    if (!empty($preorder_settings)) {
                        $this->load->model('extension/module/preorder');
                        $preorder = $this->model_extension_module_preorder->checkPreOrder($product_id);

                        if(!empty($preorder['preorder_product'])) {
                            $product_data['product_id'] = $product_id;
                            $product_data['PO'] = $preorder['preorder_product'];
                        }
                    }
                } catch(\Exception $e) {
                    // Exception handler
                }

                if (isset($this->request->post['store_id'])) {
                    $store_id = $this->request->post['store_id'];
                } elseif (isset($this->request->get['store_id'])) {
                    $store_id = $this->request->get['store_id'];
                } else {
                    $store_id = 0;
                }

                $nwa_settings = $this->model_setting_setting->getSetting('notifywhenavailable', $store_id);

				if (isset($this->session->data['language'])) {
					$lang_code = $this->session->data['language'];
				} else {
					$lang_code = $this->config->get('config_language');
				}

                if ($product_info['quantity'] <= 0  && isset($product_info['stock_status_id']) && isset($nwa_settings['notifywhenavailable']['stock_status'][$product_info['stock_status_id']]) && $nwa_settings['notifywhenavailable']['stock_status'][$product_info['stock_status_id']] == 'on') {
                    $product_data['product_id'] = $product_id;
                    $product_data['NWA_text'] = $nwa_settings['notifywhenavailable']['ButtonLabel'][$lang_code];
                }

                if ($product_info) {
                    if (isset($this->request->post['quantity'])) {
                        $quantity = (int) $this->request->post['quantity'];
                    } else {
                        $quantity = 1;
                    }

                    if (isset($this->request->post['option'])) {
                        $option = array_filter($this->request->post['option']);
                    } else {
                        $option = array();
                    }

                    $product_options       = $this->model_catalog_product->getProductOptions($product_id);
                    $product_option_values = array();

                    foreach ($product_options as $product_option) {
                        if ($product_option['required'] && empty($option[$product_option['product_option_id']])) {
                            $json['error']['option'][$product_option['product_option_id']] = sprintf($this->language->get('error_required'), $product_option['name']);
                        }

                        if (isset($option) && !empty($option) && (empty($json['error']) && !isset($json['error']))) {
                            if (!$product_option['required'] && empty($option[$product_option['product_option_id']])) {
                                continue;
                            }
                            switch ($product_option['type']) {
                                case 'radio':
                                    $temp_option_values      = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value as a JOIN " . DB_PREFIX . "option_value_description as b ON a.option_value_id = b.option_value_id WHERE product_option_value_id = '" . (int) $option[$product_option['product_option_id']] . "' AND product_option_id = '" . (int) $product_option["product_option_id"] . "'");
                                    $product_option_values[] = $temp_option_values->row;
                                    break;

                                case 'checkbox':
                                    if (!isset($product_option['product_option_id']) || !isset($option[$product_option['product_option_id']]) || !is_array($option[$product_option['product_option_id']])) break;
                                    foreach ($option[$product_option['product_option_id']] as $checkbox_id) {
                                        $temp_option_values      = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value as a JOIN " . DB_PREFIX . "option_value_description as b ON a.option_value_id = b.option_value_id WHERE product_option_value_id = '" . (int) $checkbox_id . "' AND product_option_id = '" . (int) $product_option["product_option_id"] . "'");
                                        $product_option_values[] = $temp_option_values->row;

                                    }
                                    break;
                                case 'select':
                                    $temp_option_values      = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value as a JOIN " . DB_PREFIX . "option_value_description as b ON a.option_value_id = b.option_value_id WHERE product_option_value_id = '" . (int) $option[$product_option['product_option_id']] . "' AND product_option_id = '" . (int) $product_option["product_option_id"] . "'");
                                    $product_option_values[] = $temp_option_values->row;
                                    break;
                            }
                        }

                    }

                    if (empty($json['error']) && !isset($json['error'])) {
                        foreach ($product_option_values as $product_selected_options) {
                            if ($product_options && isset($product_selected_options['quantity']) && ($product_selected_options['quantity'] <= 0 && isset($product_info['stock_status_id']) && isset($nwa_settings['notifywhenavailable']['stock_status'][$product_info['stock_status_id']]) && $nwa_settings['notifywhenavailable']['stock_status'][$product_info['stock_status_id']] == 'on')) {
                                $product_data['product_id'] = $product_id;
                                $product_data['NWA_text'] = $nwa_settings['notifywhenavailable']['ButtonLabel'][$lang_code];
                            }
                        }
                        $this->session->data['nwa'][$product_id]['selected_options'] = $product_option_values;
                    }
                }

                if(!empty($product_data)) {
                    $json[] = $product_data;
                    $json['replace_add_to_cart'] =  isset($nwa_settings['notifywhenavailable']['ReplaceAddToCart']) ? $nwa_settings['notifywhenavailable']['ReplaceAddToCart'] : 1;
                }
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    private function getStore($store_id) {
        if ($store_id && $store_id != 0) {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "store WHERE store_id = " . $store_id . " LIMIT 1");
            $store = $query->row;
        } else {
            $store['store_id'] = 0;
            $store['name'] = $this->config->get('config_name');
            $store['url'] = $this->getCatalogURL();
        }

        return $store;
    }

    private function getCatalogURL()
    {
        if (isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1'))) {
            $storeURL = HTTP_SERVER;
        } else {
            $storeURL = HTTPS_SERVER;
        }
        return $storeURL;
    }

    private function getStoreConfig($store_id) {
        $this->load->model('setting/setting');
        return $this->model_setting_setting->getSetting('config', $store_id);
    }

    /** Event handlers **/
    public function catalogViewProductProductBefore(&$route, &$data, &$template) {
        // REFID 05, 07
        $product_id = $data['product_id'];
        $this->load->model('catalog/product');
        $product_info = $this->model_catalog_product->getProduct($product_id);

        $data['quantity'] = $product_info['quantity'];

        $data['NotifyWhenAvailable']  = $this->config->get('notifywhenavailable');
        $this->language->load(Config::notifywhenavailable_path);
        $data['NotifyWhenAvailable_Button'] = $this->language->get('NotifyWhenAvailable_Button');
    }

    public function catalogControllerProductProductBefore(&$route, &$data) {
        // REFID 08
        // add a temporary model event to trick the controller that quantities are not 0 so they'd be displayed when the module is enabled
        $this->event->register('model/catalog/product/getProductOptions/after', new Action('extension/module/notifywhenavailable/catalogModelCatalogProductGetOptionsAfter'), 0);
    }

    public function catalogControllerProductProductAfter(&$route, &$data, &$output) {
        // REFID 08
        // unhook the model event
        $this->event->unregister('model/catalog/product/getProductOptions/after','extension/module/notifywhenavailable/catalogModelCatalogProductGetOptionsAfter');
    }

    public function catalogModelCatalogProductGetOptionsAfter(&$route, &$args, &$output) {
        // REFID 08
        // fake the quantity to force display of the option values if the module is enabled
        // the quantity itself is not shown by default, only used for checking whether to display the option
        $nwa = $this->config->get('notifywhenavailable');
        if (!empty($nwa['Enabled']) && $nwa['Enabled']=='yes') {
        	// check if the module is enabled for this product's stock status
        	$product = $this->model_catalog_product->getProduct($args[0]);
        	if (isset($nwa['stock_status'][$product['stock_status_id']]) && $nwa['stock_status'][$product['stock_status_id']] == 'on') {
	            foreach ($output as &$option) {
	                foreach ($option['product_option_value'] as &$option_value) {
	                	if (!isset($option_value['subtract'])) {
	                		print_r($option_value);
	                		exit;
	                	}
	                    if ($option_value['quantity'] == 0) {
	                        $option_value['quantity'] = 1;
	                        $option_value['subtract'] = true;
	                    }
	                }
	            }
	        }
        }
    }

    public function catalogModelCatalogProductGetProductAfter(&$route, &$args, &$output) {
        if ($output !== false) {
            $query = 'SELECT `stock_status_id` FROM `' . DB_PREFIX . 'product` WHERE `product_id` = ' . $output['product_id'];
            $result = $this->db->query($query);
            $output['stock_status_id'] = $result->row['stock_status_id'];
        }
    }

    public function catalogViewJournal2QuickviewAfter(&$route, &$data, &$output) {
        $nwa_settings = $this->config->get('notifywhenavailable');
        if(!empty($nwa_settings['Enabled']) && $nwa_settings['Enabled'] == 'yes') {
            $nwaModulePath = Config::notifywhenavailable_path;
            $containerInsert = $this->load->controller($nwaModulePath);
            $output = str_replace('<div id="container">', '<div id="container">'.$containerInsert, $output);
            if (file_exists('catalog/view/theme/' . $this->config->get('config_template') . '/stylesheet/notifywhenavailable.css')) {
               $nwa_css = 'catalog/view/theme/' . $this->config->get('config_template') . '/stylesheet/notifywhenavailable.css';
            } else {
               $nwa_css = 'catalog/view/theme/default/stylesheet/notifywhenavailable.css';
            }
            if (file_exists('catalog/view/theme/' . $this->config->get('config_template') . '/stylesheet/notifywhenavailable_journal.css')) {
                $nwa_journal_css = 'catalog/view/theme/' . $this->config->get('config_template') . '/stylesheet/notifywhenavailable_journal.css';
            } else {
                $nwa_journal_css = 'catalog/view/theme/default/stylesheet/notifywhenavailable_journal.css';
            }
            $headInsert = '<link rel="stylesheet" href="'.$nwa_css.'"/>' .
                          '<link rel="stylesheet" href="'.$nwa_journal_css.'"/>';
            $output = str_replace('</head>', $headInsert . '</head>', $output);
        }
    }

	public function captcha() {
		$this->session->data['NWA_captcha'] = substr(sha1(mt_rand()), 17, 6);

		$image = imagecreatetruecolor(150, 35);

		$width = imagesx($image);
		$height = imagesy($image);

		$black = imagecolorallocate($image, 0, 0, 0);
		$white = imagecolorallocate($image, 255, 255, 255);
		$red = imagecolorallocatealpha($image, 255, 0, 0, 75);
		$green = imagecolorallocatealpha($image, 0, 255, 0, 75);
		$blue = imagecolorallocatealpha($image, 0, 0, 255, 75);

		imagefilledrectangle($image, 0, 0, $width, $height, $white);
		imagefilledellipse($image, ceil(rand(5, 145)), ceil(rand(0, 35)), 30, 30, $red);
		imagefilledellipse($image, ceil(rand(5, 145)), ceil(rand(0, 35)), 30, 30, $green);
		imagefilledellipse($image, ceil(rand(5, 145)), ceil(rand(0, 35)), 30, 30, $blue);
		imagefilledrectangle($image, 0, 0, $width, 0, $black);
		imagefilledrectangle($image, $width - 1, 0, $width - 1, $height - 1, $black);
		imagefilledrectangle($image, 0, 0, 0, $height - 1, $black);
		imagefilledrectangle($image, 0, $height - 1, $width, $height - 1, $black);

		imagestring($image, 10, intval(($width - (strlen($this->session->data['NWA_captcha']) * 9)) / 2), intval(($height - 15) / 2), $this->session->data['NWA_captcha'], $black);

		header('Content-type: image/jpeg');

		imagejpeg($image);

		imagedestroy($image);
		exit;
	}

}
