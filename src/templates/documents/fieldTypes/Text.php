<textarea<?= $field->required ? ' required="required"' : '' ?> onfocus="textAreaAdjust(this)" onclick="textAreaAdjust(this)" onkeyup="textAreaAdjust(this)" id="<?= $field->slug ?>" name="<?= $fieldPrefix ?>[<?= $field->slug ?>][]" placeholder="<?= $field->title ?>"><?= isset($value) ? $value : '' ?></textarea>