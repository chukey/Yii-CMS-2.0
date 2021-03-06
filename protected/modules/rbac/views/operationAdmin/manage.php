<?
$this->page_title = 'Операции';

$this->widget('AdminGridView', array(
	'id' => 'operations-grid',
	'dataProvider' => $model->search(AuthItem::TYPE_OPERATION),
	'filter'       => $model,
	'columns' => array(
        array(
            'name'  => 'name',
            'value' => '$data->actionExists() ? "$data->name" : "$data->name &nbsp;&nbsp;&nbsp; <span class=\"red_font\">действие не существует</span>"',
            'type'  => 'raw'
        ),
        'description',
        array('name' => 'allow_for_all', 'value' => '$data->allow_for_all ? "Да" : "Нет"'),
		array(
			'class' => 'CButtonColumn',
		),
	),
)); 

