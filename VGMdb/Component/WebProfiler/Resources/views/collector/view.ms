{{#panel}}
<div id="collector-content">
{{#collector.data}}
<h2>Views</h2>
<h3>Globals</h3>
<table>
  <thead>
    <tr>
      <th scope="col">Key</th>
      <th scope="col">Value</th>
    </tr>
  </thead>
  <tbody>
    {{:globals}}
    <tr>
      <th>{{..}}</th>
      <td>{{.}}</td>
    </tr>
    {{/globals}}
</tbody>
</table>
{{#views}}
<h3>{{template}} <small>- {{engine}} - {{time}} ms</small></h3>
<table>
  <thead>
    <tr>
      <th scope="col">Key</th>
      <th scope="col">Value</th>
    </tr>
  </thead>
  <tbody>
    {{:data}}
    <tr>
      <th>{{..}}</th>
      <td>{{.}}</td>
    </tr>
    {{/data}}
</tbody>
</table>
{{/views}}
{{^views}}
<p><em>No views rendered</em></p>
{{/views}}

{{/collector.data}}
</div>
{{/panel}}
{{#toolbar}}
<span class="label">
  <span class="icon"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAeAgMAAABGXkYxAAAABGdBTUEAALGPC/xhBQAAAAlQTFRFAAAA7OzsPz8/UudDywAAAAF0Uk5TAEDm2GYAAAA8SURBVBjTY2CAAa1VYNCAYGiGgkBYA4MGWAETkAGWayKVIQoyxwnFnBVaUDW0Z4iGiKLajulTsANDQx0ArI1fl9sPWHQAAAAASUVORK5CYII=" alt="View" /></span>
  <strong>View</strong>
</span>
{{/toolbar}}