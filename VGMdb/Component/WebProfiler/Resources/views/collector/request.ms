{{#panel}}
<div id="collector-content">
{{#collector.data}}
<h2>Request <small> - HTTP {{status_code}} - {{format}}</small></h2>
<h3>Request GET Parameters</h3>
{{?request_query}}
<table>
  <thead>
    <tr>
      <th scope="col">Key</th>
      <th scope="col">Value</th>
    </tr>
  </thead>
  <tbody>
    {{:request_query}}
    <tr>
      <th>{{..}}</th>
      <td>{{#.}}{{.}}{{/.}}</td>
    </tr>
    {{/request_query}}
</tbody>
</table>
{{/request_query}}
{{^request_query}}
<p><em>No GET parameters</em></p>
{{/request_query}}

<h3>Request POST Parameters</h3>
{{?request_request}}
<table>
  <thead>
    <tr>
      <th scope="col">Key</th>
      <th scope="col">Value</th>
    </tr>
  </thead>
  <tbody>
    {{:request_request}}
    <tr>
      <th>{{..}}</th>
      <td>{{#.}}{{.}}{{/.}}</td>
    </tr>
    {{/request_request}}
</tbody>
</table>
{{/request_request}}
{{^request_request}}
<p><em>No POST parameters</em></p>
{{/request_request}}

<h3>Request Attributes</h3>
{{?request_attributes}}
<table>
  <thead>
    <tr>
      <th scope="col">Key</th>
      <th scope="col">Value</th>
    </tr>
  </thead>
  <tbody>
    {{:request_attributes}}
    <tr>
      <th>{{..}}</th>
      <td>{{#.}}{{.}}{{/.}}</td>
    </tr>
    {{/request_attributes}}
</tbody>
</table>
{{/request_attributes}}
{{^request_attributes}}
<p><em>No attributes</em></p>
{{/request_attributes}}

<h3>Request Cookies</h3>
{{?request_cookies}}
<table>
  <thead>
    <tr>
      <th scope="col">Key</th>
      <th scope="col">Value</th>
    </tr>
  </thead>
  <tbody>
    {{:request_cookies}}
    <tr>
      <th>{{..}}</th>
      <td>{{#.}}{{.}}{{/.}}</td>
    </tr>
    {{/request_cookies}}
</tbody>
</table>
{{/request_cookies}}
{{^request_cookies}}
<p><em>No cookies</em></p>
{{/request_cookies}}

<h3>Request Headers</h3>
{{?request_headers}}
<table>
  <thead>
    <tr>
      <th scope="col">Key</th>
      <th scope="col">Value</th>
    </tr>
  </thead>
  <tbody>
    {{:request_headers}}
    <tr>
      <th>{{..}}</th>
      <td>{{#.}}{{.}}{{/.}}</td>
    </tr>
    {{/request_headers}}
</tbody>
</table>
{{/request_headers}}

<h3>Request Content</h3>
{{?content}}
<pre>{{content}}</pre>
{{/content}}
{{^content}}
<p><em>No content</em></p>
{{/content}}

<h3>Request Server Parameters</h3>
{{?request_server}}
<table>
  <thead>
    <tr>
      <th scope="col">Key</th>
      <th scope="col">Value</th>
    </tr>
  </thead>
  <tbody>
    {{:request_server}}
    <tr>
      <th>{{..}}</th>
      <td>{{#.}}{{.}}{{/.}}</td>
    </tr>
    {{/request_server}}
</tbody>
</table>
{{/request_server}}

<h3>Response Headers</h3>
{{?response_headers}}
<table>
  <thead>
    <tr>
      <th scope="col">Key</th>
      <th scope="col">Value</th>
    </tr>
  </thead>
  <tbody>
    {{:response_headers}}
    <tr>
      <th>{{..}}</th>
      <td>{{#.}}{{.}}{{/.}}</td>
    </tr>
    {{/response_headers}}
</tbody>
</table>
{{/response_headers}}

<h3>Session Metadata</h3>
{{?session_metadata}}
<table>
  <thead>
    <tr>
      <th scope="col">Key</th>
      <th scope="col">Value</th>
    </tr>
  </thead>
  <tbody>
    {{:session_metadata}}
    <tr>
      <th>{{..}}</th>
      <td>{{#.}}{{.}}{{/.}}</td>
    </tr>
    {{/session_metadata}}
</tbody>
</table>
{{/session_metadata}}
{{^session_metadata}}
<p><em>No session metadata</em></p>
{{/session_metadata}}

<h3>Session Attributes</h3>
{{?session_attributes}}
<table>
  <thead>
    <tr>
      <th scope="col">Key</th>
      <th scope="col">Value</th>
    </tr>
  </thead>
  <tbody>
    {{:session_attributes}}
    <tr>
      <th>{{..}}</th>
      <td>{{#.}}{{.}}{{/.}}</td>
    </tr>
    {{/session_attributes}}
</tbody>
</table>
{{/session_attributes}}
{{^session_attributes}}
<p><em>No session attributes</em></p>
{{/session_attributes}}

<h3>Flashes</h3>
{{?flashes}}
<table>
  <thead>
    <tr>
      <th scope="col">Key</th>
      <th scope="col">Value</th>
    </tr>
  </thead>
  <tbody>
    {{:flashes}}
    <tr>
      <th>{{..}}</th>
      <td>{{#.}}{{.}}{{/.}}</td>
    </tr>
    {{/flashes}}
</tbody>
</table>
{{/flashes}}
{{^flashes}}
<p><em>No flashes</em></p>
{{/flashes}}

{{/collector.data}}
</div>
{{/panel}}
{{#toolbar}}
<span class="label">
  <span class="icon"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACYAAAAcCAQAAACn1QXuAAAD2UlEQVR42p2Ve0zTVxTHKS4+KCBqNomCClgEJJAYkznQQIFaWltAiigsxGUgMy6b45HWV4UKUoP1yaMS0DqniVpngKlEMoMzW2Z0QTf4Ax/bdCzFCpQWq60U+Xp/baG/EoGf3vPH7/b3PffTc++55/w8xg+wji4W3ImDw4S3DgSD5fGhA+wcbRxclqsB+30RnmWcda1JPWn1poj8e3TYlvb/l6edTdSLWvYHgcUIdSwiuduxOOdu/n90WF7350648J+a0ClxYNWECglgahP+OyUOPpm34sDMNt6Ez+QwjniAKSzFgKWTw6L33x/3/yMHzU09l/XKlykj7krlXURNDlsEaVm/a8Fh48trUEEKGY4Zb5SaXUpZH4oROAlKvjijPu9GQfcY6jkOQoBlWIgARCAVVbtNo1rxky9/lqiV/hMmQfwXfRtZQxYVVoItC5aUpO8rDIcvYvUNqcN0n7TfJkyC+5lUdYIH9hlOkn3bCWbVCoJLLX9C9+FZEcoIpj2HYHh9XT92ZbUEFl7XSvfhD2EVI5imFh/DX948+lvWhgAEHL3kBrNhNSOYvImCdSgEb+wbGrmjomCFv46DrWn6hN+2QY6ZDYH8Tt6Dv+c4Yfn9bofbN8ABG/xHjYcMKmNHC0Tw/XOF0Ez3+VaH2BMZ1Ezclaynnm1x8LTDBo7U65Tm0tejrltPwwvzIcQO7EIKFsB3c8uoprAqzZruwQpE1cnpeMVxxZLNc8mFQQy2W9Tb+1xSplbjD18EEvM7sjTjuksp6rXVDBeVN29s5ztjFY1VSILpfJAHZiFkG1lAtyTD+gvZsix5emPSC3flm6v3JGvfxNvn+8zDt/HLFR3XUYI6RFPltERkYFro4j6Itdd5JB6JzaaGBAKUFtorpOsHRNoLveAxU1jRQ6xFQbaVNNFBpICN6YjZ00UpN0swj4KFPK/MtTJBffXKoETk3mouiYw7cmoLpsGzNVFkth+NpTKWgnkjof9MnjOflRYqsy4rfV1udebZatIgHhyB0XmylsyL2VXJjtQReMNWe9uGH5JN3ytMubY6HS7J9HSYTI/L1c9ybQoTQfEwG2HN52p7KixuEQ91PH5wEYkE5sRxUYJaFCCr4g+6o+o2slEMNVHjCYqF+RBjJ87m0OI/2YnvwMVCgnLi2AjCcgQgpGen1Mh1bATSgV4pghGISKKyqT6Gj+CHRUj/grT66sGOp7tIjOpmhGEGqYLxA174DOW4gjZiP6EMn2LWO7pz+O8N2nYcQhGq7v+ITZg3wYcPPghFDKibGUNm3u/qq5hL1PWIxgJEIRZBmE69fQsyes/JMSWb+gAAAABJRU5ErkJggg==" alt="Request" /></span>
  <strong>Request</strong>
</span>
{{/toolbar}}