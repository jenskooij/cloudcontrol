<? function renderAction($title, $class, $href, $icon, $onclick = false) {?>
	<a class="btn <?=$class?>" title="<?=$title?>" href="<?=$href?>"<? if ($onclick !== false) : ?> onclick="<?=$onclick?>"<? endif ?>><i class="fa fa-<?=$icon?>"></i></a>
<?}?>