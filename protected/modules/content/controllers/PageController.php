<?

class PageController extends Controller
{
    public static function actionsTitles()
    {
        return array(
            "View"   => "Просмотр страницы",
            "Main"   => "Главная страница",
            "Search" => "Поиск"
        );
    }


    public function actionView()
    {
        $id = $this->request->getParam("id");
        if ($id)
        {
            $page = Page::model()->published()->findByPk($id);
            if (!$page || mb_strlen($page->url, 'utf-8') > 0)
            {
                $this->pageNotFound();
            }
        }
        else
        {
            $url  = $this->request->getParam("url");
            $page = Page::model()->language()->published()->findByAttributes(array("url" => $url));
            if (!$page)
            {
                $this->pageNotFound();
            }
        }

        $this->crumbs = array($page->title);
        $this->_setMetaTags($page);

        $this->render("view", array("page" => $page));
    }


    public function actionMain()
    {
        $page = Page::model()->published()->findByAttributes(array("url" => "/"));
        if (!$page)
        {
            $this->pageNotFound();
        }

        $this->_setMetaTags($page);

        $this->render('main', array(
            'page' => $page
        ));
    }

    private function _setMetaTags(Page $page)
    {
        $meta_tags = $page->metaTags();

        $this->meta_title       = $meta_tags['title'];
        $this->meta_keywords    = $meta_tags['keywords'];
        $this->meta_description = $meta_tags['description'];
    }


    public function actionSearch($query)
    {
        $query = trim(strip_tags($query));
        if (mb_strlen($query, 'utf-8') >= 3)
        {
            $criteria = new CDbCriteria();
            $criteria->addSearchCondition('title', $query, true, 'OR');
            $criteria->addSearchCondition('short_text', $query, true, 'OR');
            $criteria->addSearchCondition('text', $query, true, 'OR');

            $pages = Page::model()->findAll($criteria);
        }

        $this->render('search', array(
            'pages' => isset($pages) ? $pages : null
        ));
    }


    public function actionCreate()
    {
        $model = new Page(ActiveRecord::SCENARIO_CREATE);
        $model->disableBehavior('FileManager');
        $model->disableBehavior('MetaTag');

        $form = new Form('content.PageForm', $model);
        unset($form->elements['url']);

        $this->performAjaxValidation($model);

        if ($form->submitted() && $model->save())
        {
            $this->redirect(array(
                'view',
                'id' => $model->id
            ));
        }

        $this->render('create', array('form' => $form));
    }
}
