<div>
  <a data-title="{{#t}}{{!userbox.menu.login}}Sign in{{/t}}" href="#">
    {{#t}}{{!userbox.menu.anonymous}}Anonymous{{/t}} <span class="caret"></span>
  </a>
  <div style="display: none">
    <form class="form" id="form-login" action="" method="post">
      <input type="text" name="_username" value="" placeholder="{{#t}}{{!userbox.form.username}}Username{{/t}}" required="required">
      <input type="password" name="_password" value="" placeholder="{{#t}}{{!userbox.form.password}}Password{{/t}}" required="required">
      <button type="submit">{{#t}}{{!userbox.form.login}}Sign in{{/t}}</button>
    </form>
    <form class="form" action="" method="post">
      <button type="submit">{{#t}}{{!userbox.form.login.facebook}}Sign in with Facebook{{/t}}</button>
    </form>
    <form class="form" action="" method="post">
      <button type="submit">{{#t}}{{!userbox.form.login.twitter}}Sign in with Twitter{{/t}}</button>
    </form>
    <form class="form" action="" method="post">
      <button type="submit">{{#t}}{{!userbox.form.login.google}}Sign in with Google{{/t}}</button>
    </form>
  </div>
</div>
