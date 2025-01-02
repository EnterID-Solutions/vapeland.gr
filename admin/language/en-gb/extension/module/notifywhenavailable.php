<?php
// Heading
$_['heading_title']       				= 'NotifyWhenAvailable';


// Text
$_['text_module']        				= 'Modules';
$_['text_nwa_success']       			= 'Success: You have modified module NotifyWhenAvailable!';
$_['text_success_activation']  			= 'ACTIVATED: You have successfully activated NotifyWhenAvailable!';
$_['text_enabled']  					= 'Enabled';
$_['text_disabled']   					= 'Disabled';
$_['text_add']   						= 'Add';

$_['text_customer_email']       		= 'Customer Email';
$_['text_customer_name']       			= 'Customer Name';
$_['text_customer_comment']             = "Customer Comment";
$_['text_product']   					= 'Product';
$_['text_date']  						= 'Date';
$_['text_language']   					= 'Language';
$_['text_actions']   					= 'Actions';
$_['text_remove']   					= 'Remove';
$_['text_remove_all']  					= 'Remove all';
$_['text_status']       				= 'Module status:';
$_['text_status_help']       			= 'Enable or disable NotifyWhenAvailable.';
$_['text_privacy_policy']     = "Privacy Policy";
$_['text_privacy_policy_help']     = "Use this feature if you want your customers to accept the Privacy Policy. Compatible with GDPR Compliance.";
$_['text_agree']     = "Agree";
$_['text_scheduled']   					= 'Scheduled tasks:';
$_['text_scheduled_help']  				= 'When activated, this function will send automatically emails to the customers who are waiting for products that now are in stock.';
$_['text_scheduled_help_sec']  			= "When you change a given product's quantity from the <strong>Products -> Edit page</strong>, <strong>NotifyWhenAvailable</strong> automatically checks if there are customers that are waiting for it. However, if you use another software to edit the products quantities, such as quick edit modules or inventory system, the module will not be triggered. In such cases, you can use the scheduling tasks in <strong>NotifyWhenAvailable</strong> which will help you alert the customers when the product that they are waiting for is back in stock.";
$_['text_receive_notifications']		= 'Receive notification when the task is executed:';
$_['text_receive_notifications_help']   = 'An Email will be sent to the store owner when a sheduled task is finished.';
$_['text_type']  						= 'Schedule type:';
$_['text_type_help']       				= 'Choose the type of the schedule.';
$_['text_settings_help']       			= 'Choose the type of the schedule.';
$_['text_schedule']   					= 'Schedule:';
$_['text_schedule_help']  				= 'Enter the desired schedule for the reminders. You will see the options for the fixed dates functionality or the periodic functionality depending on the option above.';
$_['text_fixed']       					= 'Fixed dates';
$_['text_periodic']   					= 'Periodic';
$_['text_test_cron']   					= 'Test Cron';
$_['text_test_cron_help']   			= 'Click here to check if your server supports Cron commands.';
$_['text_test_cron_help_sec']  			= 'If you want to use the scheduling features, your server has to support <strong>Cron</strong> functions.<br /><br />The cron daemon is a long running process that executes commands at specific dates and times. By clicking on the button <strong>Test Cron</strong> you can check if your server supports <strong>Cron</strong> commands. If your server <strong>does</strong> support Cron jobs, but this script shows that the feature is disabled, this means that the automatic creation of Cron commands is disabled. In that case, you can use this URL string - <strong>{path_to_your_site}vendors/notifywhenavailable/sendMails.php 0 http</strong> - in your hosting config panel.';
$_['text_test_cron_help_third']   		= '* After sendMails.php you have to write your store ID and either http or https depending on whether your store front is accessed through HTTPS or not. Your default store is with ID <strong>0</strong>.';
$_['text_cron_disabled']   				= 'My server does not support cron jobs';
$_['text_close']  						= 'Close';
$_['text_schedule_cron']   				= 'Schedule options & cron jobs';
$_['text_alternative_cron']  			= 'Alternative cron solutions';
$_['text_alternative_cron_help'] 		= 'If your server does not support cron jobs, you can try using services such as <strong>easycron.com</strong>, <strong>setcronjob.com</strong> or others which can provide you this feature.<br /><br />
        In order to do that, you have to register in the selected service and use this URL for execution:';
