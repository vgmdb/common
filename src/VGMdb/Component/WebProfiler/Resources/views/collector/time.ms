{{#panel}}
<div id="collector-content">
{{#collector.data}}
<h2>Timeline</h2>
{{?events}}
<form id="timeline-control" action="" method="get">
  <input type="hidden" name="panel" value="time" />
  <table>
    <tr>
      <th style="width: 20%">Total time</th>
      <td>{{collector.duration}} ms</td>
    </tr>
    <tr>
      <th>Initialization time</th>
      <td>{{collector.inittime}} ms</td>
    </tr>
    <tr>
      <th>Threshold</th>
      <td>
        <input type="number" size="3" name="threshold" value="1" min="0" /> ms
        <input style="margin-left: 20px" type="checkbox" name="namespaces" id="namespaces" value="1" checked="checked" /><label for="namespaces"> Show namespaces</label>
      </td>
    </tr>
  </table>
</form>
<h3>{{^profile.parent}}Main {{/profile.parent}}Request <small>- {{events.__section__.duration}} ms</small></h3>

<div class="sf-profiler-timeline">
  <div id="legend_{{token}}" class="legends"></div>
  <canvas width="680" height="" id="timeline_{{token}}" class="timeline"></canvas>
</div>

<script type="text/javascript">//<![CDATA[
    /**
     * In-memory key-value cache manager
     */
    var cache = new function() {
        "use strict";
        var dict = {};

        this.get = function(key) {
            return dict.hasOwnProperty(key)
                ? dict[key]
                : null;
            }

        this.set = function(key, value) {
            dict[key] = value;

            return value;
        }
    };

    /**
     * Query an element with a CSS selector.
     *
     * @param  string selector a CSS-selector-compatible query string.
     *
     * @return DOMElement|null
     */
    function query(selector)
    {
        "use strict";
        var key = 'SELECTOR: ' + selector;

        return cache.get(key) || cache.set(key, document.querySelector(selector));
    }

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

    /**
     * Canvas Manager
     */
    function CanvasManager(requests, maxRequestTime) {
        "use strict";

        var _drawingColors  = {
                'default':                '#aacd4e',
                'section':                '#666',
                'boot':                   '#ccc',
                'event_listener':         '#3dd',
                'event_listener_loading': '#add',
                'template':               '#dd3',
                'guzzle':                 '#f91',
                'doctrine':               '#d3d',
                'propel':                 '#f4d',
                'child_sections':         '#eed'
            },
            _storagePrefix  = 'timeline/',
            _threshold      = 1,
            _namespaces     = 1,
            _requests       = requests,
            _maxRequestTime = maxRequestTime,
            _disabledCategories = [];

        /**
         * Check whether this event is a child event.
         *
         * @return true if it is.
         */
        function isChildEvent(event)
        {
            return '__section__.child' === event.name;
        }

        /**
         * Check whether this event is categorized in 'section'.
         *
         * @return true if it is.
         */
        function isSectionEvent(event)
        {
            return 'section' === event.category;
        }

        /**
         * Get the width of the container.
         */
        function getContainerWidth()
        {
            return query('#collector-content h2').clientWidth;
        }

        /**
         * Draw one canvas.
         *
         * @param request            the request object
         * @param max                <subjected for removal>
         * @param threshold          the threshold (lower bound) of the length of the timeline (in milliseconds).
         * @param namespaces         whether to display namespaces.
         * @param disabledCategories categories to ignore.
         * @param width              the width of the canvas.
         */
        this.drawOne = function(request, max, threshold, namespaces, disabledCategories, width)
        {
            "use strict";
            var text,
                ms,
                xc,
                drawableEvents,
                mainEvents,
                elementId    = 'timeline_' + request.id,
                canvasHeight = 0,
                gapPerEvent  = 38,
                colors = _drawingColors,
                space  = 10.5,
                ratio  = (width - space * 2) / max,
                h = space,
                x = request.left * ratio + space, // position
                canvas = cache.get(elementId) || cache.set(elementId, document.getElementById(elementId)),
                ctx    = canvas.getContext("2d");

            // Filter events whose total time is below the threshold or whose category is disabled.
            drawableEvents = request.events.filter(function(event) {
                return event.duration >= threshold && disabledCategories.indexOf(event.category) == -1;
            });

            canvasHeight += gapPerEvent * drawableEvents.length;

            canvas.width  = width;
            canvas.height = canvasHeight;

            ctx.textBaseline = "middle";
            ctx.lineWidth = 0;

            // For each event, draw a line.
            ctx.strokeStyle = "#dfdfdf";

            drawableEvents.forEach(function(event) {
                event.periods.forEach(function(period) {
                    var timelineHeadPosition = x + period.start * ratio;

                    if (isChildEvent(event)) {
                        ctx.fillStyle = colors.child_sections;
                        ctx.fillRect(timelineHeadPosition, 0, (period.end - period.start) * ratio, canvasHeight);
                    } else if (isSectionEvent(event)) {
                        var timelineTailPosition = x + period.end * ratio;

                        ctx.beginPath();
                        ctx.moveTo(timelineHeadPosition, 0);
                        ctx.lineTo(timelineHeadPosition, canvasHeight);
                        ctx.moveTo(timelineTailPosition, 0);
                        ctx.lineTo(timelineTailPosition, canvasHeight);
                        ctx.fill();
                        ctx.closePath();
                        ctx.stroke();
                    }
                });
            });

            // Filter for main events.
            mainEvents = drawableEvents.filter(function(event) {
                return ! isChildEvent(event)
            });

            // For each main event, draw the visual presentation of timelines.
            mainEvents.forEach(function(event) {

                h += 8;

                // For each sub event, ...
                event.periods.forEach(function(period) {
                    // Set the drawing style.
                    ctx.fillStyle   = colors['default'];
                    ctx.strokeStyle = colors['default'];

                    if (colors[event.name]) {
                        ctx.fillStyle   = colors[event.name];
                        ctx.strokeStyle = colors[event.name];
                    } else if (colors[event.category]) {
                        ctx.fillStyle   = colors[event.category];
                        ctx.strokeStyle = colors[event.category];
                    }

                    // Draw the timeline
                    var timelineHeadPosition = x + period.start * ratio;

                    if ( ! isSectionEvent(event)) {
                        ctx.fillRect(timelineHeadPosition, h + 3, 2, 6);
                        ctx.fillRect(timelineHeadPosition, h, (period.end - period.start) * ratio || 2, 6);
                    } else {
                        var timelineTailPosition = x + period.end * ratio;

                        ctx.beginPath();
                        ctx.moveTo(timelineHeadPosition, h);
                        ctx.lineTo(timelineHeadPosition, h + 11);
                        ctx.lineTo(timelineHeadPosition + 8, h);
                        ctx.lineTo(timelineHeadPosition, h);
                        ctx.fill();
                        ctx.closePath();
                        ctx.stroke();

                        ctx.beginPath();
                        ctx.moveTo(timelineTailPosition, h);
                        ctx.lineTo(timelineTailPosition, h + 11);
                        ctx.lineTo(timelineTailPosition - 8, h);
                        ctx.lineTo(timelineTailPosition, h);
                        ctx.fill();
                        ctx.closePath();
                        ctx.stroke();

                        ctx.beginPath();
                        ctx.moveTo(timelineHeadPosition, h);
                        ctx.lineTo(timelineTailPosition, h);
                        ctx.lineTo(timelineTailPosition, h + 2);
                        ctx.lineTo(timelineHeadPosition, h + 2);
                        ctx.lineTo(timelineHeadPosition, h);
                        ctx.fill();
                        ctx.closePath();
                        ctx.stroke();
                    }
                });

                h += 30;

                ctx.beginPath();
                ctx.strokeStyle = "#dfdfdf";
                ctx.moveTo(0, h - 10);
                ctx.lineTo(width, h - 10);
                ctx.closePath();
                ctx.stroke();
            });

            h = space;

            // For each event, draw the label.
            mainEvents.forEach(function(event) {

                ctx.fillStyle = "#444";
                ctx.font = "12px sans-serif";
                text = event.name;

                if (namespaces !== 1) {
                    text = text.split('\\');
                    text = text.pop();
                }

                ms = " ~ " + (event.duration < 1 ? event.duration : parseInt(event.duration, 10)) + " ms / ~ " + event.memory + " MB";
                if (x + event.starttime * ratio + ctx.measureText(text + ms).width > width) {
                    ctx.textAlign = "end";
                    ctx.font = "10px sans-serif";
                    xc = x + event.endtime * ratio - 1;
                    ctx.fillText(ms, xc, h);

                    xc -= ctx.measureText(ms).width;
                    ctx.font = "12px sans-serif";
                    ctx.fillText(text, xc, h);
                } else {
                    ctx.textAlign = "start";
                    ctx.font = "12px sans-serif";
                    xc = x + event.starttime * ratio + 1;
                    ctx.fillText(text, xc, h);

                    xc += ctx.measureText(text).width;
                    ctx.font = "10px sans-serif";
                    ctx.fillText(ms, xc, h);
                }

                h += gapPerEvent;
            });
        };

        this.drawAll = function(width, threshold, namespaces, disabledCategories)
        {
            "use strict";

            width      = width || getContainerWidth();
            threshold  = threshold || this.getThreshold();
            namespaces = namespaces || this.getNamespaces();
            disabledCategories = disabledCategories || this.getDisabledCategories();

            var self = this;

            _requests.forEach(function(request) {
                self.drawOne(request, maxRequestTime, threshold, namespaces, disabledCategories, width);
            });
        };

        this.getThreshold = function()
        {
            var threshold = Sfjs.getPreference(_storagePrefix + 'threshold');

            if (threshold === null) {
                return _threshold;
            }

            _threshold = parseInt(threshold);

            return _threshold;
        };

        this.setThreshold = function(threshold)
        {
            _threshold = threshold;

            Sfjs.setPreference(_storagePrefix + 'threshold', threshold);

            return this;
        };

        this.getNamespaces = function()
        {
            var namespaces = Sfjs.getPreference(_storagePrefix + 'namespaces');

            if (namespaces === null) {
                return _namespaces;
            }

            _namespaces = parseInt(namespaces);

            return _namespaces;
        };

        this.setNamespaces = function(namespaces)
        {
            _namespaces = namespaces;

            Sfjs.setPreference(_storagePrefix + 'namespaces', namespaces);

            return this;
        };

        this.getDisabledCategories = function()
        {
            var disabledCategories = JSON.parse(Sfjs.getPreference(_storagePrefix + 'disabledCategories'));

            if (disabledCategories === null) {
                return _disabledCategories;
            }

            _disabledCategories = disabledCategories;

            return _disabledCategories;
        };

        this.setDisabledCategories = function(disabledCategories)
        {
            _disabledCategories = disabledCategories;

            Sfjs.setPreference(_storagePrefix + 'disabledCategories', JSON.stringify(disabledCategories));

            return this;
        };

        this.drawLegends = function()
        {
            "use strict";

            var colors = _drawingColors;
            var disabledCategories = this.getDisabledCategories();

            _requests.forEach(function(request) {
                var elementId = 'legend_' + request.id;

                for (var category in colors) {
                    var legend = document.createElement('span');
                    if (disabledCategories.indexOf(category) != -1) {
                        legend.className = 'disabled';
                    }
                    var toggle = document.createElement('a');
                    toggle.setAttribute('data-category', category);
                    toggle.setAttribute('href', '#');
                    toggle.appendChild(document.createTextNode(category));
                    legend.appendChild(toggle);
                    legend.style.borderLeftColor = colors[category];

                    document.getElementById(elementId).appendChild(legend);
                }
            });
        }
    };

    function canvasAutoUpdateOnResizeAndSubmit(e) {
        e.preventDefault();
        canvasManager.drawAll();
    }

    function canvasAutoUpdateOnThresholdChange(e) {
        canvasManager
            .setThreshold(query('input[name="threshold"]').value)
            .drawAll();
    }

    function canvasAutoUpdateOnNamespaceChange(e) {
        canvasManager
            .setNamespaces(query('input[name="namespaces"]').checked ? 1 : 0)
            .drawAll();
    }

    function canvasAutoUpdateOnCategoryToggle(e) {
        e.preventDefault();
        var element = e.target;
        var span = element.parentNode;
        span.className = (span.className == 'disabled') ? '' : 'disabled';

        var disabledCategories = [];
        var elementDisabledCategories = queryAll('.legends > span.disabled');
        for (var i = 0; i < elementDisabledCategories.length; ++i) {
            disabledCategories.push(elementDisabledCategories[i].innerText);
        }

        canvasManager
            .setDisabledCategories(disabledCategories)
            .drawAll();
    }

    var requests_data = {
        "max": {{events.__section__.endtime}},
        "requests": [
            {
                "id": "{{token}}",
                "left": 0,
                "events": {{{events_json}}}
            }
        ]
    };

    var canvasManager = new CanvasManager(requests_data.requests, requests_data.max);

    query('input[name="threshold"]').value = canvasManager.getThreshold();
    query('input[name="namespaces"]').checked = canvasManager.getNamespaces();
    canvasManager.drawAll();
    canvasManager.drawLegends();

    // Bind event handlers
    var elementTimelineControl  = query('#timeline-control'),
        elementThresholdControl = query('input[name="threshold"]'),
        elementNamespaceControl = query('input[name="namespaces"]'),
        elementCategoryToggle   = queryAll('a[data-category]');

    window.onresize                 = canvasAutoUpdateOnResizeAndSubmit;
    elementTimelineControl.onsubmit = canvasAutoUpdateOnResizeAndSubmit;

    elementThresholdControl.onclick  = canvasAutoUpdateOnThresholdChange;
    elementThresholdControl.onchange = canvasAutoUpdateOnThresholdChange;
    elementThresholdControl.onkeyup  = canvasAutoUpdateOnThresholdChange;

    elementNamespaceControl.onchange = canvasAutoUpdateOnNamespaceChange;

    for (var i = 0; i < elementCategoryToggle.length; ++i) {
        elementCategoryToggle[i].onclick = canvasAutoUpdateOnCategoryToggle;
    }

    window.setTimeout(function() {
        canvasAutoUpdateOnThresholdChange(null);
    }, 50);

//]]></script>

{{/events}}
{{^events}}
<p><em>No timing events have been recorded. Are you sure that debugging is enabled in the kernel?</em></p>
{{/events}}
{{/collector.data}}
</div>
{{/panel}}
{{#toolbar}}
<span class="label">
  <span class="icon"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAhCAYAAADOHBvaAAACz0lEQVR42t2XXWhSYRjHc+pyWrC10e66qKggiGoF0Qh1SBPFDxCcX00yrG6EImFsRhAuvUjwQgZB1EUICYEkgRJGB0QmKMNGEYx1URYEg2w6amWY/YUXXH7Ms9M5RAk/PByf8/58P573ec+2Wq32V/g3xFKpdB3UQBV8JVTJvXUuxbXN+P96TODNzMzsNJvNo3UCgYCYszmmKKrPYrE4NBrNU7lcvtY8xGNjYyWtVvvEZrPZES9kQyy02+3nx8fH3xFJV5RK5RuHw2GqP8tInEgk+g0GwyN6wlZMJtPdfD6/Y0viVCo1iKHLMpM20Ov1FNqT0BWLsWhiXdImDyiQ7ybHVN1HnKibWHgNHxr5egXI6t90ej49PX0BsYKO4qWlpT1qtXqFhlhGnpHREWPaPiB2dydxL/7ZTRLMnpjg8/lcpNct4mGr1fqSK7HT6UwhfleLuFwuH1EoFBWuxDqd7jPiDzSLe+Lx+AQJYl9MKBQKZ+pb7kaxKBKJXGUgPgqCgGrQOc3S6fQEfuNvFEtisdgUA3E/kcsadE6zhYUFG9lKG2LcvMxA3EKnKUBx+bm6uqptFvcCtUql+kECWcdoNH6BQw56fltcYNTtdr/nSuzxeJbhONkujw9Fo9EYV+JkMvkAjv3txEOVSuUSytk3tqWoz2to/xwYaCcWgtPI5+dsSrGo6mn0GG2PtG6ZDYbBRb/f/5EtcSgUeos2J8HgpmURHMaQz6JglP5U6vV6P6G962Sr5HcUE/rAKXB7bm5uhenwhsPheim8BUa6HwQaSMjSv5HNZl+gstDOb5fL9X1xcTGLZ6fAMSDe6ilTBA4CO7iXyWReB4PBMo5F1WYZymkVc1nK5XKvEHsHWMA+sJ3p8VYAhkjvJ4EPRMCzYrE4XyqV5nGdBA/BLLCC46Tu8tl4kxCAAdKLE0AGzhKkZB73kqIh4PI1lQf4BB6TNn4B8KR3FN9bp4MAAAAASUVORK5CYII=" alt="Timeline" /></span>
  <strong>Timeline</strong>
</span>
{{/toolbar}}