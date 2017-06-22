<section class="sitemap">
  <h2><i class="fa fa-map-signs"></i>
    <a href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/sitemap">Sitemap</a> &raquo; Redirects</h2>
  <nav class="actions">
    <ul>
      <li>
        <a class="btn" href="<?= \library\cc\Request::$subfolders ?><?= $cmsPrefix ?>/sitemap/redirects">Back</a>
      </li>
    </ul>
  </nav>
  <form method="post" class="panel" id="sitemapForm">
    <div class="form-element">
      <label>Title</label>
      <input type="text" name="title" required="required" placeholder="Redirect Title" value="<?=isset($redirect) ? $redirect->title : ''?>" />
    </div>
    <div class="form-element">
      <label for="fromUrl">From Url</label>
      <input required="required" id="fromUrl" type="text" name="fromUrl" placeholder="From Url" value="<?= isset($redirect) ? $redirect->fromUrl : '' ?>"/>
    </div>
    <div class="form-element">
      <label for="toUrl">To Url</label>
      <input required="required" id="toUrl" type="text" name="toUrl" placeholder="To Url" value="<?= isset($redirect) ? $redirect->toUrl : '' ?>"/>
    </div>
    <div class="form-element">
      <label for="redirectType">Type</label>
      <select name="type" id="redirectType">
        <option value="301"<?= isset($redirect) && $redirect->type == '301' ? ' selected="selected"' : '' ?>>Permanent (301)</option>
        <option value="302"<?= isset($redirect) && $redirect->type == '302' ? ' selected="selected"' : '' ?>>Temporarily (302)</option>
      </select>
    </div>
    <div class="form-element">
      <input class="btn" type="submit" value="Save"/>
    </div>
  </form>
</section>