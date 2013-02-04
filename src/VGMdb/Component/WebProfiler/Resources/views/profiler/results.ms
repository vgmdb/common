<div style="padding: 40px">
<h2>Search Results</h2>
{{?tokens}}
<table>
  <thead>
    <tr>
      <th scope="col">Token</th>
      <th scope="col">IP</th>
      <th scope="col">Method</th>
      <th scope="col">URL</th>
      <th scope="col">Time</th>
    </tr>
  </thead>
  <tbody>
    {{#tokens}}
    <tr>
      <td><a href="{{link}}">{{token}}</a></td>
      <td>{{ip}}</td>
      <td>{{method}}</td>
      <td>{{url}}</td>
      <td>{{time}}</td>
    </tr>
    {{/tokens}}
  </tbody>
</table>
{{/tokens}}
{{^tokens}}
<p><em>No results found</em></p>
{{/tokens}}
</div>