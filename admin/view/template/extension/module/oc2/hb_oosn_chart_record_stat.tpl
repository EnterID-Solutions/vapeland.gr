<?php if ($total_alert == 0) { ?>
	<div class="pr_warning">Data not available to plot the chart</div>
<?php } else { ?>
	<div id="pie_chart_div"></div>
	<script type="text/javascript">	
      google.charts.load('current', {'packages':['corechart']});
      google.charts.setOnLoadCallback(drawChart);

      function drawChart() {

        var data = google.visualization.arrayToDataTable([
          ['Type', 'Records total'],
          ['Total Notified', <?php echo $total_responded; ?>],
          ['Total Pending', <?php echo $total_pending; ?>]
        ]);

        var options = {
          title: 'Records Statistics',
		  is3D: true,
		  height: 500,
		  width: 500
        };

        var chart = new google.visualization.PieChart(document.getElementById('pie_chart_div'));
        chart.draw(data, options);
      }
	</script>
<?php } ?>