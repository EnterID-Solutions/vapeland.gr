<h3 class="sub-heading"><b>Product: <?php echo $product_name; ?></b></h3>
<div id="product_trend_chart"></div>

<h4><b>Customers looking for this product (<?php echo $pending_customer_count; ?>)</b></h4>
<div class="table-responsive">
  <table class="table table-bordered table-hover">
    <thead>
      <tr>
		<td class="text-left">Email</td>
		<td class="text-left">Name</td>
		<td class="text-left">Phone</td>
		<td class="text-left">Selected Options</td>
		<td class="text-center">Qty</td>
		<td class="text-left">Type</td>
        <td class="text-right">Previous Purchases</td>
		<td class="text-center">Since</td>
        <td class="text-left">Enquiry Date</td>
      </tr>
    </thead>
    <tbody>
      <?php if ($pending_customers) { ?>
      <?php foreach ($pending_customers as $record) { ?>
      <tr>
	  	<td class="text-left"><?php echo $record['email']; ?></td>
		<td class="text-left"><?php echo $record['name']; ?></td>
		<td class="text-left"><?php echo $record['phone']; ?></td>
        <td class="text-left"><?php echo $record['all_selected_option']; ?></td>
        <td class="text-center"><?php echo $record['qty']; ?></td>
		<td class="text-left"><?php echo $record['customer_type']; ?></td>
		<td class="text-right"><?php echo $record['customer_purchases']; ?></td>
		<td class="text-center"><?php echo $record['customer_since']; ?></td>
        <td class="text-right"><?php echo $record['enquiry_date']; ?></td>
      </tr>
      <?php } ?>
      <?php } else { ?>
      <tr>
        <td class="text-center" colspan="9">No Records Found</td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
</div>

<h4><b>Customers Archive (<?php echo $notified_customer_count; ?>)</b></h4>
<div class="table-responsive">
  <table class="table table-bordered table-hover">
    <thead>
      <tr>
		<td class="text-left">Email</td>
		<td class="text-left">Name</td>
		<td class="text-left">Phone</td>
		<td class="text-left">Selected Options</td>
		<td class="text-center">Qty</td>
		<td class="text-left">Type</td>
        <td class="text-right">Previous Purchases</td>
		<td class="text-center">Since</td>
        <td class="text-left">Enquiry Date</td>
		<td class="text-left">Notified Date</td>
      </tr>
    </thead>
    <tbody>
      <?php if ($notified_customers) { ?>
      <?php foreach ($notified_customers as $record) { ?>
      <tr>
	  	<td class="text-left"><?php echo $record['email']; ?></td>
		<td class="text-left"><?php echo $record['name']; ?></td>
		<td class="text-left"><?php echo $record['phone']; ?></td>
        <td class="text-left"><?php echo $record['all_selected_option']; ?></td>
        <td class="text-center"><?php echo $record['qty']; ?></td>
		<td class="text-left"><?php echo $record['customer_type']; ?></td>
		<td class="text-right"><?php echo $record['customer_purchases']; ?></td>
		<td class="text-center"><?php echo $record['customer_since']; ?></td>
        <td class="text-right"><?php echo $record['enquiry_date']; ?></td>
		<td class="text-right"><?php echo $record['notified_date']; ?></td>
      </tr>
      <?php } ?>
      <?php } else { ?>
      <tr>
        <td class="text-center" colspan="10">No Records Found</td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
</div>

<script type="text/javascript">
google.charts.load('current', {'packages':['line']});
    google.charts.setOnLoadCallback(drawChart);

    function drawChart() {

      var data = new google.visualization.DataTable();
	  	data.addColumn('string', 'Month');
	  	data.addColumn('number', 'Qty');

		data.addRows([
		<?php foreach ($allyears as $year) { ?>
			<?php foreach ($total_alert[$year] as $alert) { ?>
				['<?php echo $alert["month"].' '.$year; ?>', <?php echo $alert["count"]; ?>],
			<?php } ?>
		<?php } ?>
		]);

      var options = {
        chart: {
          title: 'Product Subscription Trend',
        },
        height: 300
      };

      var chart = new google.charts.Line(document.getElementById('product_trend_chart'));

      chart.draw(data, google.charts.Line.convertOptions(options));
	  
    }  
	
</script>	
