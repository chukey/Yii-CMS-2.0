<?
/**
 * @package form
 * @version
 */
class AdminFormInputElement extends BaseFormInputElement
{
    public $layout="{hint}\n{label}\n{input}\n{error}";

    public $widgets = array(
        'alias'             => 'AliasField',
        'file'              => 'FileWidget',
        'captcha'           => 'Captcha',
        'chosen'            => 'Chosen',
        'all_in_one_input'  => 'AllInOneInput',
        'multi_select'      => 'EMultiSelect',
        'date'              => 'FJuiDatePicker',
        'checkbox'          => 'IphoneCheckbox',
        'multi_autocomplete'=> 'MultiAutocomplete',
        'editor'            => 'TinyMCE',
        'elrteEditor'       => 'application.extensions.elrte.elRTE',
        'markdown'          => 'EMarkitupWidget',
        'autocomplete'      => 'zii.widgets.jui.CAutoComplete',
        'meta_tags'         => 'main.portlets.MetaTags',
        'file_manager'      => 'fileManager.portlets.Uploader',
    );


    public function getDefaultWidgetSettings()
    {
        switch ($this->type)
        {
            case 'file_manager':
                $id    = isset($this->id) ? $this->id : 'uploader' . $this->attributes['tag'];
                $title = isset($this->attributes['title']) ? $this->attributes['title'] : 'Файлы';

                return array(
                    'name'        => $this->attributes['tag'],
                    'id'          => $id,
                    'title'       => $title
                );

            case 'date':
                return array(
                    'options'    => array(
                        'dateFormat'=> 'd.m.yy'
                    ),
                    'language'   => 'ru',
                    'htmlOptions'=> array('class'=> 'date text date_picker')
                );

            case 'autocomplete':
                return array(
                    'minChars'   => 2,
                    'delay'      => 500,
                    'matchCase'  => false,
                    'htmlOptions'=> array(
                        'size'  => '40',
                        'class' => 'text'
                    )
                );

            case 'dropdownlist':
                return array(
                    'class' => 'dropdownlist cmf-skinned-select'
                );

            case 'markdown':
                return array(
                    'htmlOptions'=> array(
                        'settings' => 'markdown'
                    )
                );

            default:
                return array();
        }
    }

    public function renderLabel()
    {
        if (in_array($this->type, array('meta_tags', 'file_manager')))
        {
            return '';
        }

        return parent::renderLabel();
    }
}
