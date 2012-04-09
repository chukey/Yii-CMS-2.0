<?

class Tree extends ActiveRecord
{
    public function name()
    {
        return 'Древовидная структура';
    }

	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function tableName()
	{
		return 'tree';
	}

    public function behaviors()
    {
        return array_merge(parent::behaviors(), array(
            'Tree' => 'application.components.ActiveRecordBehaviors.NestedSetBehavior'
        ));
    }
}
