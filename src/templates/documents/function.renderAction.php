<?
/**
 * @param bool $condition
 * @param string $title
 * @param string $class
 * @param string $href
 * @param string $icon
 * @param bool $onclick
 */
function renderAction($condition, $title, $class, $href, $icon, $onclick = false)
{ ?>
    <? if ($condition) : ?>
  <a class="btn <?= $class ?>" title="<?= $title ?>" href="<?= $href ?>"<? if ($onclick !== false) : ?> onclick="<?= $onclick ?>"<? endif ?>><i class="fa fa-<?= $icon ?>"></i></a>
<? endif
    ?>
<? } ?>