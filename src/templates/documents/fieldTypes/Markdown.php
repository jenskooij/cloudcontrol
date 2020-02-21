<textarea<?= $field->required ? ' required="required"' : '' ?> id="<?= $field->slug ?>" name="<?= $fieldPrefix ?>[<?= $field->slug ?>][]" placeholder="<?= $field->title ?>"><?= isset($value) ? $value : '' ?></textarea>
<? if (!isset($GLOBALS['markdownassets'])) : ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.css">
  <script src="https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.js"></script>
    <? $GLOBALS['markdownassets'] = true ?>
<? endif ?>
<script>
  (function () {
    var simplemde = new SimpleMDE({
      element: document.getElementById("<?= $field->slug ?>"),
      spellChecker: false,
      insertTexts: {
        image: ["![](//", ")"],
        link: ["[", "](//)"]
      }
    });
  })();
</script>
