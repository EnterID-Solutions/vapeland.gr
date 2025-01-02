<?php if (!empty($graph_data)) { ?>
	<div id="trendchart_div" style="min-width:800px;"></div>
	<script type="text/javascript">
	google.charts.load('current', {packages: ['corechart', 'line']});
	google.charts.setOnLoadCallback(drawBackgroundColor);
	
	function drawBackgroundColor() {
		  var data = new google.visualization.DataTable();
		  data.addColumn('string', 'Days');
		  data.addColumn('number', 'Total Subscription');
	
		  data.addRows([
			<?php echo $graph_data; ?>
		  ]);
	
		  var options = {
		  	title: 'Subscription Trend',
			hAxis: { title: 'Days' },
			vAxis: { title: 'Subscriptions' },
			colors: ['#097138'],
			height: 500,
		  };
	
		  var chart = new google.visualization.LineChart(document.getElementById('trendchart_div'));
		  chart.draw(data, options);
		}
	</script>
<?php } else { ?>
	<div class="pr_warning">Data not available to plot the chart</div>
<?php } ?>