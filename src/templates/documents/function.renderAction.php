<?php /**
 * @param bool $condition
 * @param string $title
 * @param string $class
 * @param string $href
 * @param string $icon
 * @param bool|string $onclick
 */
function renderAction($condition, $title, $class, $href, $icon, $onclick = false)
{ ?>
    <?php if ($condition) : ?>
  <a class="btn <?= $class ?>" title="<?= $title ?>" href="<?= $href ?>"<?php if ($onclick !== false) : ?> onclick="<?= $onclick ?>"<?php endif ?>><i class="fa fa-<?= $icon ?>"></i></a>
<?php endif
    ?>
<?php } ?>