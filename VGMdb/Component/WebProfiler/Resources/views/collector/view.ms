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
  <span class="icon"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAdCAYAAAC9pNwMAAAABGdBTUEAALGPC/xhBQAAAAFzUkdCAK7OHOkAAAAgY0hSTQAAeiYAAICEAAD6AAAAgOgAAHUwAADqYAAAOpgAABdwnLpRPAAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAAASAAAAEgARslrPgAABE1JREFUSMfNV01oXFUU/s697707k8k06UwnsWrrD20pSkIlYNGFiiJUuyjUbqSKK1vtyr1QaNSle6Hqwq1/XSoEFBdSRG1torSIorE/k2TyP5l3373v3uPiTSYjmWlNmooXhjdczjvf+f3OebR33743i8Xi48bYBASBO3WYIaX0xhp1YGj422BbsfhYqVR6PggkiAh39DCwVK+jr6+PA2OtllL63Xff84hnd2N7qRx55+C9B+j2gWQgwZ5hjbEjjx4cO/vhBw8baxsBACGIxMLc/FXVo+ZW6nVcvHAR1eo1qCiCZ94wPgMQREiMwY7yAJ546kksLi8hn++xgoQAiIJVQZJC1RsxjYwcVFGU33vt6p9QSsEzb8pZQQSdJNi16z6USpVf/5icTJxzqz5kwADgncNnn5/jZ59+5t7a7MylqdlZ5LYAONdTwIMP3L/n408+/e3o0WMuCx9zsFZ0GYDWDa8T7bWxzCSIvd8UMAmBxFjWWlOsG+usbwGvVrSQElIGIpCSAynJb7LShRBIpWQpJUkp0RWYmy6nNoUxidVJwgDodkKdJAkbYyi1trvHUkoCgN7eoiiXd4Q6MVAqAnveZKgJSWJQLpexbVtf0BVYSMkAUB4cnHpo//5XKqXtiKIInj0IBCmFA8gDgPc+YOab5oCIYIxBZWAQlbt2TmeX4HXAvllEOwcGlr87f/6j6o3rUErBOYcwDDFQqSCKQgDATK0GrXVGMNwtx5nHjoEjhw/fJMdN4Inx8UptZuat69Uq55SiNE3TfL4nKBR6z8VajwFAtTr1RiOO9wgBD+7M7yQEdJJwEEb05djYaQDTaOOido8JAIzW/StxfHJFa3gGrLVgIjQaK9X3zr7/BQA8d+jQyw2th2/G7UQEbRI04gZ0HL8LYLpLO2VPx94xswWDPTN5wHjmiL2vr3WArzFgGXAAJHWizOxPpoNd2j3ULQsAIoQAmIiIACZQCCKxJkIBAWFT/7ombTOECUSdxp7AOuRutH+rmw29vgbcMmpzbbtxgmnFPMiibq3dgkG8AeD/+vx/gP/tMNpQMjoIt9opTdO2YcE+60ImAL7547Yi9Qx4yu7pFvVMnQp23SKgVD5ihmgTzjUlojZ9PQCLbnTZ8pIzvdTBuBZwGIY4c/o0OfaTuZwazvfkoZSCTFPO5XOkIlV9Z3SUAOD7H358kUkUQMTdPCYiCCGRz+ewuLx87e0zo0REMnOI1nauycm/kpeOH+dCoRB/8/VX4yZuAM7BeYdlHWPH0BAODA1lg+TSpd9rM0sIwrBr46+OxcW5ORw5dQq12hzm6/VV4ebOxYxjx17Y3d/fHxhro9dOvg7v/T+YTkURZBAwAJx49QQ5725NWNnXA80vLHKpVEoX6vWomVIKpBCwqcVPExMXfr58Oct1t9JeXYM2uocxQwiBRhyztQZSCBGQEM57xi9XrtQ7Ef5WHQYQSOkA6mWw+xtsQBSRFyU7hQAAACV0RVh0ZGF0ZTpjcmVhdGUAMjAxMi0xMi0yOFQxMDo1Njo1Ny0wNTowMG9TusYAAAAldEVYdGRhdGU6bW9kaWZ5ADIwMTItMTItMjhUMTA6NTY6NTctMDU6MDAeDgJ6AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAABJRU5ErkJggg==" alt="View" /></span>
  <strong>View</strong>
</span>
{{/toolbar}}