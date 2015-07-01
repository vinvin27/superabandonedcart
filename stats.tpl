<h1>Stats are on beta /!\</h1>

<div class="panel col-lg-12">
								<div class="panel-heading">
									 {l s='Tracking click on campaign'}
								</div> <!-- panel-heading -->

<canvas id="myChart" width="800" height="400"></canvas>

</div>

<script>
	// Get the context of the canvas element we want to select
	var ctx = document.getElementById("myChart").getContext("2d");

	var data = {
	labels: [{$label}],
	datasets: [
		{
			label: "Click on campaing",
			fillColor: "rgba(220,220,220,0.5)",
			strokeColor: "rgba(220,220,220,0.8)",
			highlightFill: "rgba(220,220,220,0.75)",
			highlightStroke: "rgba(220,220,220,1)",
			data: [{$data}]
		}
	]
};
var myBarChart = new Chart(ctx).Bar(data, Chart.defaults.Bar);

</script>
