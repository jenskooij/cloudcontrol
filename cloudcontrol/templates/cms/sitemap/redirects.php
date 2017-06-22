<section class="sitemap">
  <h2><i class="fa fa-map-signs"></i>
    <a href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/sitemap">Sitemap</a> &raquo; Redirects</h2>
  <nav class="actions">
    <ul>
      <li>
        <a class="btn" href="<?= \library\cc\Request::$subfolders ?><?= $cmsPrefix ?>/sitemap/redirects/new" title="New Redirect">
          + <i class="fa fa-random"></i>
        </a>
      </li>
    </ul>
  </nav>
  <ul class="sitemap">
      <? foreach ($redirects as $redirect) : ?>
        <li>
          <h3><?=$redirect->title?> (<?=$redirect->type?>)</h3>
          <span class="url"><?=$redirect->fromUrl?></span>
          <span class="url"><?=$redirect->toUrl?></span>
          <a onclick="return confirm('Are you sure you want to delete this item?');" class="btn error" href="<?=\library\cc\Request::$subfolders?><?=$cmsPrefix?>/sitemap/redirects/delete?slug=<?=$redirect->slug?>" title="Delete"><i class="fa fa-trash"></i></a>
          <a class="btn" href="<?=\library\cc\Request::$subfolders?><?=$cmsPrefix?>/sitemap/redirects/edit?slug=<?=$redirect->slug?>" title="Edit"><i class="fa fa-pencil"></i></a>
        </li>
      <? endforeach ?>
  </ul>
</section>