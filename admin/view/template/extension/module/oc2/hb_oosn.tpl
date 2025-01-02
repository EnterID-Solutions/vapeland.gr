<?php echo $header; ?><?php echo $column_left; ?>

<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">

        <a class="btn btn-success" onclick="manualrun();"><i class="fa fa-envelope"></i> Check & Notify Customers</a>
        <button type="submit" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i> <?php echo $button_save; ?></button>
		<div class="btn-group">
		  <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown">
			<i class="fa fa-check-square-o"></i> Selective Operation <span class="caret"></span>
		  </button>
		  <ul class="dropdown-menu dropdown-menu-right" role="menu">
		  	<li><a id="reset-selected"><i class="fa fa-undo"></i> Reset Selected</a></li>
			<li><a id="delete-selected"><i class="fa fa-trash-o"></i> Delete Selected</a></li>
			<li><a onclick="emailSelected();"><i class="fa fa-envelope"></i> Notify Selected (Overrides Stock Check)</a></li>
		  </ul>
		</div>
		
		<div class="btn-group">
		  <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
			<i class="fa fa-support"></i> Help <span class="caret"></span>
		  </button>
		  <ul class="dropdown-menu dropdown-menu-right" role="menu">
			<li><a href="https://www.huntbee.com/documentation/docs/product-stock-notification/" target="_blank"><i class="fa fa-book"></i> Documentation</a></li>
			<li><a href="https://www.huntbee.com/get-support"  target="_blank"><i class="fa fa-ticket"></i> Get Support</a></li>
		  </ul>
		</div>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <?php if ($success) { ?>
    <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo $success; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $heading_title; ?></h3>
      </div>
      <div class="panel-body"> <br>
        <div id="msgoutput" style="text-align:left;"></div>
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-alerts" class="form-horizontal">
          <ul class="nav nav-tabs">
            <li class="active"><a href="#tab-records" data-toggle="tab"><i class="fa fa-folder-open-o"></i> <?php echo $tab_records; ?> </a></li>
            <li><a href="#tab-stat" data-toggle="tab"><i class="fa fa-pie-chart" aria-hidden="true"></i> <?php echo $tab_statistics; ?></a></li>
            <li><a href="#tab-email" data-toggle="tab"><i class="fa fa-envelope"></i> <?php echo $tab_email; ?></a></li>
            <li><a href="#tab-setting" data-toggle="tab"><i class="fa fa-gears"></i> <?php echo $tab_setting; ?></a></li>
            <li><a href="#tab-language" data-toggle="tab"><i class="fa fa-language"></i> <?php echo $tab_language; ?></a></li>
            <li><a href="#tab-tool" data-toggle="tab"><i class="fa fa-wrench" aria-hidden="true"></i> <?php echo $tab_tools; ?></a></li>
            <li><a href="#tab-log" data-toggle="tab"><i class="fa fa-calendar-plus-o" aria-hidden="true"></i> <?php echo $tab_log; ?></a></li>
			<li><a href="#tab-license" data-toggle="tab"><i class="fa fa-user" aria-hidden="true"></i> LICENSE</a></li>
          </ul>
          <div class="tab-content">
            <div class="tab-pane active" id="tab-records">				
				<div class="col-sm-2">
					<input type="checkbox" value="1" data-toggle="toggle" data-onstyle="default" data-on="Compact View" data-off="Detailed View" id="compact" onchange="showfields();" class="form-control" />
				</div>
							
				<div class="col-sm-2">
					<select class="form-control" id="notified-type" onchange="searchItem();">
						<option value="all"><?php echo $text_show_all_reports;?></option>
						<option value="awaiting"><?php echo $text_show_awaiting_reports;?></option>
						<option value="notified"><?php echo $text_show_archive_reports;?></option>
					</select>
				</div>
				
				<div class="col-sm-8">
					<div class="input-group" style="margin-bottom:10px;">
						<input type="text" id="search-value" onkeyup="searchItem();" class="form-control" placeholder="Search by product name/id OR customer name/email address">
						<span class="input-group-addon btn" id="search-button" onclick="searchItem();"><i class="fa fa-search"></i></span>
					</div>
				</div>
				
				<div class="col-sm-12">
			 		<div id="list-block"></div>
				</div>
            </div>
            <!-- tab 1 end -->
			<!--TAB:STAT - START-->
            <div class="tab-pane" id="tab-stat">
				<div class="col-sm-12">
					<div class="col-sm-8"><h3>Products in Demand</h3></div>
					<div class="col-sm-4"><a onclick="chartPID();" class="btn btn-primary pull-right">View Chart</a></div>
					<div class="col-sm-12" style="margin-top:10px;">
						<div class="table-responsive">
							<table class="table table-bordered">
							  <thead>
								<tr>
								  <td class="text-left">Product ID</td>
								  <td class="text-left">Name</td>
								  <td class="text-right">Quantity</td>
								</tr>
							  </thead>
							  <tbody>
								<?php if ($demands) { ?>
								<?php foreach ($demands as $demand) { ?>
								<tr>
								  <td class="text-left"><?php echo $demand['pid']; ?></td>
								  <td class="text-left"><?php echo $demand['name']; ?></td>
								  <td class="text-right"><?php echo $demand['count']; ?></td>
								</tr>
								<?php } ?>
								<?php }else { ?>
								<tr>
								  <td class="center" colspan="3"><?php echo $text_no_results; ?></td>
								</tr>
								<?php } ?>
							  </tbody>
							</table>
						  </div>
						</div>
					</div>

					<div class="col-sm-12">
						<div class="col-sm-8"><h3>Records Statistics</h3></div>
						<div class="col-sm-4"><a onclick="chartRecordsStat();" class="btn btn-primary pull-right">View Chart</a></div>
						<div class="col-sm-12" style="margin-top:10px;">
							<table class="table table-bordered table-hover">
							  <tr>
								<td class="col-sm-4"><?php echo $text_total_alert; ?></td>
								<td><?php echo $total_alert;?></td>
							  </tr>
							  <tr>
								<td><?php echo $text_total_responded; ?></td>
								<td><?php echo $total_responded;?></td>
							  </tr>
							  <tr>
								<td><?php echo $text_customers_awaiting_notification; ?></td>
								<td><?php echo $awaiting_notification;?></td>
							  </tr>
							  <tr>
								<td><?php echo $text_number_of_products_demanded; ?></td>
								<td><?php echo $product_requested;?></td>
							  </tr>
							  <tr>
								<td><?php echo $text_archive_records;?></td>
								<td><?php echo $total_responded;?></td>
							  </tr>
							  <tr>
								<td><?php echo $text_customers_notified; ?></td>
								<td><?php echo $customer_notified;?></td>
							  </tr>
							</table>
						</div>
					</div>

					<div class="col-sm-12">
						<div class="col-sm-8"><h3>Subscription Trend</h3></div>
						<div class="col-sm-4"><a onclick="chartTrend();" class="btn btn-warning pull-right col-sm-6">Open Chart</a></div>
					</div>
			  
            </div><!--TAB:STAT - END-->
			
            <div class="tab-pane" id="tab-email">
              <ul class="nav nav-tabs" id="languages">
                <?php foreach ($languages as $language) { ?>
                <li><a href="#languages<?php echo $language['language_id']; ?>" data-toggle="tab"><?php echo $language['name']; ?></a></li>
                <?php } ?>
              </ul>
              <div class="tab-content">
                <!-- language tab content -->
                <?php foreach ($languages as $language) { ?>
                <div class="tab-pane" id="languages<?php echo $language['language_id']; ?>"> <span class="sub-heading"><?php echo $text_header_customer; ?></span>
                  <div class="form-group required">
                    <label class="col-sm-4 control-label"><?php echo $entry_subject; ?></label>
                    <div class="col-sm-8">
                      <input type="text" class="form-control" name="hb_oosn_customer_email_subject_<?php echo $language['language_id']; ?>" value="<?php echo $hb_oosn_customer_email_subject[$language['language_id']] ;?>" />
                    </div>
                  </div>
                  <div class="form-group required">
                    <div class="col-sm-4">
                      <table class="table table-bordered table-hover">
                        <tr>
                          <td>Customer Name</td>
                          <td>{customer_name}</td>
                        </tr>
                        <tr>
                          <td>Product Name</td>
                          <td>{product_name}</td>
                        </tr>
                        <tr>
                          <td>Customer Selected Options</td>
                          <td>{all_option}</td>
                        </tr>
                        <tr>
                          <td>Customer Selected Product Options that are out-of-stock</td>
                          <td>{option}</td>
                        </tr>
                        <tr>
                          <td>Model</td>
                          <td>{model}</td>
                        </tr>
                        <tr>
                          <td>Product Image URL</td>
                          <td>{image_url}</td>
                        </tr>
                        <tr>
                          <td>Product Image</td>
                          <td>{show_image}</td>
                        </tr>
                        <tr>
                          <td>Product Link</td>
                          <td>{link}</td>
                        </tr>
                        <tr>
                          <td>Store Name</td>
                          <td>{store_name}</td>
                        </tr>
                        <tr>
                          <td>Store URL</td>
                          <td>{store_url}</td>
                        </tr>
                      </table>
                    </div>
                    <div class="col-sm-8">
                      <label><?php echo $entry_body; ?></label>
                      <textarea name="hb_oosn_customer_email_body_<?php echo $language['language_id']; ?>" id="email-n<?php echo $language['language_id']; ?>"><?php echo $hb_oosn_customer_email_body[$language['language_id']] ;?></textarea>
                    </div>
                  </div>
                  <hr>
				  <!--CONFIRMATION EMAIL-->
				  <span class="sub-heading">CUSTOMER CONFIRMATION EMAIL</span>
                  <div class="form-group required">
                    <label class="col-sm-4 control-label"><?php echo $entry_subject; ?></label>
                    <div class="col-sm-8">
                      <input type="text" class="form-control" name="hb_oosn_confirm_email_subject_<?php echo $language['language_id']; ?>" value="<?php echo $hb_oosn_confirm_email_subject[$language['language_id']] ;?>" />
                    </div>
                  </div>
                  <div class="form-group required">
                    <div class="col-sm-4">
                      <table class="table table-bordered table-hover">
                        <tr>
                          <td>Customer Name</td>
                          <td>{customer_name}</td>
                        </tr>
                        <tr>
                          <td>Product Name</td>
                          <td>{product_name}</td>
                        </tr>
                        <tr>
                          <td>Customer Selected Options</td>
                          <td>{all_option}</td>
                        </tr>
                        <tr>
                          <td>Customer Selected Product Options that are out-of-stock</td>
                          <td>{option}</td>
                        </tr>
                        <tr>
                          <td>Model</td>
                          <td>{model}</td>
                        </tr>
                        <tr>
                          <td>Product Image URL</td>
                          <td>{image_url}</td>
                        </tr>
                        <tr>
                          <td>Product Image</td>
                          <td>{show_image}</td>
                        </tr>
                        <tr>
                          <td>Product Link</td>
                          <td>{link}</td>
                        </tr>
                        <tr>
                          <td>Store Name</td>
                          <td>{store_name}</td>
                        </tr>
                        <tr>
                          <td>Store URL</td>
                          <td>{store_url}</td>
                        </tr>
                      </table>
                    </div>
                    <div class="col-sm-8">
                      <label><?php echo $entry_body; ?></label>
                      <textarea name="hb_oosn_confirm_email_body_<?php echo $language['language_id']; ?>" id="email-c<?php echo $language['language_id']; ?>"><?php echo $hb_oosn_confirm_email_body[$language['language_id']] ;?></textarea>
                    </div>
                  </div>
				  <hr />
                  <span class="sub-heading"><?php echo $text_header_sms; ?></span>
                  <div class="form-group required">
                    <div class="col-sm-4">
                      <table class="table table-bordered table-hover">
                        <tr>
                          <td>Product Name</td>
                          <td>{product_name}</td>
                        </tr>
                        <tr>
                          <td>Customer Selected Options</td>
                          <td>{all_option}</td>
                        </tr>
                        <tr>
                          <td>Customer Selected Product Options that are out-of-stock</td>
                          <td>{option}</td>
                        </tr>
                        <tr>
                          <td>Model</td>
                          <td>{model}</td>
                        </tr>
                        <tr>
                          <td>Product Link</td>
                          <td>{link}</td>
                        </tr>
                      </table>
                    </div>
                    <div class="col-sm-8">
                      <label><?php echo $sms_body; ?></label>
                      <textarea name="hb_oosn_customer_sms_body_<?php echo $language['language_id']; ?>" class="form-control" rows="7"><?php echo $hb_oosn_customer_sms_body[$language['language_id']] ;?></textarea>
                    </div>
                  </div>
                </div>
                <?php } ?>
              </div>
              <hr />
              <div class="form-group required">
                <label class="col-sm-4 control-label"><?php echo $text_product_image_size; ?></label>
                <div class="col-sm-4">
                  <input type="text" class="form-control" name="hb_oosn_product_image_h" placeholder="Enter Height" value="<?php echo $hb_oosn_product_image_h;?>" />
                </div>
                <div class="col-sm-4">
                  <input type="text" class="form-control" name="hb_oosn_product_image_w" placeholder="Enter Width" value="<?php echo $hb_oosn_product_image_w;?>" />
                </div>
              </div>
              <div class="form-group required">
                <label class="col-sm-4 control-label"><?php echo $text_admin_notify; ?></label>
                <div class="col-sm-4">
                  <select name="hb_oosn_admin_notify"  class="form-control">
                    <option value="y" <?php echo ($hb_oosn_admin_notify == 'y')? 'selected':''; ?> >Yes</option>
                    <option value="n" <?php echo ($hb_oosn_admin_notify == 'n')? 'selected':''; ?> >No</option>
                  </select>
                </div>
              </div>
              <div class="form-group required">
                <label class="col-sm-4 control-label"><?php echo $text_admin_email; ?></label>
                <div class="col-sm-4">
                  <input type="text" class="form-control" name="hb_oosn_admin_email" placeholder="Enter admin email address" value="<?php echo $hb_oosn_admin_email;?>" />
                </div>
              </div>
            </div>
            <div class="tab-pane" id="tab-language">
              <ul class="nav nav-tabs" id="t-languages">
                <?php foreach ($languages as $language) { ?>
                <li><a href="#t-languages<?php echo $language['language_id']; ?>" data-toggle="tab"><?php echo $language['name']; ?></a></li>
                <?php } ?>
              </ul>
              <div class="tab-content">
                <!-- language tab content -->
                <?php foreach ($languages as $language) { ?>
                <div class="tab-pane" id="t-languages<?php echo $language['language_id']; ?>">
                  <div class="form-group">
                    <label class="col-sm-3 control-label">Notify Button on Product Page</label>
                    <div class="col-sm-9">
                      <input type="text" class="form-control" name="hb_oosn_notifybtn_p<?php echo $language['language_id']; ?>" value="<?php echo $hb_oosn_notifybtn_p[$language['language_id']];?>" />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-3 control-label">Notify Button on Popup form</label>
                    <div class="col-sm-9">
                      <input type="text" class="form-control" name="hb_oosn_notifybtn_f<?php echo $language['language_id']; ?>" value="<?php echo $hb_oosn_notifybtn_f[$language['language_id']];?>" />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-3 control-label">Notify Button on other pages</label>
                    <div class="col-sm-9">
                      <input type="text" class="form-control" name="hb_oosn_notifybtn_o<?php echo $language['language_id']; ?>" value="<?php echo $hb_oosn_notifybtn_o[$language['language_id']];?>" />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-3 control-label">Information Text</label>
                    <div class="col-sm-9">
                      <textarea class="form-control" name="hb_oosn_t_info<?php echo $language['language_id']; ?>" id="info<?php echo $language['language_id']; ?>" ><?php echo $hb_oosn_t_info[$language['language_id']] ;?></textarea>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-3 control-label">Information Text for Option</label>
                    <div class="col-sm-9">
                      <textarea class="form-control" name="hb_oosn_t_info_opt<?php echo $language['language_id']; ?>" id="info-opt<?php echo $language['language_id']; ?>" ><?php echo $hb_oosn_t_info_opt[$language['language_id']] ;?> </textarea>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-3 control-label">Success Text</label>
                    <div class="col-sm-9">
                      <input type="text" class="form-control" name="hb_oosn_t_success<?php echo $language['language_id']; ?>" value="<?php echo $hb_oosn_t_success[$language['language_id']] ;?>" />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-3 control-label">Name Label</label>
                    <div class="col-sm-9">
                      <input type="text" class="form-control" name="hb_oosn_t_name<?php echo $language['language_id']; ?>" value="<?php echo $hb_oosn_t_name[$language['language_id']];?>" />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-3 control-label">Email Label</label>
                    <div class="col-sm-9">
                      <input type="text" class="form-control" name="hb_oosn_t_email<?php echo $language['language_id']; ?>" value="<?php echo $hb_oosn_t_email[$language['language_id']];?>" />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-3 control-label">Phone Label</label>
                    <div class="col-sm-9">
                      <input type="text" class="form-control" name="hb_oosn_t_phone<?php echo $language['language_id']; ?>" value="<?php echo $hb_oosn_t_phone[$language['language_id']];?>" />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-3 control-label">Comment Label</label>
                    <div class="col-sm-9">
                      <input type="text" class="form-control" name="hb_oosn_t_comment<?php echo $language['language_id']; ?>" value="<?php echo $hb_oosn_t_comment[$language['language_id']];?>" />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-3 control-label">Name Placeholder</label>
                    <div class="col-sm-9">
                      <input type="text" class="form-control" name="hb_oosn_t_name_ph<?php echo $language['language_id']; ?>" value="<?php echo $hb_oosn_t_name_ph[$language['language_id']];?>" />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-3 control-label">Email Placeholder</label>
                    <div class="col-sm-9">
                      <input type="text" class="form-control" name="hb_oosn_t_email_ph<?php echo $language['language_id']; ?>" value="<?php echo $hb_oosn_t_email_ph[$language['language_id']];?>" />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-3 control-label">Phone Placeholder</label>
                    <div class="col-sm-9">
                      <input type="text" class="form-control" name="hb_oosn_t_phone_ph<?php echo $language['language_id']; ?>" value="<?php echo $hb_oosn_t_phone_ph[$language['language_id']];?>" />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-3 control-label">Comments Placeholder</label>
                    <div class="col-sm-9">
                      <input type="text" class="form-control" name="hb_oosn_t_comment_ph<?php echo $language['language_id']; ?>" value="<?php echo $hb_oosn_t_comment_ph[$language['language_id']];?>" />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-3 control-label">Name Error</label>
                    <div class="col-sm-9">
                      <input type="text" class="form-control" name="hb_oosn_t_error_email<?php echo $language['language_id']; ?>" value="<?php echo $hb_oosn_t_error_email[$language['language_id']];?>" />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-3 control-label">Email Error</label>
                    <div class="col-sm-9">
                      <input type="text" class="form-control" name="hb_oosn_t_error_name<?php echo $language['language_id']; ?>" value="<?php echo $hb_oosn_t_error_name[$language['language_id']];?>" />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-3 control-label">Phone Error</label>
                    <div class="col-sm-9">
                      <input type="text" class="form-control" name="hb_oosn_t_error_phone<?php echo $language['language_id']; ?>" value="<?php echo $hb_oosn_t_error_phone[$language['language_id']];?>" />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-3 control-label">Comments Error</label>
                    <div class="col-sm-9">
                      <input type="text" class="form-control" name="hb_oosn_t_error_comment<?php echo $language['language_id']; ?>" value="<?php echo $hb_oosn_t_error_comment[$language['language_id']];?>" />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-3 control-label">Captcha Select</label>
                    <div class="col-sm-9">
                      <input type="text" class="form-control" name="hb_oosn_t_warn_captcha<?php echo $language['language_id']; ?>" value="<?php echo $hb_oosn_t_warn_captcha[$language['language_id']];?>" />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-3 control-label">Captcha Error</label>
                    <div class="col-sm-9">
                      <input type="text" class="form-control" name="hb_oosn_t_error_captcha<?php echo $language['language_id']; ?>" value="<?php echo $hb_oosn_t_error_captcha[$language['language_id']];?>" />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-3 control-label">Duplicate Alert Entry</label>
                    <div class="col-sm-9">
                      <input type="text" class="form-control" name="hb_oosn_t_duplicate<?php echo $language['language_id']; ?>" value="<?php echo $hb_oosn_t_duplicate[$language['language_id']];?>" />
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-3 control-label">Invalid Product</label>
                    <div class="col-sm-9">
                      <input type="text" class="form-control" name="hb_oosn_t_invalid<?php echo $language['language_id']; ?>" value="<?php echo $hb_oosn_t_invalid[$language['language_id']];?>" />
                    </div>
                  </div>
                </div>
                <?php } ?>
              </div>
            </div>
            <div class="tab-pane" id="tab-setting"> <span class="sub-heading"><?php echo $text_header_form; ?></span>
              <table class="table table-hover">
                <tr>
                  <td class="col-sm-4"><strong><?php echo $entry_enable_name; ?></strong></td>
                  <td><select name="hb_oosn_name_enable"  class="form-control">
                      <option value="y" <?php echo ($hb_oosn_name_enable == 'y')? 'selected':''; ?> >Yes</option>
                      <option value="n" <?php echo ($hb_oosn_name_enable == 'n')? 'selected':''; ?> >No</option>
                    </select></td>
                </tr>
                <tr>
                  <td><strong><?php echo $entry_enable_mobile; ?></strong></td>
                  <td><select name="hb_oosn_mobile_enable"  class="form-control">
                      <option value="y" <?php echo ($hb_oosn_mobile_enable == 'y')? 'selected':''; ?> >Yes</option>
                      <option value="n" <?php echo ($hb_oosn_mobile_enable == 'n')? 'selected':''; ?> >No</option>
                    </select></td>
                </tr>
                <tr>
                  <td><strong><?php echo $entry_enable_comments; ?></strong></td>
                  <td><select name="hb_oosn_comments_enable"  class="form-control">
                      <option value="n" <?php echo ($hb_oosn_comments_enable == 'n')? 'selected':''; ?> >No</option>
                      <option value="y" <?php echo ($hb_oosn_comments_enable == 'y')? 'selected':''; ?> >Yes</option>
                    </select></td>
                </tr>
                <tr>
                  <td><strong><?php echo $entry_enable_sms; ?></strong></td>
                  <td><select name="hb_oosn_sms_enable"  class="form-control">
                      <option value="n" <?php echo ($hb_oosn_sms_enable == 'n')? 'selected':''; ?> >No</option>
                      <option value="y" <?php echo ($hb_oosn_sms_enable == 'y')? 'selected':''; ?> >Yes</option>
                    </select></td>
                </tr>
				<tr>
                  <td><strong>SMS HTTP API (if you have one already, else leave empty)</strong></td>
                  <td>
				  	<input name="hb_oosn_sms_http_api"  type="text" class="form-control" placeholder="" value="<?php echo $hb_oosn_sms_http_api;?>">
				  	<p><i>Replace mobile number variable of API with <strong>{to}</strong> and message variable with <strong>{sms}</strong>. Contact support if you need help with SMS API Integration</i></p>
				  </td>
                </tr>
                <tr>
                  <td><strong><?php echo $text_mobile_validation; ?></strong></td>
                  <td><div class="input-group col-sm-6">
                      <div class="input-group-addon"> > </div>
                      <input name="hb_oosn_mobile_validation_min"  type="text" class="form-control" placeholder="" value="<?php echo $hb_oosn_mobile_validation_min;?>">
                      <div class="input-group-addon">digits</div>
                    </div>
                    <div class="input-group col-sm-6">
                      <div class="input-group-addon"> < </div>
                      <input name="hb_oosn_mobile_validation_max"  type="text" class="form-control" placeholder="" value="<?php echo $hb_oosn_mobile_validation_max;?>">
                      <div class="input-group-addon">digits</div>
                    </div></td>
                </tr>
                <tr>
                  <td><strong><?php echo $entry_animation; ?></strong></td>
                  <td><select name="hb_oosn_animation"  class="form-control">
                      <option value="mfp-zoom-in" <?php echo ($hb_oosn_animation == 'mfp-zoom-in')? 'selected':''; ?> >Zoom</option>
                      <option value="mfp-newspaper" <?php echo ($hb_oosn_animation == 'mfp-newspaper')? 'selected':''; ?> >Newspaper</option>
                      <option value="mfp-move-horizontal" <?php echo ($hb_oosn_animation == 'mfp-move-horizontal')? 'selected':''; ?>>Horizontal Move</option>
                      <option value="mfp-move-from-top" <?php echo ($hb_oosn_animation == 'mfp-move-from-top')? 'selected':''; ?>>Move from top</option>
                      <option value="mfp-3d-unfold" <?php echo ($hb_oosn_animation == 'mfp-3d-unfold')? 'selected':''; ?>>3D unfold</option>
                      <option value="mfp-zoom-out" <?php echo ($hb_oosn_animation == 'mfp-zoom-out')? 'selected':''; ?>>Zoom-out</option>
                    </select></td>
                </tr>
                <tr>
                  <td><strong><?php echo $entry_css; ?></strong></td>
                  <td><textarea name="hb_oosn_css"  class="form-control" rows="10" cols="40" style="width: 90%"><?php echo $hb_oosn_css;?></textarea></td>
                </tr>
                <tr>
                  <td><strong><?php echo $entry_enable_captcha; ?></strong> <br />
                    <small style="color:#FF0000;"><?php echo ($google_captcha_status != "1")? 'Google reCAPTCHA is not enabled!':''; ?></small></td>
                  <td><input type="checkbox" data-toggle="toggle" data-onstyle="success" name="hb_oosn_enable_captcha" class="form-control"  value="1" <?php echo ($hb_oosn_enable_captcha == "1")? 'checked':''; ?> <?php echo ($google_captcha_status == "1")? '':'disabled'; ?> /></td>
                </tr>
                <tr>
                  <td><strong><?php echo $entry_enable_logs; ?></strong> </td>
                  <td><input type="checkbox" data-toggle="toggle" data-onstyle="success" name="hb_oosn_logs" class="form-control"  value="1" <?php echo ($hb_oosn_logs == "1")? 'checked':''; ?> /></td>
                </tr>
                <tr>
                  <td><strong><?php echo $entry_include_magnific; ?></strong> </td>
                  <td><input type="checkbox" data-toggle="toggle" data-onstyle="success" name="hb_oosn_incl_magnific" class="form-control"  value="1" <?php echo ($hb_oosn_incl_magnific == "1")? 'checked':''; ?> /></td>
                </tr>
				<tr>
                  <td><strong>Enable Confirmation Email</strong> </td>
                  <td><input type="checkbox" data-toggle="toggle" data-onstyle="success" name="hb_oosn_confirm_email_enable" class="form-control"  value="1" <?php echo ($hb_oosn_confirm_email_enable == "1")? 'checked':''; ?> /></td>
                </tr>
              </table>
              <span class="sub-heading"><?php echo $text_header_condition; ?></span>
              <table class="table table-hover">
                <tr>
                  <td class="col-sm-4"><span class="required">*</span> <strong><?php echo $text_product_qty; ?></strong></td>
                  <td><input name="hb_oosn_product_qty"  class="form-control" type="text" value="<?php echo $hb_oosn_product_qty;?>"></td>
                </tr>
                <tr>
                  <td><strong><?php echo $text_product_stock_status; ?></strong></td>
                  <td><select name="hb_oosn_stock_status"  class="form-control">
                      <option value="0" <?php echo ($hb_oosn_stock_status ==  '0')? 'selected':''; ?> >DISABLE THIS CONDITION CHECK</option>
                      <?php foreach ($stock_statuses as $stock_status) { ?>
                      <option value="<?php echo $stock_status['stock_status_id']; ?>" <?php echo ($hb_oosn_stock_status ==  $stock_status['stock_status_id'])? 'selected':''; ?> ><?php echo $stock_status['name']; ?></option>
                      <?php }?>
                    </select>
                  </td>
                </tr>
              </table>
              <span class="sub-heading">Other Settings</span>
              <div class="form-group">
                <label class="col-sm-4 control-label"><?php echo $text_campaign_source; ?></label>
                <div class="col-sm-8">
                  <input name="hb_oosn_campaign"  class="form-control" type="text" value="<?php echo $hb_oosn_campaign;?>">
                </div>
              </div>
			  <div class="form-group">
                <label class="col-sm-4 control-label">Order Status that defines order as completed</label>
                <div class="col-sm-8">
                  <select class="form-control" name="hb_oosn_orderstatus">
				  	<?php foreach ($order_statuses as $order_status) { ?>
						<option value="<?php echo $order_status['order_status_id']; ?>" <?php echo ($hb_oosn_orderstatus ==  $order_status['order_status_id'])? 'selected':''; ?>><?php echo $order_status['name']; ?></option>
					<?php } ?>
				  </select>
                </div>
              </div>
              <span class="sub-heading">Cron Job Setting</span>
              <div class="form-group">
                <label class="col-sm-4 control-label"><?php echo $text_authkey; ?></label>
                <div class="col-sm-8">
                  <input name="hb_oosn_authkey"  class="form-control" type="text" value="<?php echo $hb_oosn_authkey; ?>">
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-4 control-label">NOTIFY TO CUSTOMER EMAILS</label>
                <div class="col-sm-8">
				  <p><i>If you want to automate the customer notifications, you need to set up cron job to run at a frequency of once per day or multiple times per day</i></p>
                  <textarea rows="2" class="form-control" readonly><?php echo $cron_notify; ?></textarea>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-4 control-label">DEMANDED OUT-OF-STOCK PRODUCTS EMAIL TO ADMIN</label>
                <div class="col-sm-8">
					<p><i>If you want to recieve product-in-demand report to your admin email, you can set up cron job to run at a daily once or weekly once</i></p>
                  <textarea rows="2" class="form-control" readonly><?php echo $cron_demand; ?></textarea>
                </div>
              </div>
            </div>
            <div class="tab-pane" id="tab-tool">
              <table class="table table-bordered table-hover">
                <tr>
                  <td><strong><?php echo $button_export; ?></strong></td>
                  <td><a href="<?php echo $export;?>" class="btn btn-primary col-sm-4" target="_blank"><i class="fa fa-download"></i> <?php echo $button_export; ?></a></td>
                </tr>
                <tr>
                  <td class="col-sm-4"><strong><?php echo $text_reset_all; ?></strong></td>
                  <td><a class="btn btn-danger col-sm-4" onclick="confirm('Are you sure?') ? window.location.href ='<?php echo $delete_bulk;?>&record_type=all' : false;"><i class="fa fa-trash-o"></i> <?php echo $button_delete; ?></a></td>
                </tr>
                <tr>
                  <td><strong><?php echo $text_reset_awaiting; ?></strong></td>
                  <td><a class="btn btn-danger col-sm-4"  onclick="confirm('Are you sure?') ? window.location.href ='<?php echo $delete_bulk;?>&record_type=awaiting' : false;"><i class="fa fa-trash-o"></i> <?php echo $button_delete; ?></a></td>
                </tr>
                <tr>
                  <td><strong><?php echo $text_reset_archive; ?></strong></td>
                  <td><a class="btn btn-danger col-sm-4" onclick="confirm('Are you sure?') ? window.location.href ='<?php echo $delete_bulk;?>&record_type=archive' : false;"><i class="fa fa-trash-o"></i> <?php echo $button_delete; ?></a></td>
                </tr>
                <tr>
                  <td><strong>Fix Notified Dates that are set to 01 Jan 1970 1:00 AM / 0000-00-00 00:00:00</strong></td>
                  <td><a class="btn btn-warning col-sm-4" href ="<?php echo $fix_notified_dates;?>"><i class="fa fa-wrench"></i> Fix Notified Dates</a></td>
                </tr>
                <tr>
                  <td><strong>Check & fix database structure issues (in case you have upgraded this extension from 7.x.x)</strong></td>
                  <td><a class="btn btn-warning col-sm-4" href ="<?php echo $upgrade;?>"><i class="fa fa-wrench"></i> Check and Fix database structure</a></td>
                </tr>
				<tr>
                  <td><strong>Uninstall Extension</strong></td>
                  <td>
				  	<div class="pr_warning"><p>Because data is important, this extension main database table can only be deleted by clicking the below button. Clicking the uninstall button on modules page will not delete the database table of this extension.</p>
					<p>Once the below button is clicked, all the data stored will no more be available.</p></div>
				  	<a class="btn btn-danger col-sm-4" href ="<?php echo $uninstall;?>"><i class="fa fa-trash"></i> Uninstall Extension</a>
				  </td>
                </tr>
              </table>
            </div>
            <div class="tab-pane" id="tab-log">
              <div class="form-group">
                <div class="col-sm-4">
                  <select class="form-control" id="logfiles">
                    <?php foreach ($all_files as $logfile) { ?>
                    <option value="<?php echo $logfile; ?>" <?php echo ($filename == $logfile)? 'selected':'' ?>><?php echo $logfile; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <div id="logs-block"></div>
            </div>
			
			<div class="tab-pane" id="tab-license">
				<div class="form-group">
                <label class="col-sm-2 control-label">License Code</label>
                <div class="col-sm-6">
                  <input name="hb_oosn_license" id="hb_oosn_license" class="form-control" type="text" value="<?php echo $hb_oosn_license; ?>">
                </div>
				<div class="col-sm-2">
					<?php if ($fullpro_status == '-1') { ?>
						<a onclick="enableScript();" id="enable-script-btn" class="btn btn-warning">Enable Add-on Script</a>
					<?php } else if ($fullpro_status == 0) { ?>
						<div class="btn-group">
						  <button type="button" id="install-script-btn" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
							<i class="fa fa-cloud-download"></i> Install Add-on Script <span class="caret"></span>
						  </button>
						  <ul class="dropdown-menu dropdown-menu-right" role="menu">
							<li><a onclick="installScript('fullpro');"><i class="fa fa-angle-double-right"></i> Install FullPRO</a></li>
							<li><a onclick="installScript('forminline');"><i class="fa fa-angle-double-right"></i> Install Form-Inline</a></li>
						  </ul>
						</div>						
					<?php } else { ?>
						<a onclick="removeScript();" id="remove-script-btn" class="btn btn-danger"><i class="fa fa-trash"></i> Uninstall Add-on Script</a>
					<?php } ?>
				</div>
              </div>
			</div>
          </div>
        </form>
		
		<!--PRODUCT MODAL START-->
		<div class="modal fade" id="product-modal" tabindex="-1" role="dialog">
		  <div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Product Report</h4>
			  </div>
			  <div class="modal-body">
			  		<div id="product-block"></div>
			  </div>
			</div>
		  </div>
		</div>
		<!--PRODUCT MODAL END-->
		
		<!--CHART MODAL START-->
		<div class="modal fade" id="chart-modal" tabindex="-1" role="dialog">
		  <div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">GRAPHICAL REPRESENTATION</h4>
			  </div>
			  <div class="modal-body">
			  		<div id="chart-block"></div>
			  </div>
			</div>
		  </div>
		</div>
		<!--PRODUCT MODAL END-->
		
      </div>
    </div>
  </div>
  <div class="container-fluid">
    <!--Huntbee copyrights-->
    <center>
      <span class="help"><?php echo $heading_title; ?> <?php echo $extension_version; ?> &copy; <a href="https://www.huntbee.com/">HUNTBEE.COM</a> | <a href="https://www.huntbee.com/get-support">SUPPORT</a> | <a href="https://www.huntbee.com/documentation/docs/product-stock-notification/" target="_blank">DOCUMENTATION</a> </span>
    </center>
  </div>
  <!--Huntbee copyrights end-->
</div>

<style type="text/css">
.pr_error,.pr_info,.pr_infos,.pr_success,.pr_warning{margin:10px 0;padding:12px}.pr_info{color:#00529B;background-color:#BDE5F8}.pr_success{color:#4F8A10;background-color:#DFF2BF}.pr_warning{color:#9F6000;background-color:#FEEFB3}.pr_error{color:#D8000C;background-color:#FFBABA}.pr_error i,.pr_info i,.pr_success i,.pr_warning i{margin:10px 0;vertical-align:middle}

.sub-heading{
	font-size:16px;
	color:#0099CC;
	font-weight:bold;
	margin-bottom:10px;
	text-transform:uppercase;
	line-height:3;
}

a {cursor:pointer;}

</style>

<link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>

<script type="text/javascript" src="view/javascript/ckeditor/ckeditor.js"></script>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

<script type="text/javascript"><!--
	$('#languages a:first').tab('show');
	$('#t-languages a:first').tab('show');
//--></script>

<script type="text/javascript"><!--
<?php foreach ($languages as $language) { ?>
	CKEDITOR.replace("email-n<?php echo $language['language_id']; ?>", {
		filebrowserBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
		filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
		filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
		filebrowserUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
		filebrowserImageUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
		filebrowserFlashUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>'
	});
	
	CKEDITOR.replace("email-c<?php echo $language['language_id']; ?>", {
		filebrowserBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
		filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
		filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
		filebrowserUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
		filebrowserImageUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
		filebrowserFlashUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>'
	});
	
	CKEDITOR.replace("info-opt<?php echo $language['language_id']; ?>", {
		filebrowserBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
		filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
		filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
		filebrowserUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
		filebrowserImageUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
		filebrowserFlashUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
		height: '100px'
	});
	
	CKEDITOR.replace("info<?php echo $language['language_id']; ?>", {
		filebrowserBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
		filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
		filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
		filebrowserUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
		filebrowserImageUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
		filebrowserFlashUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
		height: '100px'
	});
<?php } ?>
//--></script>
<script type="text/javascript">
$(document).ready(function() {
	loadpages();
});

function showfields(){
	if ($('#compact').is(':checked')) {
        $('.col-compact').show();
		$('.col-detail').hide();
    }else{
		$('.col-compact').hide();
		$('.col-detail').show();
	}
}
</script>

<script type="text/javascript">
function loadpages(){
	$('#list-block').html('<center><i class="fa fa-refresh fa-spin fa-3x fa-fw"></i></center>');
	$('#list-block').load('index.php?route=<?php echo $base_route; ?>/hb_oosn/alertlist&token=<?php echo $token; ?>');
	$('#logs-block').html('<center><i class="fa fa-refresh fa-spin fa-3x fa-fw"></i></center>');
	$('#logs-block').load('index.php?route=<?php echo $base_route; ?>/hb_oosn/logs&token=<?php echo $token; ?>');
}

$('#list-block').delegate('.pagination a', 'click', function(e) {
	e.preventDefault();
	$('#list-block').load(this.href);
});
</script>

<script type="text/javascript">
function searchItem() {
	var searchvalue = $('#search-value').val();
	searchvalue = searchvalue.replace(/\s/g, '+');
	var notified = $('#notified-type').val();
	$('#list-block').html('<center><i class="fa fa-refresh fa-spin fa-3x fa-fw"></i></center>');
	$('#list-block').load('index.php?route=<?php echo $base_route; ?>/hb_oosn/alertlist&token=<?php echo $token; ?>&search='+searchvalue+'&notified='+notified);
}

function manualrun(){
	$('#msgoutput').html('<center><i class="fa fa-spinner fa-spin fa-3x fa-fw"></i></center>');
	$.ajax({
		url: '<?php echo $catalog; ?>index.php?route=extension/module/product_oosn/autonotify&authkey=<?php echo $hb_oosn_authkey; ?>',
		success: function(result) {
			 $('#msgoutput').html('<div class="alert alert-info">'+result+'<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
			 $("html, body").animate({ scrollTop: 0 }, "slow");
			 loadpages();
		},
		error: function(xhr, ajaxOptions, thrownError) { alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText); }
	 });					
}

function emailSelected(){
	$('#msgoutput').html('<center><i class="fa fa-spinner fa-spin fa-3x fa-fw"></i></center>');
	var selected_items = $('input[name="selected[]"]:checked').map(function(){
        return this.value;
    }).get();
	//var oosn_id = selected_items[0];
	if (selected_items.length < 1) {
		$('#msgoutput').html('<div class="alert alert-danger">Please select record!<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
		return false;
	} else {
		$.ajax({
		    type: 'post',
			url: '<?php echo $catalog; ?>index.php?route=extension/module/product_oosn/force_send_email&authkey=<?php echo $hb_oosn_authkey; ?>',
			data: {selected : selected_items},
			success: function(result) {
				 $('#msgoutput').html('<div class="alert alert-info">'+result+'<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
				 $("html, body").animate({ scrollTop: 0 }, "slow");
				 loadpages();
			},
			error: function(xhr, ajaxOptions, thrownError) { alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText); }
		 });
	 }
					
}

function showProductReport(product_id){
	$('#product-block').html('<i class="fa fa-circle-o-notch fa-spin fa-2x fa-fw"></i>');
	$('#product-block').load('index.php?route=<?php echo $base_route; ?>/hb_oosn/product_report&token=<?php echo $token; ?>&product_id='+product_id);
	$('#product-modal').modal('show');
}

function chartPID(){
	$('#chart-block').html('<i class="fa fa-circle-o-notch fa-spin fa-2x fa-fw"></i>');
	$('#chart-block').load('index.php?route=<?php echo $base_route; ?>/hb_oosn/chart_product_demand&token=<?php echo $token; ?>');
	$('#chart-modal').modal('show');
}

function chartRecordsStat(){
	$('#chart-block').html('<i class="fa fa-circle-o-notch fa-spin fa-2x fa-fw"></i>');
	$('#chart-block').load('index.php?route=<?php echo $base_route; ?>/hb_oosn/chart_record_stat&token=<?php echo $token; ?>');
	$('#chart-modal').modal('show');
}

function chartTrend(){
	$('#chart-block').html('<i class="fa fa-circle-o-notch fa-spin fa-2x fa-fw"></i>');
	$('#chart-block').load('index.php?route=<?php echo $base_route; ?>/hb_oosn/chart_trend&token=<?php echo $token; ?>');
	$('#chart-modal').modal('show');
}

$('#delete-selected').on('click', function() {
	$('#msgoutput').html('<center><i class="fa fa-spinner fa-spin fa-3x fa-fw"></i></center>');
	var selected_items = $('input[name="selected[]"]:checked').map(function(){
        return this.value;
    }).get();
	
	$.ajax({
		  type: 'post',
		  url: 'index.php?route=<?php echo $base_route; ?>/hb_oosn/delete_selected&token=<?php echo $token; ?>',
		  data: {selected : selected_items},
		  dataType: 'json',
		  success: function(json) {
				if (json['success']) {
					  $('#msgoutput').html('<div class="alert alert-success">'+json['success']+'<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
					  loadpages();
				}
				if (json['warning']) {
					  $('#msgoutput').html('<div class="alert alert-danger">'+json['warning']+'<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
				}
		  },			
		  error: function(xhr, ajaxOptions, thrownError) { alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText); }
	 });	
});

$('#reset-selected').on('click', function() {
	$('#msgoutput').html('<center><i class="fa fa-spinner fa-spin fa-3x fa-fw"></i></center>');
	var selected_items = $('input[name="selected[]"]:checked').map(function(){
        return this.value;
    }).get();
	
	$.ajax({
		  type: 'post',
		  url: 'index.php?route=<?php echo $base_route; ?>/hb_oosn/reset_selected&token=<?php echo $token; ?>',
		  data: {selected : selected_items},
		  dataType: 'json',
		  success: function(json) {
				if (json['success']) {
					  $('#msgoutput').html('<div class="alert alert-success">'+json['success']+'<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
					  loadpages();
				}
				if (json['warning']) {
					  $('#msgoutput').html('<div class="alert alert-danger">'+json['warning']+'<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
				}
		  },			
		  error: function(xhr, ajaxOptions, thrownError) { alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText); }
	 });	
});

function installScript(installtype){
	$('#install-script-btn').attr("disabled", "disabled");
	$('#msgoutput').html('<center><i class="fa fa-spinner fa-spin fa-3x fa-fw"></i></center>');
	$.ajax({
		  type: 'post',
		  url: 'index.php?route=<?php echo $base_route; ?>/hb_oosn/install_script&token=<?php echo $token; ?>',
		  data: {license : $('#hb_oosn_license').val(), type: installtype},
		  dataType: 'json',
		  success: function(json) {
				if (json['success']) {
					  $('#msgoutput').html('<div class="alert alert-success">'+json['success']+'<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
				}
				if (json['error']) {
					  $('#msgoutput').html('<div class="alert alert-danger">'+json['error']+'<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
					  $('#install-script-btn').removeAttr("disabled");
				}
		  },			
		  error: function(xhr, ajaxOptions, thrownError) { alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText); }
	 });	
}

function enableScript(){
	$('#enable-script-btn').attr("disabled", "disabled");
	$('#msgoutput').html('<center><i class="fa fa-spinner fa-spin fa-3x fa-fw"></i></center>');
	$.ajax({
		  url: 'index.php?route=<?php echo $base_route; ?>/hb_oosn/enable_script&token=<?php echo $token; ?>',
		  dataType: 'json',
		  success: function(json) {
				if (json['success']) {
					  $('#msgoutput').html('<div class="alert alert-success">'+json['success']+'<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
				}
		  },			
		  error: function(xhr, ajaxOptions, thrownError) { alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText); }
	 });	
}

function removeScript(){
	$('#remove-script-btn').attr("disabled", "disabled");
	$('#msgoutput').html('<center><i class="fa fa-spinner fa-spin fa-3x fa-fw"></i></center>');
	$.ajax({
		  url: 'index.php?route=<?php echo $base_route; ?>/hb_oosn/uninstall_script&token=<?php echo $token; ?>',
		  dataType: 'json',
		  success: function(json) {
				if (json['success']) {
					  $('#msgoutput').html('<div class="alert alert-success">'+json['success']+'<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
				}
		  },			
		  error: function(xhr, ajaxOptions, thrownError) { alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText); }
	 });	
}
</script>
<?php echo $footer; ?>