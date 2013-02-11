{{#panel}}
<div id="collector-content">
{{#collector.data}}
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
  google.load("visualization", "1", {packages:["corechart"]});
  google.setOnLoadCallback(drawChart);
  function drawChart() {
    var data = google.visualization.arrayToDataTable([
      ['Namespace', 'Class Count'],
      {{:namespaces}}['{{..}}', {{.}}],{{/namespaces}}
      ['', 0]
    ]);

    var options = {
      title: 'Namespaces'
    };

    var chart = new google.visualization.PieChart(document.getElementById('chart'));
    chart.draw(data, options);
  }
</script>
<h2>ClassLoader</h2>
<h3>{{classcount}} classes loaded</h3>
<div id="chart" style="width: 100%; height: 500px;"></div>
<table>
  <tbody>
  {{#classes}}
  <tr>
    <td>{{.}}</td>
  </tr>
  {{/classes}}
  </tbody>
</table>
{{/collector.data}}
</div>
{{/panel}}
{{#toolbar}}
<span class="label">
  <span class="icon"><img src="data:image/png;base64,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" alt="ClassLoader" /></span>
  <strong>ClassLoader</strong>
</span>
{{/toolbar}}