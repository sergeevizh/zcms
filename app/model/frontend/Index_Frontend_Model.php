<?php
/**
 * Класс Index_Frontend_Model для показа главной страницы сайта,
 * взаимодействует с базой данных, общедоступная часть сайта
 */
class Index_Frontend_Model extends Frontend_Model {

    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Функция возвращает все данные для формирования главной страницы сайта;
     * результат работы кэшируется
     */
    public function getAllIndexData() {
        // если не включено кэширование данных
        if ( ! $this->enableDataCache) {
            return $this->allIndexData();
        }

        // уникальный ключ доступа к кэшу
        $key = __METHOD__ . '()';
        // имя этой функции (метода)
        $function = __FUNCTION__;
        // арументы, переданные этой функции
        $arguments = func_get_args();
        // получаем данные из кэша
        return $this->getCachedData($key, $function, $arguments);   
    }
    
    /**
     * Функция возвращает все данные для формирования главной страницы сайта
     */
    protected function allIndexData() {
        $data = array(
            $this->getIndexPage(),
            $this->getAllBanners(),
            $this->getCompanyNews(),
            $this->getGeneralNews(),
            $this->getHitProducts(),
            $this->getNewProducts()
        );
        return $data;
    }

    /**
     * Функция возвращает информацию о главной (стартовой) странице сайта
     */
    private function getIndexPage() {
        $query = "SELECT
                      `name`, `title`, `description`, `keywords`, `body`
                  FROM
                      `start`
                  WHERE
                      `id` = 1";
        return $this->database->fetch($query);
    }

    /**
     * Функция возвращает массив всех баннеров для главной (стартовой) страницы сайта
     */
    private function getAllBanners() {
        $query = "SELECT
                      `id`, `name`, `url`, `alttext`
                  FROM
                      `slider`
                  WHERE
                      `visible` = 1
                  ORDER BY
                      `sortorder`";
        return $this->database->fetchAll($query);
    }
    
    /**
     * Функция возвращает массив трех последних событий отрасли
     */
    private function getGeneralNews() {
        
        $query = "SELECT
                      `id`, `name`, `excerpt`,
                      DATE_FORMAT(`added`, '%d.%m.%Y') AS `date`,
                      DATE_FORMAT(`added`, '%H:%i:%s') AS `time`
                  FROM
                      `blog_posts`
                  WHERE
                      `category` = 2
                  ORDER BY
                      `added` DESC
                  LIMIT
                      3";
        $news = $this->database->fetchAll($query);
        // добавляем в массив новостей информацию об URL новости, картинки
        foreach($news as $key => $value) {
            $news[$key]['url']['item'] = $this->getURL('frontend/blog/post/id/' . $value['id']);
            if (is_file('files/blog/thumb/' . $value['id'] . '.jpg')) {
                $news[$key]['url']['image'] = $this->config->site->url . 'files/blog/thumb/' . $value['id'] . '.jpg';
            } else {
                $news[$key]['url']['image'] = $this->config->site->url . 'files/blog/thumb/default.jpg';
            }
        }
        return $news;
        
    }

    /**
     * Функция возвращает массив трех последних новостей компании
     */
    private function getCompanyNews() {
        
        $query = "SELECT
                      `id`, `name`, `excerpt`,
                      DATE_FORMAT(`added`, '%d.%m.%Y') AS `date`,
                      DATE_FORMAT(`added`, '%H:%i:%s') AS `time`
                  FROM
                      `blog_posts`
                  WHERE
                      `category` = 1
                  ORDER BY
                      `added` DESC
                  LIMIT
                      3";
        $news = $this->database->fetchAll($query);
        // добавляем в массив новостей информацию об URL новости, картинки
        foreach($news as $key => $value) {
            $news[$key]['url']['item'] = $this->getURL('frontend/blog/post/id/' . $value['id']);
            if (is_file('files/blog/thumb/' . $value['id'] . '.jpg')) {
                $news[$key]['url']['image'] = $this->config->site->url . 'files/blog/thumb/' . $value['id'] . '.jpg';
            } else {
                $news[$key]['url']['image'] = $this->config->site->url . 'files/blog/thumb/default.jpg';
            }
        }
        return $news;
        
    }

    /**
     * Функция возвращает массив лидеров продаж для главной (стартовой) страницы сайта
     */
    private function getHitProducts() {
        
        $query = "SELECT
                      `a`.`id` AS `id`, `a`.`code` AS `code`, `a`.`name` AS `name`,
                      `a`.`title` AS `title`, `a`.`price` AS `price`,`a`.`unit` AS `unit`,
                      `a`.`image` AS `image`
                  FROM
                      `products` `a`
                      INNER JOIN `categories` `b` ON `a`.`category` = `b`.`id`
                      INNER JOIN `makers` `c` ON `a`.`maker` = `c`.`id`
                      INNER JOIN `groups` `d` ON `a`.`group` = `d`.`id`
                  WHERE
                      `a`.`hit` = 2
                      AND `a`.`visible` = 1
                  ORDER BY
                      `b`.`globalsort`, `a`.`sortorder`
                  LIMIT
                      20";
        $products = $this->database->fetchAll($query);

        // добавляем в массив товаров информацию об URL товаров, фото
        foreach ($products as $key => $value) {
            // URL ссылки на страницу товара
            $products[$key]['url']['product'] = $this->getURL('frontend/catalog/product/id/' . $value['id']);
            // URL ссылки на фото товара
            if ((!empty($value['image'])) && is_file('./files/catalog/imgs/small/' . $value['image'])) {
                $products[$key]['url']['image'] = $this->config->site->url . 'files/catalog/imgs/small/' . $value['image'];
            } else {
                $products[$key]['url']['image'] = $this->config->site->url . 'files/catalog/imgs/small/nophoto.jpg';
            }
        }

        return $products;
        
    }

    /**
     * Функция возвращает массив новых товаров для главной (стартовой) страницы сайта
     */
    public function getNewProducts() {
        
        $query = "SELECT
                      `a`.`id` AS `id`, `a`.`code` AS `code`, `a`.`name` AS `name`,
                      `a`.`title` AS `title`, `a`.`price` AS `price`,`a`.`unit` AS `unit`,
                      `a`.`image` AS `image`
                  FROM
                      `products` `a`
                      INNER JOIN `categories` `b` ON `a`.`category` = `b`.`id`
                      INNER JOIN `makers` `c` ON `a`.`maker` = `c`.`id`
                      INNER JOIN `groups` `d` ON `a`.`group` = `d`.`id`
                  WHERE
                      `a`.`new` = 2
                      AND `a`.`visible` = 1
                  ORDER BY
                      `b`.`globalsort`, `a`.`sortorder`
                  LIMIT
                      20";
        $products = $this->database->fetchAll($query);

        // добавляем в массив товаров информацию об URL товаров, фото
        foreach ($products as $key => $value) {
            // URL ссылки на страницу товара
            $products[$key]['url']['product'] = $this->getURL('frontend/catalog/product/id/' . $value['id']);
            // URL ссылки на фото товара
            if ((!empty($value['image'])) && is_file('./files/catalog/imgs/small/' . $value['image'])) {
                $products[$key]['url']['image'] = $this->config->site->url . 'files/catalog/imgs/small/' . $value['image'];
            } else {
                $products[$key]['url']['image'] = $this->config->site->url . 'files/catalog/imgs/small/nophoto.jpg';
            }
        }

        return $products;

    }

}
