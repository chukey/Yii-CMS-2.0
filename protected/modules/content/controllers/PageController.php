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
        Y::end(3);
        require_once Yii::getPathOfAlias('ext.Github.Autoloader').'.php';
        Yii::registerAutoloader(array('Github_Autoloader','autoload'), true);
        $github = new Github_Client();
        $tags = $github->getRepoApi()->getRepoTags('nizsheanez', 'documentation');
        $version = key(array_slice($tags,0,1));
        $cur_v = '0.0.1';

        if (version_compare($version, $cur_v, '>'))
        {
            $target = Yii::getPathOfAlias('application.runtime').'/';
            $src = 'https://raw.github.com/ostapetc/Yii-CMS-2.0/tree_and_update/protected/';
            $file = $version.'.phar';
            file_put_contents($target.$file, file_get_contents($src.$file));
            $p = new Phar($target.$file);
            $p->extractTo(Yii::getPathOfAlias('application.runtime'));
        }
Y::end();


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
}
