<section class="documents">
  <h2><i class="fa fa-file-text-o"></i> Documents</h2>
  <nav class="actions">
    <ul>
      <li>
        <a class="btn" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/documents" title="Back">
          Back
        </a>
      </li>
    </ul>
  </nav>
  <ul class="documents grid-wrapper">
    <li class="grid-container">
      <div class="grid-box-12">
        <i class="fa fa-terminal" title="Path"></i>
        <i id="pathHolder"><?= $request::$get['path'] ?></i>
      </div>
    </li>
  </ul>
  <form method="post">
    <input type="hidden" name="path" value="<?= $request::$get['path'] ?>"/>
    <textarea style="display:none;" name="content"><?= isset($folder) ? json_encode($folder->content) : '[]' ?></textarea>
    <div class="form-element">
      <label>Title</label>
      <input type="text" name="title" placeholder="Folder Title" value="<?= isset($folder) ? $folder->title : '' ?>"/>
    </div>
    <div class="form-element">
      <input class="btn" type="submit" value="Save"/>
    </div>
  </form>
</section>
