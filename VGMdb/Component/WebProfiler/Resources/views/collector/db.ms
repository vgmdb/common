{{#panel}}
<div id="collector-content">
{{#collector.data}}
<h2>Doctrine Queries</h2>
<table summary="Show logged queries">
  <thead>
    <tr>
      <th>SQL queries</th>
    </tr>
  </thead>
  <tbody>
  {{#collector.querycount}}
  {{:queries}}
  {{:.}}
    <tr>
      <td>
        <code>{{sql}}</code>
        <div class="SQLInfo">Time: {{executionMS}} sec - Connection: {{:queries}}{{..}}{{/queries}}</div>
      </td>
    </tr>
  {{/.}}
  {{/queries}}
  {{/collector.querycount}}
  {{^collector.querycount}}
    <tr><td>No queries.</td></tr>
  {{/collector.querycount}}
  </tbody>
</table>
{{/collector.data}}
</div>
{{/panel}}
{{#toolbar}}
<span class="label">
  <span class="icon"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABoAAAAcCAYAAAB/E6/TAAABLUlEQVR42u3TP0vDQBiA8UK/gDiLzi0IhU4OEunk5OQUAhGSOBUCzqWfIKSzX8DRySF0URCcMjWLIJjFD9Cpk/D6HITecEPUuzhIAz8CIdyTP/f2iqI4qaqqDx8l5Ic2uIeP/bquezCokOAFF+oCN3t4gPzSEjc4NEPaCldQbzjELTYW0RJzHDchwwem+ons6ZBpLSJ7nueJC22h0V+FzmwWV0ee59vQNV67CGVZJmEYbkNjfpY6X6I0Qo4/3RMmTdDDspuQVsJvgkP3IdMbIkIjLPBoadG2646iKJI0Ta2wxm6OdnP0/Tk6DYJgHcfxpw21RtscDTDDnaVZ26474GkkSRIrrPEv5sgMTfHe+cA2O6wPH6vOBpYQNALneHb96XTEDI6dzpEZ0VzO0Rf3pP5LMLI4tAAAAABJRU5ErkJggg==" alt="Doctrine" /></span>
  <strong>Doctrine</strong>
</span>
{{/toolbar}}