$_['text_alternative_cron_help_two']    = '* After store_id you have to write your store ID. Your default store is with ID <strong>0</strong>.</span><br /><br />
        You should also enable the <strong>Scheduled tasks</strong> feature in module settings.';


$_['text_stock_status']       			= 'Enable for the following Out of Stock Statuses:';
$_['text_stock_status_help']       		= 'Enable the module only for products with quantity <=0 and Out-of-Stock status among the selected ones.';
$_['text_admin_notifications']   		= 'Admin notification by Email:';
$_['text_admin_notifications_help']  	= 'You will receive an email when someone wants to be notified for out of stock product.';
$_['text_button_options']         = 'Replace "Add to cart"';
$_['text_button_options_help']   = 'Choose whether to replace the "Add to cart" button or add a separate "Notify me!" button';
$_['text_popup_width']   				= 'Popup Width:';
$_['text_popup_width_help']   	= 'In Pixels';
$_['text_design']   = 'Design:';
$_['text_design_help']  				= 'Use these codes:<br /><br />
  {name_field} - Name<br />

  {email_field} - Email <br />

  {comment_field} - Comment<br />

  {captcha} - Captcha<br />

  {submit_button} - Submit<br></br>
  Note: only comment field is optional.';
$_['text_button_label']       			= 'Button label:';
$_['text_button_label_help']       		= "Enter the label for the 'Add to Cart' button of out of stock products";
$_['text_popup_title']   				= 'Popup Title:';
$_['text_popup_title_help']  			= 'You will receive an email when someone wants to be notified for out of stock product.';
$_['text_notification_customer']   		= 'Send notification to customer:';
$_['text_notification_customer_help']  	= 'Send email to customers confirming that they will be notified once the product is back in stock.';
$_['text_notification_mail']   			= 'Notification Email Text:';
$_['text_notification_mail_help']  		= 'Use these codes:<br /><br />
    {name} - Customer Name<br />
    {product_name} - Product Name<br />
    {product_model} - Product Model<br />';
$_['text_notification_subject']       	= 'Notification Email Subject:';
$_['text_email_text']  					= 'Email Text:';
$_['text_email_text_help']  			= 'Email that will be sent once the product is back in stock:';
$_['text_email_text_help_sec']  		= 'Use these codes:<br />

    {c_name} - Customer Name<br />

    {p_name} - Product Name<br />

    {p_model} - Product Model<br />

    {p_image} - Product Image<br />

    {p_link} - Product Link';

$_['text_email_subject']       			= 'Email Subject:';
$_['text_custom_css']       			= 'Custom CSS:';
$_['text_custom_css_help']   			= 'Using this field, you can set custom styles for the popup.';
$_['text_export_csv']   				= 'Export to CSV';


$_['preorder_enabled']					= 'Please, note that you have also enabled <strong>PreOrder</strong>. The PreOrder module is enabled only for products with the selected in the PreOrder Control Panel tab Out-of-Stock statuses. The NotifyWhenAvailable module is enabled for all of the rest Out-of-Stock products.';

$_['text_most_wanted_ofs']  = "Most Wanted (Out of stock)";
$_['text_most_wanted_all_time']  = "Most Wanted (All time)";
$_['text_statistic_option_note']  = "Note: The number of requested options may be lower or higher than the number of interested customers, since each customer can select none or more than one option value.";

$_['text_count']              = "Count";
$_['text_captcha']				= "Captcha";
$_['text_captcha_help']			= "Captcha To use on the notify form.<br><br><b>Note: </b>Do not forget to add the captcha design code in the Design section below";
$_['text_replace_cart']			= "Replace Add to cart";
$_['text_new_button']			= "Add a new button";
