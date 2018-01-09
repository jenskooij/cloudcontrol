<script id="jqueryScript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.4/jquery-ui.js"></script>
<script src="//netdna.bootstrapcdn.com/bootstrap/3.0.1/js/bootstrap.min.js"></script>
<link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css" rel="stylesheet">
<script>var smallestImage = '<?=$smallestImage?>';</script>
<? $copyable = '' ?>
<section class="documents">
  <h2><i class="fa fa-file-text-o"></i> Documents</h2>
  <nav class="actions">
    <ul>
      <li>
        <a id="backButton" class="btn" href="<?= $request::$subfolders ?><?= $cmsPrefix ?>/documents" title="Back">
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
    <? include('document-form-form.php'); ?>
</section>

<script>
  $(function () {
    "use strict";
    $(".sortable").sortable({
      placeholder: "ui-state-highlight",
      axis: "y",
      forcePlaceholderSize: true,
      tolerance: "pointer",
      handle: "a.move",
      stop: function (event, ui) {
        window.onbeforeunload = function (e) {
          return 'You have unsaved changes. Are you sure you want to leave this page?';
        };
      }
    });
    applyDeleteButtons();
    applyAddButtons();
  });
</script>
<div style="display:none;" id="cloneableCollection"></div>
