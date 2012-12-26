{{#panel}}
<div id="collector-content">
{{#collector.data}}
<h2>Project Configuration</h2>
<table>
  <tr>
    <th>Key</th>
    <th>Value</th>
  </tr>
  <tr>
    <th>Symfony version</th>
    <td>{{symfony_version}}</td>
  </tr>
  <tr>
    <th>Silex version</th>
    <td>{{silex_version}}</td>
  </tr>
  <tr>
    <th>Application name</th>
    <td>{{name}}</td>
  </tr>
  <tr>
    <th>Environment</th>
    <td>{{env}}</td>
  </tr>
  <tr>
    <th>Debug</th>
    <td>{{#debug}}enabled{{/debug}}{{^debug}}disabled{{/debug}}</td>
  </tr>
</table>

<h2>PHP configuration</h2>
<table>
  <tr>
    <th>Key</th>
    <th>Value</th>
  </tr>
  <tr>
    <th>PHP version</th>
    <td>{{php_version}}</td>
  </tr>
  <tr>
    <th>Xdebug</th>
    <td>{{#xdebug_enabled}}enabled{{/xdebug_enabled}}{{^xdebug_enabled}}disabled{{/xdebug_enabled}}</td>
  </tr>
  <tr>
    <th>XCache</th>
    <td>{{#xcache_enabled}}enabled{{/xcache_enabled}}{{^xcache_enabled}}disabled{{/xcache_enabled}}</td>
  </tr>
  <tr>
    <th>APC</th>
    <td>{{#apc_enabled}}enabled{{/apc_enabled}}{{^apc_enabled}}disabled{{/apc_enabled}}</td>
  </tr>
  <tr>
    <th>EAccelerator</th>
    <td>{{#eaccel_enabled}}enabled{{/eaccel_enabled}}{{^eaccel_enabled}}disabled{{/eaccel_enabled}}</td>
  </tr>
  <tr>
    <th>WinCache</th>
    <td>{{#wincache_enabled}}enabled{{/wincache_enabled}}{{^wincache_enabled}}disabled{{/wincache_enabled}}</td>
  </tr>
</table>
{{/collector.data}}
</div>
{{/panel}}
{{#toolbar}}
<span class="label">
    <span class="icon"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABUAAAAcCAMAAAC5xgRsAAAAZlBMVEX///////////////////////////////////////////////////////////////////////////////////////////+ZmZmZmZlISEhJSUmdnZ1HR0fR0dFZWVlpaWlfX18/Pz+puygPAAAAIXRSTlMACwwlJygpLzIzNjs8QEtMUmd6e32AucDBw8fIydTm6u5l8MjvAAAAo0lEQVR42r2P2Q6CMBBFL6XsZRGRfZv//0nbDBNEE19MnJeTc5ILKf58ahiUwzy/AJpIWwREwQnEXRdbGCLjrO+djWRvVMiJcigxB7viGogxDdJpSmHEmCVPS7YczJvgUu+CS30IvtbNYZMvsGVo2mVpG/kbm4auiCpdcC3YPCAhSpAdUzaAn6qPKZtUT6ZSzb4bi2hdo9MQ1nX4ASjfV+/4/Z40pyCHrNTbIgAAAABJRU5ErkJggg==" alt="Configuration" /></span>
    <strong>Config</strong>
</span>
{{/toolbar}}