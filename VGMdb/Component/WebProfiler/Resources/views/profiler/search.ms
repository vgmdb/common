<div class="search clearfix" id="searchBar">
    <h3>
        <img style="margin: 0 5px 0 0; vertical-align: middle;" width="16" height="16" alt="Search" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAC2ElEQVR42u3XvUtbYRQG8JcggSxSiqlQoXUQUfEDnDoIEkK30ulKh0REFEOkitaIaBUU4gchQ8BBGyKGIC79B7rVxaGlm+JooYtQCq2U0oq9OX0eOBeCRXrf69DFwI9z73nPeTlJbrxXIyL/1e0AfyWueTVAEgrwGt5qLGge675e1gPUQaqxsfEgmUyerq6uft3e3v61v78vjDxnnuusYz3WTI0bDXAnHA6/Gh0d/bS7u+vu7e3JdbjOOtazDzmjAg9QF41Gy+vr6+eVSkX8Yj372I9zA8EGiEQi6bW1tfNyuSyK7/II0YEmMBodzYuHfezXmkADNAwNDX3c2dkRKpVKl4hZCIO5SvNZ1LleD/uxzz0c2w/Q0tLyAheYWywWRT0H4wPrhNjf1taWYd56gOHh4XdbW1tC+Xz+CNH4pfVCuo/9AAsLC182NzeFVlZWUojGL60X0n3sB8BFdFEoFISWlpYeIhq/tF5I97EfIJfLufgohZqbm+8jGr+0Xkj3sR9geXn5x8bGhlBHR8czROOX1gvpPvYDzM3NnWSzWaFYLPYG0fil9UK6j/0As7OzWVxMQul0+ht6nuDY/AvrWO/16j7WA/BCerC4uHiJKNTX13eid7wQzs1VzHOddV4P+n9zHwj0l5BfQ35+fl4Ix248Hj8NhUIlLPXDXeQNI8+Z5zrrvJ6BgYEzrMVxHGgAfg3hmZmZD4jiwd3ue3d393F9ff0hnwcYec4812tlMplqb2/vMepigW/H09PTUXgPEsTU1FS1p6dHhwj4QDI5ORmBHFyAXEfXK+DW5icmJqpdXV21Q9g/ko2Pj1MTvIQDOAPReKD5Jq1zwAVR/CVVOzs7OUR/oAFSqZQtB1wQz9jYWLW9vf0I2z2yHgAXWRAOuCCekZGRamtr66HtALw9B+WAC+JJJBI/rQfA081NOOCCEJ6gP1sPMDg4eFNP4Uw9vv3X7HaAq/4AszA5PJFqlwgAAAAASUVORK5CYII=" />
        Search
    </h3>
    <form action="{{urls.search}}" method="get">
        <label for="ip">IP</label>
        <input type="text" name="ip" id="ip" value="{{ip}}" />
        <div class="clear-fix"></div>
        <label for="method">Method</label>
        <select name="method" id="method">
            <option></option>
            <option>GET</option>
            <option>POST</option>
            <option>PUT</option>
            <option>DELETE</option>
            <option>PATCH</option>
            <option>HEAD</option>
        </select>
        <div class="clear-fix"></div>
        <label for="url">URL</label>
        <input type="text" name="url" id="url" value="{{url}}" />
        <div class="clear-fix"></div>
        <label for="token">Token</label>
        <input type="text" name="token" id="token" value="{{token}}" />
        <div class="clear-fix"></div>
        <label for="start">From</label>
        <input type="text" name="start" id="start" value="{{start}}" />
        <div class="clear-fix"></div>
        <label for="end">Until</label>
        <input type="text" name="end" id="end" value="{{end}}" />
        <div class="clear-fix"></div>
        <label for="limit">Limit</label>
        <select name="limit" id="limit">
            <option>10</option>
            <option>20</option>
            <option>50</option>
        </select>
        <button type="submit" class="sf-button">
            <span class="border-l">
                <span class="border-r">
                    <span class="btn-bg">SEARCH</span>
                </span>
            </span>
        </button>
        <div class="clear-fix"></div>
    </form>
</div>