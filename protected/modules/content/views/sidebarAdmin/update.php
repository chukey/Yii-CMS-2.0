<?

$this->tabs = array(
    'управление сайдбарами' => $this->createUrl('manage'),
    'просмотр'   => $this->createUrl('view', array('id' => $form->model->id))
);

echo $form;