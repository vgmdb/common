<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="robots" content="noindex,nofollow" />
    <title>Profiler</title>
    <link rel="icon" type="image/x-icon" sizes="16x16" href="data:image/ico;base64,AAABAAEAEBAAAAEAIABoBAAAFgAAACgAAAAQAAAAIAAAAAEAIAAAAAAAAAQAABMLAAATCwAAAAAAAAAAAAAAAAAAAAAAADIvMQAyLzEIMi8xSzEuMKoyLzHkMi8x/TIvMf0yLzHlMi8xrDIvMU4yLzEJMi8xAAAAAAAAAAAAAAAAADIvMQAyLzEYMS4wkTMwMu45Njj/MS4w/zEuMP8yLzH/Mi8x/zIvMf8yLzHvMi8xlDIvMRkyLzEAAAAAADIvMQAyLzEXMS4wrTk2OPyVk5T7kI6P/nl3ef8+Oz3/MS4w/zIvMf8yLzH/Mi8x/zIvMf4yLzGxMi8xGjIvMQAyLzEGMi8xkDEuMP4/PD79wcDA+oB+gP6Ni4z/paOk/zk2OP8xLjD/Mi8x/zIvMf8yLzH/Mi8x/zIvMZQyLzEIMi8xSTIvMewyLzH/MS4w/z06PP81MjT+TktN/93c3f97eXv/MC0v/zIvMf8yLzH/Mi8x/zIvMf8yLzHuMi8xTzIvMaUyLzH/Mi8x/0lHSf9kYmP/XFpb/zs4Ov/DwsL+ycjI/zs4Ov8xLjD/Mi8x/zIvMf8yLzH/Mi8x/zIvMawyLzHfMC0v/1tYWv+opqf/YV5g/8bFxf96eHn+m5qb/u7u7v9WVFX/MC0v/zIvMf8yLzH/Mi8x/zIvMf8yLzHkMi8x+jAtL/9iYGH/mZiZ/2dlZv/p6On/oJ+g/np4ev/6+vr/dXN1/y0qLP8xLjD/Mi8x/zIvMf8yLzH/Mi8x/DIvMfoyLzH/MzAy/0A+QP7JyMj85eXl/1tYWv9XVVf/8fDx/6qpqv9ZV1j/Q0BC/zIvMf8yLzH/Mi8x/zIvMfwyLzHeMi8x/zEuMP8/PD762dnZ9JWTlP81MjT/ZmRm/+Dg4P/DwsP/YV5g/6Wkpf9BPkD/MS4w/zIvMf8yLzHjMi8xozIvMf8yLzH/Mi8x/nZ0dv2amJn4dXN0+V5bXf+Pjo//0tLS/0hFR/9vbG7/Ozg6/zEuMP8yLzH/Mi8xqTIvMUcyLzHrMi8x/zIvMf8xLjD/Ozg6/Do3OfwwLS//REFD/728vP9nZWb/TktN/4mIif05Nzn/Mi8x7jMwMkwyLzEGMi8xjDIvMf4yLzH/Mi8x/zIvMf8yLzH/Mi8x/zAtL/9XVFb/goGC+Hx6e+6qqanwOzg6/DMwMpJDQEIIMi8xADIvMRUyLzGrMi8x/jIvMf8yLzH/Mi8x/zIvMf8yLzH/MS4w/zg2N/xBPkD3OTY4/DIvMa8yLzEYMi8xAAAAAAAyLzEAMi8xGDIvMY0yLzHqMi8x/zIvMf8yLzH/Mi8x/zIvMf8yLzH/MS4w7DEuMJEyLzEaMi8xAAAAAAAAAAAAAAAAADIvMQAyLzEGMi8xQzIvMZ4yLzHdMi8x+jIvMfoyLzHeMi8xoDIvMUUyLzEGMi8xAAAAAAAAAAAA4AcAAMADAACAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAIABAADAAwAA4AcAAA==" />
    {{>@WebProfiler/layouts/base_css}}
    <script type="text/javascript">/*<![CDATA[*/
    Sfjs = (function() {
        "use strict";

        var noop = function() {},

            profilerStorageKey = 'sf2/profiler/',

            request = function(url, onSuccess, onError, payload, options) {
                var xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
                options = options || {};
                xhr.open(options.method || 'GET', url, true);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.onreadystatechange = function(state) {
                    if (4 === xhr.readyState && 200 === xhr.status) {
                        (onSuccess || noop)(xhr);
                    } else if (4 === xhr.readyState && xhr.status != 200) {
                        (onError || noop)(xhr);
                    }
                };
                xhr.send(payload || '');
            },

            hasClass = function(el, klass) {
                return el.className.match(new RegExp('\\b' + klass + '\\b'));
            },

            removeClass = function(el, klass) {
                el.className = el.className.replace(new RegExp('\\b' + klass + '\\b'), ' ');
            },

            addClass = function(el, klass) {
                if (!hasClass(el, klass)) { el.className += " " + klass; }
            },

            getPreference = function(name) {
                if (!window.localStorage) {
                    return null;
                }

                return localStorage.getItem(profilerStorageKey + name);
            },

            setPreference = function(name, value) {
                if (!window.localStorage) {
                    return null;
                }

                localStorage.setItem(profilerStorageKey + name, value);
            };

        return {
            hasClass: hasClass,
            removeClass: removeClass,
            addClass: addClass,
            getPreference: getPreference,
            setPreference: setPreference,
            request: request,
            load: function(selector, url, onSuccess, onError, options) {
                var el = document.getElementById(selector);

                if (el && el.getAttribute('data-sfurl') !== url) {
                    request(
                        url,
                        function(xhr) {
                            el.innerHTML = xhr.responseText;
                            el.setAttribute('data-sfurl', url);
                            removeClass(el, 'loading');
                            (onSuccess || noop)(xhr, el);
                        },
                        function(xhr) { (onError || noop)(xhr, el); },
                        options
                    );
                }

                return this;
            },
            toggle: function(selector, elOn, elOff) {
                var i,
                    style,
                    tmp = elOn.style.display,
                    el = document.getElementById(selector);

                elOn.style.display = elOff.style.display;
                elOff.style.display = tmp;

                if (el) {
                    el.style.display = 'none' === tmp ? 'none' : 'block';
                }

                return this;
            }
        }
    })();
    /*]]>*/</script>
  </head>
  <body>
    <div id="content">
      <div id="header" class="clear-fix">
        <h1><img src="data:image/png;base64,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" alt="Symfony profiler"/></h1>
      </div>
      <div id="main">
        {{{content}}}
      </div>
    </div>
  </body>
</html>