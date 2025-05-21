<div id="notifyform_inlineform">
	<div class="notify-info-block">
		<div id="oosn_info_text_inlineform"><?php echo $oosn_info_text; ?></div>
		<div id="opt_info_inlineform"></div>
	</div>
	
	<?php if ($hb_oosn_name_enable == 'y') {?>
		<div class="notify-row-group">
			<div class="notify-label notify-col-25"><?php echo $oosn_text_name; ?></div>
			<div class="notify-col-75">
			<input type="text" placeholder="<?php echo $oosn_text_name_plh; ?>" id="notifyname_inlineform" class="notify-element" value="<?php echo $fname;?>" />
			</div>
		</div>
	 <?php } ?>

	<div class="notify-row-group">
		<div class="notify-label notify-col-25"><?php echo $oosn_text_email; ?></div>
		<div class="notify-col-75">
		<input type="text" placeholder="<?php echo $oosn_text_email_plh; ?>" id="notifyemail_inlineform" class="notify-element" value="<?php echo $email;?>" />
		</div>
	</div>
   
	<?php if ($hb_oosn_mobile_enable == 'y') {?>
	<div class="notify-row-group">
		 <div class="notify-label notify-col-25"><?php echo $oosn_text_phone; ?></div>
		<div class="notify-col-75">
		<input type="text" placeholder="<?php echo $oosn_text_phone_plh; ?>" id="notifyphone_inlineform" class="notify-element" value="<?php echo $phone;?>"/>
		</div>
	</div>
	<?php } ?>
	<?php if ($hb_oosn_comments_enable == 'y') {?>
	<div class="notify-row-group">
		<div class="notify-label notify-col-25"><?php echo $oosn_text_comment; ?></div>
		<div class="notify-col-75">
		<textarea rows="4" placeholder="<?php echo $oosn_text_comment_plh; ?>" id="notifycomment_inlineform" class="notify-element"></textarea>
		</div>
	</div>
	<?php } ?>
	<?php if ($show_captcha) { ?>
		<div class="notify-row-group">
			<div class="notify-label notify-col-25"></div>					
			<div class="notify-col-75" id="recaptcha-inline">
			<div class="g-recaptcha" data-sitekey="<?php echo $site_key; ?>"></div>
			</div>
			<script src="//www.google.com/recaptcha/api.js" type="text/javascript"></script>
		</div>
	<?php } ?>
	<div class="notify-row-group">
		<button type="button" id="notify_btn_inline" class="notify-button"><i class="fa fa-bell"></i> <?php echo $notify_button; ?></button> 
	</div>
	
	<div id='loadgif_inlineform' style='display:none'><center><img src='catalog/view/theme/default/image/loading.gif'/></center></div>
	<div id="msgoosn_inlineform"></div>
						
</div>

<script type="text/javascript">
$("#notify_btn_inline").click(function() {
    $("#msgoosn_inlineform").html(""), $("#loadgif_inlineform").show();
    var e = $("#notifyname_inlineform").length ? $("#notifyname_inlineform").val() : "",
        o = $("#notifyphone_inlineform").length ? $("#notifyphone_inlineform").val() : "";
		stock_comment = $("#notifycomment_inlineform").length ? $("#notifycomment_inlineform").val() : "";
		stock_qty = $("input[name='quantity']").length ? $("input[name='quantity']").val() : "1";
    if ($("#option_values_inlineform").length) var t = $("#option_values_inlineform").val(),
        n = $("#selected_option_inlineform").val(),
        a = $("#all_selected_option_inlineform").val();
    else var t = 0,
        n = 0,
        a = 0;
    $.ajax({
        type: "post",
        url: "index.php?route=extension/module/product_oosn",
        data: {
            email: $("#notifyemail_inlineform").val(),
			gcaptcha: $("[name='g-recaptcha-response']").val(),
            name: e,
            phone: o,
			stock_comment: stock_comment,
			stock_qty: stock_qty,
            product_id: $("input[name='product_id']").val(),
            selected_option_value: t,
            selected_option: n,
            all_selected_option: a
        },
        dataType: "json",
		success: function(json) {
			$("#loadgif").hide();
			<?php if ($show_captcha) { ?>
			 grecaptcha.reset() 
			<?php } ?>
			if (json['success']) {
				  $('#msgoosn_inlineform').html('<span class="notify_form_success">'+json['success']+'</span>');
			}
			if (json['notify_error']) {
				  $('#msgoosn_inlineform').html('<span class="notify_form_error">'+json['notify_error']+'</span>');
			}
			if (json['notify_warning']) {
				  $('#msgoosn_inlineform').html('<span class="notify_form_warning">'+json['notify_warning']+'</span>');
			}
			$("#loadgif_inlineform").hide();
        },
        error: function(e, o, t) { alert(t + "\r\n" + e.statusText + "\r\n" + e.responseText); $("#loadgif_inlineform").hide(); }
    })
});
</script>