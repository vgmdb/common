{{#panel}}
<div id="collector-content">
{{#collector.data}}
<h2>Router</h2>
<h3>Routing <small>for "{{request.data.path_info}}"</small></h3>
{{#request.data.request_attributes._route}}
<table>
  <thead>
    <tr>
      <th scope="col">Key</th>
      <th scope="col">Value</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <th>Route</th>
      <td>{{request.data.request_attributes._route}}</td>
    </tr>
    <tr>
      <th>Parameters</th>
      <td>{{request.data.route_params}}</td>
    </tr>
    {{#collector.data.redirect}}
    <tr>
      <th>Redirect URL</th>
      <td>{{collector.data.url}}</td>
    </tr>
    <tr>
      <th>Redirect Route</th>
      <td>{{collector.data.route}}</td>
    </tr>
    {{/collector.data.redirect}}
</tbody>
</table>
{{/request.data.request_attributes._route}}
{{^request.data.request_attributes._route}}
<p><em>No matching route</em></p>
{{/request.data.request_attributes._route}}

<h3>Route Trace</h3>
<table class="routing inline">
  <tr>
    <th>Route name</th>
    <th>Path</th>
    <th>Log</th>
  </tr>
  {{#traces}}
  <tr>
    <td>{{name}}</td>
    <td>{{path}}</td>
    <td>{{log}}</td>
  </tr>
  {{/traces}}
</table>
<em><small>Note: The above matching is based on the configuration for the current router
which might differ from the configuration used while routing this request.</small></em>
{{/collector.data}}
</div>
{{/panel}}
{{#toolbar}}
<span class="label">
  <span class="icon"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAMAAABEpIrGAAABDlBMVEU/Pz////////////////////////////////////////////////////////////////////+qqqr///////////+kpKT///////////////////////////////////+Kior///////////+Ghob///////9kZGT///////////////////////9bW1v///9aWlpZWVn////t7e3////m5ub///9cXFxZWVn////////////////////KysrNzc3///9tbW1WVlZTU1NwcHCnp6dgYGCBgYGZmZl3d3dLS0tMTEyNjY2Tk5NJSUlFRUVERERZWVlCQkJVVVVAQEBCQkJUVFRVVVU/Pz9ERER+LwjMAAAAWHRSTlMAAQIDBQYHCAkLDQ4VFhscHyAiIiMlJjAyNDY3ODk9P0BAREpMTlBdXl9rb3BzdHl6gICChIyPlaOmqKuusLm6v8HFzM3X2tzd4ePn6Onq8vb5+vv9/f3+EYS6xwAAAQFJREFUeNrN0dlSwkAQBdAbA2FTQIIsAmJEA5qIiIoim8oibigI0vz/jygFZEwIw4sP3reeOtVTdRt/G6kwHBYkDvC/EL0HOCBGP4lzwN4UHJGRrMMClOmrzsDH/oYNKBLLc0gA4MwvZtUK6MELiIeDxagvgY4MIdIzxqIVfF6F4WvSSjBpZHyQW6tBO7clIHjRNwO9dDdP5UQWAc9BfWICalSZZzfgBCBsHndNQIEl4o5Wna0s6UYZROcSO3IwMVsZVX9Xfe0CAF7VN+414N7PB68aH7xdxm2+YEXVzmJuLANWVHLbBXvAivqnID0iGqU5IPU0/npMckD49LasyTDlG31Ah7wRFiUBAAAAAElFTkSuQmCC" alt="Routing" /></span>
  <strong>Routing</strong>
</span>
{{/toolbar}}