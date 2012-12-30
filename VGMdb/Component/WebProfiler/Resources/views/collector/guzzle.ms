{{#panel}}
<div id="collector-content">
<h2>Webservice Requests</h2>
{{#collector.data}}
{{#requests}}
<table>
  <tr><td><code>{{message}}</code></td></tr>
</table>
<h3>Request</h3>
<table>
  <thead>
    <tr>
      <th scope="col">Key</th>
      <th scope="col">Value</th>
    </tr>
  </thead>
  <tbody>
    {{:request}}
    <tr>
      <th>{{..}}</th>
      <td>{{#.}}<div>{{.}}</div>{{/.}}</td>
    </tr>
    {{/request}}
</tbody>
</table>
<h3>Response</h3>
<table>
  <thead>
    <tr>
      <th scope="col">Key</th>
      <th scope="col">Value</th>
    </tr>
  </thead>
  <tbody>
    {{:response}}
    <tr>
      <th>{{..}}</th>
      <td>{{#.}}<div>{{.}}</div>{{/.}}</td>
    </tr>
    {{/response}}
    <tr>
      <td colspan="2">
        <pre class="hidden" style="white-space: pre-wrap; white-space: -moz-pre-wrap !important; white-space: -pre-wrap; white-space: -o-pre-wrap; word-wrap: break-word;">{{body}}</pre>
      </td>
    </tr>
</tbody>
</table>

<script type="text/javascript">//<![CDATA[
    /**
     * Query all elements matching a CSS selector.
     *
     * @param  string selector a CSS-selector-compatible query string.
     *
     * @return DOMElement|null
     */
    function queryAll(selector)
    {
        "use strict";
        var key = 'SELECTOR: ' + selector;

        return document.querySelectorAll(selector);
    }

    function prettyPrintJson(elements) {
        for (var i = 0; i < elements.length; ++i) {
            var body = JSON.parse(elements[i].innerText);
            if (body) {
                elements[i].innerText = JSON.stringify(body, undefined, 4);
                elements[i].className = '';
            }
        }
    }

    window.setTimeout(function() {
        prettyPrintJson(queryAll('pre.hidden'));
    }, 25);

//]]></script>

{{/requests}}
{{/collector.data}}
{{^collector.data}}
<p><em>No requests sent.</em></p>
{{/collector.data}}
</div>
{{/panel}}
{{#toolbar}}
<span class="label">
  <span class="icon"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABIAAAAcCAYAAABsxO8nAAAAAXNSR0IArs4c6QAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAAOxAAADsQBlSsOGwAAAAd0SU1FB9wJFA0kAmx9x5QAAAAdaVRYdENvbW1lbnQAAAAAAENyZWF0ZWQgd2l0aCBHSU1QZC5lBwAAAsRJREFUOMvdlL1vXUUQxc+ZXV/HJCSiQRRBdOHDEYVrR5Gf3vWHrMjPtHTQ5m9IRQtI/AE0NDS2sYSc5+cPCSwUKVKE5BRBKD1ShEgsEM593pmh2Wvte3kE6kxz9652f3PmzNwLvLLBdrGwsPAGyTkRaQAYACVZkUyDweDe/wJ1Op2eiGzFGEESIQSYGcwMAODup7u7u6/9J6jb7T6cmpq6XlXVdRG5TDK6u6rqM1X9xszmRGQ2pfRrVVX3qqr6dGNj4/gF0PLy8o/T09M3tre3OZ5pbW1tXVU3U0pz/X7/59XVVQ8hIMb44ebm5sP2nAAAyQsvUf02SZAc5vebqorhcHjc6/XeHQEBeK6qk2sn/zAzkEzZr5OU0klKCSml+yOglNJTd58IOjs7g6rC3U8BwMwigGMz+0VVL7fnIgCo6pOmabCysrKVWz/j7n8COBsOhx+TRIzxBAD6/f6DxcXF70h+nlLCOOiZmUFVeyTbks7bH2PEzs7OSXtpMBh80e12ZwB8NgIys6chBLg7Wq/cHdlkmBnquva8t7W3t/eRiNwv7Yj5kpsZRKQ0Ga26lBJIfk/yfQDrnU6n0zTNkxDCKKj8VEqYmSGEgJQSjo6ObuWv4CcAKyQPyk7HfPnvdqOUKyJQ1XNluYtBRC4AuFruS87srbHt8yXxG8lHAJpSfbu6KCIY96lVV8JJXnL3d0IId0miruvZc5CI/KuMcXgI4UqM8drh4eHvuaOzZWnPW0/GSyshOU4BXCwUflAq4qTs7g4RGW9AjDFWWd0PJN8rPZqZVJaIjMxTvvy6u7fz97W7r5dzdGmSNyGEF0DufiWEMJ3XjbtXpaLZcqJLv8wMMUbUdf1mVnQHwLW6rp3ktyLy6Hyi5+fnr4rIbRF5bGZ/5Z+YAJgRkbdCCGl/f//LNtnS0tJNVf3E3R8cHBx8hVc7/gEz5WHvMjpIQQAAAABJRU5ErkJggg==" alt="Guzzle" /></span>
  <strong>Guzzle</strong>
</span>
{{/toolbar}}