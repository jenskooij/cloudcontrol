<section class="documents valuelists">
  <h2>
    <i class="fa fa-tags"></i>
    Valuelists
  </h2>
  <nav class="actions">
    <ul>
      <li>
        <a class="btn" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/valuelists" title="Back">
          Back
        </a>
      </li>
    </ul>
  </nav>
  <form method="post">
    <div class="form-element">
      <label>Title</label>
      <input type="text" name="title" required="required" placeholder="Valuelist Title" value="<?= isset($valuelist) ? $valuelist->title : '' ?>"/>
    </div>
    <div class="form-element">
      <label for="template">Key Value Pairs</label>
      <ul id="dropZone">
          <?php if (isset($valuelist)) : ?>
              <?php foreach ($valuelist->pairs as $key => $value) : ?>
              <li class="form-element parameters">
                <input type="text" required="required" name="keys[]" placeholder="Key" value="<?= htmlentities($key) ?>"/>&nbsp;
                <input type="text" required="required" name="values[]" placeholder="Value" value="<?= htmlentities($value) ?>"/>
                <a class="btn error" id="sitemap_remove_parameter">
                  <i class="fa fa-trash"></i>
                </a>
              </li>
              <?php endforeach ?>
          <?php endif ?>
      </ul>
      <a class="btn add-parameter" id="sitemap_add_parameter">+</a>
    </div>
    <div class="form-element">
      <input class="btn" type="submit" value="Save"/>
    </div>
  </form>
</section>
<li class="form-element parameters" id="parameterPlaceholder" style="display:none;">
  <input type="text" required="required" name="keys[]" placeholder="Key"/>
  <input type="text" required="required" name="values[]" placeholder="Value"/>
  <a class="btn error" id="sitemap_remove_parameter">
    <i class="fa fa-trash"></i>
  </a>
</li>
<script>
  window.onload = function () {
    "use strict";
    createCloneable('sitemap_add_parameter', 'parameterPlaceholder', 'dropZone');
  };
</script>
