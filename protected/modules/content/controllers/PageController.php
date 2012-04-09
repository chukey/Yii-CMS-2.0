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
        require_once Yii::getPathOfAlias('ext.Github.Autoloader').'.php';
        Yii::registerAutoloader(array('Github_Autoloader','autoload'), true);
        $github = new Github_Client();
        $tags = $github->getRepoApi()->getRepoTags('nizsheanez', 'documentation');
        $version = key(array_slice($tags,0,1));
        $cur_v = '0.0.1';

        if (version_compare($version, $cur_v, '>'))
        {
            $tag = 'https://github.com/nizsheanez/documentation/zipball/'.$version;
            $base = Yii::getPathOfAlias('application.runtime').'/';
            $src = $base.$version.'.zip';
//            file_put_contents($src, file_get_contents($tag));
            $target = $base.$version;

            $pp = Yii::getPathOfAlias('application.components');
            $p = new Phar('new.tar.phar');
            $p->startBuffering();
            // add all files in /path/to/project, saving in the phar with the prefix "project"
            $di = new DirectoryIterator($pp);
            $dii = new RecursiveIteratorIterator($di);
            $p->buildFromIterator($dii, $target);

        }
        Y::dump($tags);


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
