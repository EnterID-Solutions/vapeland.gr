<?php if (!empty($graph_data)) { ?>
<div id="bar_chart_div"></div>
<script type="text/javascript">	
google.charts.load('current', {packages: ['corechart', 'bar']});
google.charts.setOnLoadCallback(drawMaterial);

function drawMaterial() {
      var data = google.visualization.arrayToDataTable([
	  	['Product', 'Total Demand'],
		<?php echo $graph_data; ?>
      ]);

      var materialOptions = {
        chart: {
          title: 'Top 20 Products in Demand'
        },
        hAxis: {
          title: 'Total Population',
          minValue: 0,
        },
        vAxis: {
          title: 'Product'
        },
        bars: 'horizontal',
		height: <?php echo $graph_height; ?>
      };
      var materialChart = new google.charts.Bar(document.getElementById('bar_chart_div'));
      materialChart.draw(data, materialOptions);
    }	
</script>
<?php } else { ?>
	<div class="pr_warning">Data not available to plot the chart</div>
<?php } ?>