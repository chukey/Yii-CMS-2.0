<?

/*
 * Контроллер для всяких вспомогательных функций,
 * экшены можно добавить в него один раз, вместо того, что бы добавлять их во все контроллеры
 */
class HelpAdminController extends AdminController
{
    public static function actionsTitles()
    {
        return array(
            'Sortable'         => 'Изменение позиции',
            'ManyManySortable' => 'Изменение позиции для отношений ManyMany',
            'SaveAttribute'    => 'Сохранение Атрибута',
            'Render'           => 'render',
            'Createnode'       => 'createnode',
            'Deletenode'       => 'deletenode',
            'Movenode'         => 'movenode',
            'Copynode'         => 'copynode',
        );
    }
    public function behaviors() {
        return array(
            'EJNestedTreeActions'=>array(
                'class'=>'ext.EJNestedTreeActions.EBehavior',
                'classname'=>'Tree',
                'identity'=>'id',
                'text'=>'id',
            ),
        );
    }

    public function actions()
    {
        return array(
            'sortable'         => 'ext.sortable.SortableAction',
            'manyManySortable' => 'ext.sortable.ManyManySortableAction',
            'saveAttribute'    => 'main.components.SaveAttributeAction',


            'render'           => 'ext.EJNestedTreeActions.actions.Render',
            'createnode'       => 'ext.EJNestedTreeActions.actions.Createnode',
            'renamenode'       => 'ext.EJNestedTreeActions.actions.Renamenode',
            'deletenode'       => 'ext.EJNestedTreeActions.actions.Deletenode',
            'movenode'         => 'ext.EJNestedTreeActions.actions.Movenode',
            'copynode'         => 'ext.EJNestedTreeActions.actions.Copynode',
        );
    }
}
