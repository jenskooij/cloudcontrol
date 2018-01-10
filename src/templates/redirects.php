<section class="redirects sitemap">
  <h2><i class="fa fa-random"></i> Redirects</h2>
  <nav class="actions">
    <ul>
      <li>
        <a class="btn" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/redirects/new" title="New Redirect">
          +
        </a>
      </li>
    </ul>
  </nav>
  <ul class="redirects sitemap">
      <?php foreach ($redirects as $redirect) : ?>
        <li>
          <div class="grid-box-8">
            <h3><?= $redirect->title ?> (<?= $redirect->type ?>)</h3>
            <span class="url"><?= $redirect->fromUrl ?></span>
            <span class="url"><?= $redirect->toUrl ?></span>
          </div>
          <div class="grid-box-4 documentActions">
            <a onclick="return confirm('Are you sure you want to delete this item?');" class="btn error" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/redirects/delete?slug=<?= $redirect->slug ?>" title="Delete"><i class="fa fa-trash"></i></a>
            <a class="btn" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/redirects/edit?slug=<?= $redirect->slug ?>" title="Edit"><i class="fa fa-pencil"></i></a>
          </div>
        </li>
      <?php endforeach ?>
  </ul>
</section>