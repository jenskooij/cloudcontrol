<input value="<?=isset($value) ? $value : '' ?>" type="text"<?=$field->required ? ' required="required"' : '' ?> id="<?=$field->slug?>" name="<?=$fieldPrefix?>[<?=$field->slug?>][]" placeholder="<?=$field->title?>" />