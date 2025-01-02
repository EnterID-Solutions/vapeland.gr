<div class="table-responsive">
  <table class="table table-bordered table-hover">
    <thead>
      <tr>
        <td width="1" style="text-align: center;"><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', this.checked);" /></td>
        <td class="text-center">Product Id</td>
        <td class="text-left">Product Name</td>
        <td class="text-left">Option Out of Stock</td>
        <td class="text-left">Selected Options</td>
        <td class="text-center">Quantity</td>
		<td class="text-left col-compact">Customer Email</td>
        <td class="text-center col-detail">Customer Details</td>
        <td class="text-center col-detail">Customer Comment</td>
        <td class="text-center col-detail">Customer Language</td>
        <td class="text-left col-detail">Store</td>
        <td class="text-left">Customer Alert Set On</td>
        <td class="text-left">Notified Date</td>
      </tr>
    </thead>
    <tbody>
      <?php if ($records) { ?>
      <?php foreach ($records as $record) { ?>
      <tr>
        <td style="text-align: center;"><input type="checkbox" name="selected[]" value="<?php echo $record['id']; ?>" /></td>
        <td class="text-center"><a onclick="showProductReport('<?php echo $record['product_id']; ?>');"><?php echo $record['product_id']; ?></a></td>
        <td class="text-left">
			<a href="<?php echo $record['product_link']; ?>" target="_blank"><?php echo $record['name']; ?></a> <br />
			<div class="col-detail label label-warning">Available Qty: <?php echo $record['product_qty']; ?></div> <br />
			<div class="col-detail label label-primary" data-toggle="tooltip" title="Stock Status"> <?php echo $record['product_stock_status']; ?></div>
		</td>
        <td class="text-left"><?php echo $record['selected_option']; ?></td>
        <td class="text-left"><?php echo $record['all_selected_option']; ?></td>
        <td class="text-center"><?php echo $record['qty']; ?></td>
		<td class="text-left col-compact"><?php echo $record['email']; ?></td>
        <td class="col-detail"><table class="table table-bordered table-hover" style="min-width:200px;">
            <tr>
              <td class="text-center"><span data-toggle="tooltip" title="Email"><i class="fa fa-envelope" aria-hidden="true"></i></span></td>
              <td><?php echo $record['email']; ?></td>
            </tr>
            <tr>
              <td class="text-center"><span data-toggle="tooltip" title="IP Address"><i class="fa fa-globe" aria-hidden="true"></i></span></td>
              <td><?php echo $record['ip']; ?></td>
            </tr>
            <tr>
              <td class="text-center"><span data-toggle="tooltip" title="Name"><i class="fa fa-user" aria-hidden="true"></i></span></td>
              <td><?php echo $record['fname']; ?></td>
            </tr>
            <tr>
              <td class="text-center"><span data-toggle="tooltip" title="Phone"><i class="fa fa-phone" aria-hidden="true"></i></span></td>
              <td><?php echo $record['phone']; ?></td>
            </tr>
			<tr>
              <td class="text-center"><span data-toggle="tooltip" title="Customer Type"><i class="fa fa-street-view" aria-hidden="true"></i></span></td>
              <td><?php echo $record['customer_type']; ?></td>
            </tr>
			<tr>
              <td class="text-center"><span data-toggle="tooltip" title="Previous Purchases"><i class="fa fa-usd" aria-hidden="true"></i></span></td>
              <td><?php echo $record['customer_purchases']; ?></td>
            </tr>
          </table></td>
        <td class="text-left col-detail" style="word-break:break-all;"><?php echo $record['comment']; ?></td>
        <td class="text-center col-detail"><?php echo $record['language_name']['name']; ?></td>
        <td class="text-left col-detail"><a data-toggle="tooltip" title="<?php echo $record['store_url']; ?>"><?php echo $record['store_name']; ?></a></td>
        <td class="text-right"><?php echo $record['enquiry_date']; ?></td>
        <td class="text-right"><?php echo $record['notify_date']; ?></td>
      </tr>
      <?php } ?>
      <?php } else { ?>
      <tr>
        <td class="text-center" colspan="12">No Records Found</td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
</div>
<div class="row">
  <div class="col-sm-6 text-left"><?php echo $pagination; ?></div>
  <div class="col-sm-6 text-right"><?php echo $results; ?></div>
</div>
<script type="text/javascript">
$('.col-compact').hide();
$('.col-detail').show();
</script>
