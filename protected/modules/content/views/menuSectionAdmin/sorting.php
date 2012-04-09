<?
$this->page_title = 'Сортировка пунктов меню';

$this->tabs = array(
    'Управление пунктами меню'  => $this->createUrl("manage", array('menu_id'=> $menu_id)),
);

?>

<div class="sortable-list">
    <?
    $this->widget('content.portlets.NestedSortable', array(
        'model'    => MenuSection::model(),
        'sortable' => true,
        'root_id'  => $root_id,
        'id'       => 'menu_section_sorting'
    ));
    ?>
</div>