<?php
/**
 * Класс Category_Catalog_Frontend_Model для работы с категориями каталога,
 * взаимодействует с БД, общедоступная часть сайта
 */
class Category_Catalog_Frontend_Model extends Catalog_Frontend_Model {
    
    /*
     * public function getCategory(...)
     * public function getChildCategories(...)
     * protected function childCategories(...)
     * public function getCategoryProducts(...)
     * protected function categoryProducts(...)
     * public function getCountCategoryProducts(...)
     * protected function countCategoryProducts(...)
     * public function getCategoryMakers(...)
     * protected function categoryMakers(...)
     * public function getCategoryGroups(...)
     * protected function categoryGroups(...)
     * public function getCategoryGroupParams(...)
     * protected function categoryGroupParams(...)
     * public function getCountCategoryHit(...)
     * protected function countCategoryHit(...)
     * public function getCountCategoryNew(...)
     * protected function countCategoryNew(...)
     */

    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Возвращает информацию о категории с уникальным идентификатором $id;
     * результат работы кэшируется
     */
    public function getCategory($id) {
        $query = "SELECT
                      `name`, `description`, `keywords`, `parent`
                  FROM
                      `categories`
                  WHERE
                      `id` = :id";
        return $this->database->fetch($query, array('id' => $id), $this->enableDataCache);
    }

    /**
     * Возвращает массив дочерних категорий категории с уникальным идентификатором
     * $id с количеством товаров в каждой из них (и во всех дочерних); результат
     * работы кэшируется
     */
    public function getChildCategories($id, $group, $maker, $hit, $new, $param, $sort) {
        // если не включено кэширование данных
        if ( ! $this->enableDataCache) {
            return $this->childCategories($id, $group, $maker, $hit, $new, $param, $sort);
        }

        // уникальный ключ доступа к кэшу
        $key = __METHOD__ . '()-id-' . $id . '-group-' . $group . '-maker-' . $maker . '-hit-' . $hit
               . '-new-' . $new . '-param-' . md5(serialize($param)) . '-sort-' . $sort;
        // имя этой функции (метода)
        $function = __FUNCTION__;
        // арументы, переданные этой функции
        $arguments = func_get_args();
        // получаем данные из кэша
        return $this->getCachedData($key, $function, $arguments);
    }

    /**
     * Возвращает массив дочерних категорий категории с уникальным идентификатором
     * $id с количеством товаров в каждой из них (и во всех дочерних)
     */
    protected function childCategories($id, $group, $maker, $hit, $new, $param, $sort) {

        // получаем список дочерних категорий
        $query = "SELECT
                      `id`, `name`
                  FROM
                      `categories`
                  WHERE
                      `parent` = :id
                  ORDER BY
                      `sortorder`";
        $childCategories = $this->database->fetchAll($query, array('id' => $id));

        // для каждой дочерней категории получаем количество товаров в ней и в ее
        // потомках с учетом фильтров по функциональной группе, производителю, по
        // по лидерам продаж, по новинкам, параметрам подбора
        foreach ($childCategories as $key => $value) {
            $childs = $this->getAllChildIds($value['id']);
            $childs[] = $value['id'];
            $childs = implode(',', $childs);
            $query = "SELECT
                          COUNT(*)
                      FROM
                          `products` `a`
                          INNER JOIN `categories` `b` ON `a`.`category` = `b`.`id`
                          INNER JOIN `makers` `c` ON `a`.`maker` = `c`.`id`
                      WHERE
                          (`category` IN (" . $childs . ") OR `category2` IN (" . $childs . "))
                          AND `a`.`visible` = 1";
            if ($group) { // фильтр по функциональной группе
                $query = $query . " AND `a`.`group` = " . $group;
            }
            if ($maker) { // фильтр по производителю
                $query = $query . " AND `a`.`maker` = " . $maker;
            }
            if ($hit) { // фильтр по лидерам продаж
                $query = $query . " AND `a`.`hit` > 0";
            }
            if ($new) { // фильтр по новинкам
                $query = $query . " AND `a`.`new` > 0";
            }
            if ( ! empty($param)) { // фильтр по параметрам подбора
                $ids = $this->getProductsByParam($group, $param);
                if ( ! empty($ids)) {
                    $query = $query . " AND `a`.`id` IN (" . implode(',', $ids) . ")";
                    $childCategories[$key]['count'] = $this->database->fetchOne($query);
                } else {
                    $childCategories[$key]['count'] = 0;
                }
            } else {
                $childCategories[$key]['count'] = $this->database->fetchOne($query);
            }

            // добавляем в массив информацию об URL дочерних категорий
            $url = 'frontend/catalog/category/id/' . $value['id'];
            if ($group) {
                $url = $url . '/group/' . $group;
            }
            if ($maker) {
                $url = $url . '/maker/' . $maker;
            }
            if ($hit) {
                $url = $url . '/hit/1';
            }
            if ($new) {
                $url = $url . '/new/1';
            }
            if ( ! empty($param)) {
                $temp = array();
                foreach ($param as $k => $v) {
                    $temp[] = $k . '.' . $v;
                }
                $url = $url . '/param/' . implode('-', $temp);
            }
            if ($sort) {
                $url = $url . '/sort/' . $sort;
            }
            $childCategories[$key]['url'] = $this->getURL($url);
        }

        return $childCategories;

    }
    
    /**
     * Возвращает массив товаров категории $id и ее потомков, т.е. не только товары
     * этой категории, но и товары дочерних категорий, товары дочерних-дочерних
     * категорий и так далее; результат работы кэшируется
     */
    public function getCategoryProducts($id, $group, $maker, $hit, $new, $param, $sort, $start) {
        // если не включено кэширование данных
        if ( ! $this->enableDataCache) {
            return $this->categoryProducts($id, $group, $maker, $hit, $new, $param, $sort, $start);
        }

        // уникальный ключ доступа к кэшу
        $key = __METHOD__ . '()-id-' . $id . '-group-' . $group . '-maker-' . $maker. '-hit-' . $hit
               . '-new-' . $new . '-param-' . md5(serialize($param)) . '-sort-' . $sort . '-start-' . $start;
        // имя этой функции (метода)
        $function = __FUNCTION__;
        // арументы, переданные этой функции
        $arguments = func_get_args();
        // получаем данные из кэша
        return $this->getCachedData($key, $function, $arguments);
    }

    /**
     * Возвращает массив товаров категории $id и ее потомков, т.е. не только товары
     * этой категории, но и товары дочерних категорий, товары дочерних-дочерних
     * категорий и так далее
     */
    protected function categoryProducts($id, $group, $maker, $hit, $new, $param, $sort, $start) {

        $childs = $this->getAllChildIds($id);
        $childs[] = $id;
        $childs = implode(',', $childs);

        $tmp = '';
        if ($group) { // фильтр по функциональной группе
            $tmp = $tmp . " AND `a`.`group` = " . $group;
        }
        if ($maker) { // фильтр по производителю
            $tmp = $tmp . " AND `a`.`maker` = " . $maker;
        }
        if ($hit) { // фильтр по лидерам продаж
            $tmp = $tmp . " AND `a`.`hit` > 0";
        }
        if ($new) { // фильтр по новинкам
            $tmp = $tmp . " AND `a`.`new` > 0";
        }
        if ( ! empty($param)) { // фильтр по параметрам подбора
            $ids = $this->getProductsByParam($group, $param);
            if (empty($ids)) {
                return array();
            }
            $tmp = $tmp . " AND `a`.`id` IN (" . implode(',', $ids) . ")";
        }

        switch ($sort) { // сортировка
            case 0: $order = '`b`.`globalsort`, `a`.`sortorder`';  break; // сортировка по умолчанию
            case 1: $order = '`a`.`price`';      break;                   // сортировка по цене, по возрастанию
            case 2: $order = '`a`.`price` DESC'; break;                   // сортировка по цене, по убыванию
            case 3: $order = '`a`.`name`';       break;                   // сортировка по наименованию, по возрастанию
            case 4: $order = '`a`.`name` DESC';  break;                   // сортировка по наименованию, по убыванию
            case 5: $order = '`a`.`code`';       break;                   // сортировка по коду, по возрастанию
            case 6: $order = '`a`.`code` DESC';  break;                   // сортировка по коду, по убыванию
        }

        $query = "SELECT
                      `a`.`id` AS `id`, `a`.`code` AS `code`, `a`.`name` AS `name`,
                      `a`.`title` AS `title`, `a`.`price` AS `price`, `a`.`price2` AS `price2`,
                      `a`.`price3` AS `price3`, `a`.`unit` AS `unit`, `a`.`shortdescr` AS `shortdescr`,
                      `a`.`image` AS `image`, `a`.`hit` AS `hit`, `a`.`new` AS `new`,
                      `b`.`id` AS `ctg_id`, `b`.`name` AS `ctg_name`,
                      `c`.`id` AS `mkr_id`, `c`.`name` AS `mkr_name`,
                      `a`.`group` AS `grp_id`
                  FROM
                      `products` `a`
                      INNER JOIN `categories` `b` ON `a`.`category` = `b`.`id`
                      INNER JOIN `makers` `c` ON `a`.`maker` = `c`.`id`
                  WHERE
                      (`a`.`category` IN (" . $childs . ") OR `a`.`category2` IN (" . $childs . "))" . $tmp . "
                      AND `a`.`visible` = 1
                  ORDER BY " . $order . "
                  LIMIT " . $start . ", " . $this->config->pager->frontend->products->perpage;
        $products = $this->database->fetchAll($query);

        // добавляем в массив товаров информацию об URL товаров, производителей, фото
        foreach ($products as $key => $value) {
            // URL ссылки на страницу товара
            $products[$key]['url']['product'] = $this->getURL('frontend/catalog/product/id/' . $value['id']);
            // URL ссылки на страницу производителя
            $products[$key]['url']['maker'] = $this->getURL('frontend/catalog/maker/id/' . $value['mkr_id']);
            // URL ссылки на фото товара
            if ((!empty($value['image'])) && is_file('./files/catalog/imgs/small/' . $value['image'])) {
                $products[$key]['url']['image'] = $this->config->site->url . 'files/catalog/imgs/small/' . $value['image'];
            } else {
                $products[$key]['url']['image'] = $this->config->site->url . 'files/catalog/imgs/small/nophoto.jpg';
            }
            // атрибут action тега form для добавления товара в корзину
            $products[$key]['action']['basket'] = $this->getURL('frontend/basket/addprd');
            // атрибут action тега form для добавления товара в список отложенных
            $products[$key]['action']['wished'] = $this->getURL('frontend/wished/addprd');
            // атрибут action тега form для добавления товара в список сравнения
            $products[$key]['action']['compare'] = $this->getURL('frontend/compare/addprd');
        }

        return $products;
    }

    /**
     * Возвращает количество товаров в категории $id и в ее потомках, т.е.
     * суммарное кол-во товаров не только в категории $id, но и в дочерних
     * категориях и в дочерних-дочерних категориях и так далее; результат
     * работы кэшируется
     */
    public function getCountCategoryProducts($id, $group, $maker, $hit, $new, $param) {
        // если не включено кэширование данных
        if ( ! $this->enableDataCache) {
            return $this->countCategoryProducts($id, $group, $maker, $hit, $new, $param);
        }

        // уникальный ключ доступа к кэшу
        $key = __METHOD__ . '()-id-' . $id . '-group-' .$group . '-maker-' . $maker
               . '-hit-' . $hit . '-new-' . $new . '-param-' . md5(serialize($param));
        // имя этой функции (метода)
        $function = __FUNCTION__;
        // арументы, переданные этой функции
        $arguments = func_get_args();
        // получаем данные из кэша
        return $this->getCachedData($key, $function, $arguments);
    }

    /**
     * Возвращает количество товаров в категории $id и в ее потомках, т.е.
     * суммарное кол-во товаров не только в категории $id, но и в дочерних
     * категориях и в дочерних-дочерних категориях и так далее; результат
     * работы кэшируется
     */
    protected function countCategoryProducts($id, $group, $maker, $hit, $new, $param) {
        $childs = $this->getAllChildIds($id);
        $childs[] = $id;
        $childs = implode(',', $childs);
        $query = "SELECT
                      COUNT(*)
                  FROM
                      `products` `a`
                      INNER JOIN `categories` `b` ON `a`.`category` = `b`.`id`
                      INNER JOIN `makers` `c` ON `a`.`maker` = `c`.`id`
                  WHERE
                      (`a`.`category` IN (" . $childs . ") OR `a`.`category2` IN (" . $childs . "))
                      AND `a`.`visible` = 1";
        if ($group) { // фильтр по функциональной группе
            $query = $query . " AND `a`.`group` = " . $group;
        }
        if ($maker) { // фильтр по производителю
            $query = $query . " AND `a`.`maker` = " . $maker;
        }
        if ($hit) { // фильтр по лидерам продаж
            $query = $query . " AND `a`.`hit` > 0";
        }
        if ($new) { // фильтр по новинкам
            $query = $query . " AND `a`.`new` > 0";
        }
        if ( ! empty($param)) { // фильтр по параметрам подбора
            $ids = $this->getProductsByParam($group, $param);
            if (empty($ids)) {
                return 0;
            }
            $query = $query . " AND `a`.`id` IN (" . implode(',', $ids) . ")";
        }
        return $this->database->fetchOne($query);
    }
    
    /**
     * Возвращает массив производителей товаров в категории $id и в ее потомках,
     * т.е. не только производителей товаров этой категории, но и производителей
     * товаров в дочерних категориях, производителей товаров в дочерних-дочерних
     * категориях и так далее; результат работы кэшируется
     */
    public function getCategoryMakers($id, $group, $hit, $new, $param) {
        // если не включено кэширование данных
        if ( ! $this->enableDataCache) {
            return $this->categoryMakers($id, $group, $hit, $new, $param);
        }

        // уникальный ключ доступа к кэшу
        $key = __METHOD__ . '()-id-' . $id . '-group-' . $group . '-hit-' . $hit
               . '-new-' . $new . '-param-' . md5(serialize($param));
        // имя этой функции (метода)
        $function = __FUNCTION__;
        // арументы, переданные этой функции
        $arguments = func_get_args();
        // получаем данные из кэша
        return $this->getCachedData($key, $function, $arguments);
    }

    /**
     * Возвращает массив производителей товаров в категории $id и в ее потомках,
     * т.е. не только производителей товаров этой категории, но и производителей
     * товаров в дочерних категориях, производителей товаров в дочерних-дочерних
     * категориях и так далее
     */
    protected function categoryMakers($id, $group, $hit, $new, $param) {

        // получаем список всех произвоителей этой категории и ее потомков
        $childs = $this->getAllChildIds($id);
        $childs[] = $id;
        $childs = implode(',', $childs);
        $query = "SELECT
                      `a`.`id` AS `id`, `a`. `name` AS `name`, COUNT(*) AS `count`
                  FROM
                      `makers` `a`
                      INNER JOIN `products` `b` ON `a`.`id` = `b`.`maker`
                      INNER JOIN `categories` `c` ON `b`.`category` = `c`.`id`
                  WHERE
                      (`b`.`category` IN (" . $childs . ") OR `b`.`category2` IN (" . $childs . "))
                      AND `b`.`visible` = 1
                  GROUP BY
                      `a`.`id`, `a`. `name`
                  ORDER BY
                      `a`.`name`";

        $makers = $this->database->fetchAll($query);

        if (0 == $group && 0 == $hit && 0 == $new) {
            return $makers;
        }

        // теперь подсчитываем количество товаров для каждого производителя с
        // учетом фильтров по функциональной группе, лидерам продаж, новинкам
        // и по параметрам
        foreach ($makers as $key => $value) {
            $query = "SELECT
                          COUNT(*)
                      FROM
                          `makers` `a`
                          INNER JOIN `products` `b` ON `a`.`id` = `b`.`maker`
                          INNER JOIN `categories` `c` ON `b`.`category` = `c`.`id`
                      WHERE
                          (`b`.`category` IN (" . $childs . ") OR `b`.`category2` IN (" . $childs . "))
                          AND `a`.`id` = " . $value['id'] . "
                          AND `b`.`visible` = 1";
            if ($group) { // фильтр по функциональной группе
                $query = $query . " AND `b`.`group` = " . $group;
            }
            if ($hit) { // фильтров по лидерам продаж
                $query = $query . " AND `b`.`hit` > 0";
            }
            if ($new) { // фильтр по новинкам
                $query = $query . " AND `b`.`new` > 0";
            }
            if ( ! empty($param)) { // фильтр по параметрам подбора
                $ids = $this->getProductsByParam($group, $param);
                if ( ! empty($ids)) {
                    $query = $query . " AND `b`.`id` IN (" . implode(',', $ids) . ")";
                    $makers[$key]['count'] = $this->database->fetchOne($query);
                } else {
                    $makers[$key]['count'] = 0;
                }
            } else {
                $makers[$key]['count'] = $this->database->fetchOne($query);
            }
        }

        return $makers;

    }

    /**
     * Возвращает массив функциональных групп товаров в категории $id и в ее потомках,
     * т.е. не только функциональные группы товаров этой категории, но и функциональные
     * группы товаров в дочерних категориях, функциональные группы товаров в
     * дочерних-дочерних категориях и т.д. Результат работы кэшируется
     */
    public function getCategoryGroups($id, $maker, $hit, $new) {
        // если не включено кэширование данных
        if ( ! $this->enableDataCache) {
            return $this->categoryGroups($id, $maker, $hit, $new);
        }

        // уникальный ключ доступа к кэшу
        $key = __METHOD__ . '()-id-' . $id . '-maker-' . $maker . '-hit-' . $hit . '-new-' . $new;
        // имя этой функции (метода)
        $function = __FUNCTION__;
        // арументы, переданные этой функции
        $arguments = func_get_args();
        // получаем данные из кэша
        return $this->getCachedData($key, $function, $arguments);
    }

    /**
     * Возвращает массив функциональных групп товаров в категории $id и в ее потомках,
     * т.е. не только функциональные группы товаров этой категории, но и функциональные
     * группы товаров в дочерних категориях, функциональные группы товаров в
     * дочерних-дочерних категориях и т.д.
     */
    protected function categoryGroups($id, $maker, $hit, $new) {

        // получаем список всех функциональных групп этой категории и ее потомков
        $childs = $this->getAllChildIds($id);
        $childs[] = $id;
        $childs = implode(',', $childs);

        $query = "SELECT
                      `a`.`id` AS `id`, `a`. `name` AS `name`, COUNT(*) AS `count`
                  FROM
                      `groups` `a`
                      INNER JOIN `products` `b` ON `a`.`id` = `b`.`group`
                      INNER JOIN `categories` `c` ON `b`.`category` = `c`.`id`
                      INNER JOIN `makers` `d` ON `b`.`maker` = `d`.`id`
                  WHERE
                      (`b`.`category` IN (" . $childs . ") OR `b`.`category2` IN (" . $childs . "))
                      AND `b`.`visible` = 1
                  GROUP BY
                      `a`.`id`, `a`. `name`
                  ORDER BY
                      `a`.`name`, COUNT(*) DESC";
        $groups = $this->database->fetchAll($query);

        if (count($groups) > 15) {
            $bound = false;
            foreach ($groups as $value)  {
                if ($value['count'] > 1) {
                    $bound = true;
                    break;
                }
            }
            if ($bound) {
                $first = array();
                $second = array();
                foreach ($groups as $value)  {
                    if ($value['count'] > 1) {
                        $first[] = $value;
                    } else {
                        $second[] = $value;
                    }
                }
            }
            if (!empty($second)) {
                $second[0]['bound'] = true;
            }
            $groups = array_merge($first, $second);
        }

        if (0 == $maker && 0 == $hit && 0 == $new) {
            return $groups;
        }

        // теперь подсчитываем количество товаров для каждой группы с
        // учетом фильтров по производителю, лидерам продаж, новинкам
        foreach ($groups as $key => $value)  {
            $query = "SELECT
                          COUNT(*)
                      FROM
                          `groups` `a`
                          INNER JOIN `products` `b` ON `a`.`id` = `b`.`group`
                          INNER JOIN `categories` `c` ON `b`.`category` = `c`.`id`
                          INNER JOIN `makers` `d` ON `b`.`maker` = `d`.`id`
                      WHERE
                          (`b`.`category` IN (" . $childs . ") OR `b`.`category2` IN (" . $childs . "))
                          AND `a`.`id` = " . $value['id'] . "
                          AND `b`.`visible` = 1";
            if ($maker) {
                $query = $query . " AND `b`.`maker` = " . $maker;
            }
            if ($hit) {
                $query = $query . " AND `b`.`hit` > 0";
            }
            if ($new) {
                $query = $query . " AND `b`.`new` > 0";
            }
            $groups[$key]['count'] = $this->database->fetchOne($query);

        }
        return $groups;

    }

    /**
     * Возвращает массив параметров подбора для выбранной категории $id и всех ее потомков
     * и выбранной функциональной группы; результат работы кэшируется
     */
    public function getCategoryGroupParams($id, $group, $maker, $hit, $new, $param) {
        // если не включено кэширование данных
        if ( ! $this->enableDataCache) {
            return $this->categoryGroupParams($id, $group, $maker, $hit, $new, $param);
        }

        // уникальный ключ доступа к кэшу
        $key = __METHOD__ . '()-id-' . $id . '-group-' . $group . '-maker-' . $maker
               . '-hit-' . $hit . '-new-' . $new . '-param-' . md5(serialize($param));
        // имя этой функции (метода)
        $function = __FUNCTION__;
        // арументы, переданные этой функции
        $arguments = func_get_args();
        // получаем данные из кэша
        return $this->getCachedData($key, $function, $arguments);
    }

    /**
     * Возвращает массив параметров подбора для выбранной категории $id и всех ее потомков
     * и выбранной функциональной группы
     */
    protected function categoryGroupParams($id, $group, $maker, $hit, $new, $param) {

        if (0 == $group) {
            return array();
        }

        // получаем список всех параметров подбора для выбранной функциональной
        // группы и выбранной категории и всех ее потомков
        $childs = $this->getAllChildIds($id);
        $childs[] = $id;
        $childs = implode(',', $childs);

        $query = "SELECT
                      `f`.`id` AS `param_id`, `f`.`name` AS `param_name`,
                      `g`.`id` AS `value_id`, `g`.`name` AS `value_name`,
                      COUNT(*) AS `count`
                  FROM
                      `groups` `a`
                      INNER JOIN `products` `b` ON `a`.`id` = `b`.`group`
                      INNER JOIN `categories` `c` ON `b`.`category` = `c`.`id`
                      INNER JOIN `makers` `d` ON `b`.`maker` = `d`.`id`
                      INNER JOIN `product_param_value` `e` ON `b`.`id` = `e`.`product_id`
                      INNER JOIN `params` `f` ON `e`.`param_id` = `f`.`id`
                      INNER JOIN `values` `g` ON `e`.`value_id` = `g`.`id`
                  WHERE
                      (`b`.`category` IN (" . $childs . ") OR `b`.`category2` IN (" . $childs . "))
                      AND `a`.`id` = " . $group . "
                      AND `b`.`visible` = 1
                  GROUP BY
                      1, 2, 3, 4
                  ORDER BY
                      `f`.`name`, `g`.`name`";
        $result = $this->database->fetchAll($query);

        // теперь подсчитываем количество товаров для каждого параметра и каждого
        // значения параметра с учетом фильтров по производителю, лидерам продаж,
        // новинкам и по параметрам
        foreach ($result as $key => $value)  {
            $query = "SELECT
                          COUNT(*)
                      FROM
                          `groups` `a`
                          INNER JOIN `products` `b` ON `a`.`id` = `b`.`group`
                          INNER JOIN `categories` `c` ON `b`.`category` = `c`.`id`
                          INNER JOIN `makers` `d` ON `b`.`maker` = `d`.`id`
                          INNER JOIN `product_param_value` `e` ON `b`.`id` = `e`.`product_id`
                          INNER JOIN `params` `f` ON `e`.`param_id` = `f`.`id`
                          INNER JOIN `values` `g` ON `e`.`value_id` = `g`.`id`
                      WHERE
                          (`b`.`category` IN (" . $childs . ") OR `b`.`category2` IN (" . $childs . "))
                          AND `a`.`id` = " . $group . "
                          AND `e`.`param_id` = " . $value['param_id'] . "
                          AND `e`.`value_id` = " . $value['value_id'] . "
                          AND `b`.`visible` = 1";
            if ($maker) { // фильтр по производителю
                $query = $query . " AND `b`.`maker` = " . $maker;
            }
            if ($hit) { // фильтр по лидерам продаж
                $query = $query . " AND `b`.`hit` > 0";
            }
            if ($new) { // фильтр по новинкам
                $query = $query . " AND `b`.`new` > 0";
            }

            $temp = $param;
            if (( ! empty($temp)) && isset($temp[$value['param_id']])) {
                unset($temp[$value['param_id']]);
            }
            if ( ! empty($temp)) { // фильтр по параметрам подбора
                $ids = $this->getProductsByParam($group, $temp);
                if ( ! empty($ids)) {
                    $query = $query . " AND `b`.`id` IN (" . implode(',', $ids) . ")";
                    $result[$key]['count'] = $this->database->fetchOne($query);
                } else {
                    $result[$key]['count'] = 0;
                }
            } else {
                $result[$key]['count'] = $this->database->fetchOne($query);
            }
        }

        $params = array();
        $param_id = 0;
        $counter = -1;
        foreach($result as $value) {
            if ($param_id != $value['param_id']) {
                $counter++;
                $param_id = $value['param_id'];
                $params[$counter] = array(
                    'id' => $value['param_id'],
                    'name' => $value['param_name'],
                    'selected' => isset($param[$value['param_id']]),
                );
            }
            $params[$counter]['values'][] = array(
                'id' => $value['value_id'],
                'name' => $value['value_name'],
                'count' => $value['count'],
                'selected' => in_array($value['value_id'], $param)
            );
        }

        return $params;

    }
    
    /**
     * Функция возвращает количество лидеров продаж в категории $id и ее потомках,
     * с учетом фильтров по функциональной группе, производителю и т.п. Результат
     * работы кэшируется
     */
    public function getCountCategoryHit($id, $group, $maker, $hit, $new, $param) {

        // если не включено кэширование данных
        if ( ! $this->enableDataCache) {
            return $this->countCategoryHit($id, $group, $maker, $hit, $new, $param);
        }

        // уникальный ключ доступа к кэшу
        $key = __METHOD__ . '()-id-' . $id . '-group-' . $group . '-maker-' . $maker
               . '-hit-' . $hit . '-new-' . $new . '-param-' . md5(serialize($param));
        // имя этой функции (метода)
        $function = __FUNCTION__;
        // арументы, переданные этой функции
        $arguments = func_get_args();
        // получаем данные из кэша
        return $this->getCachedData($key, $function, $arguments);

    }

    /**
     * Функция возвращает количество лидеров продаж в категории $id и ее потомках,
     * с учетом фильтров по функциональной группе, производителю и т.п.
     */
    protected function countCategoryHit($id, $group, $maker, $hit, $new, $param) {

        $childs = $this->getAllChildIds($id);
        $childs[] = $id;
        $childs = implode(',', $childs);
        $query = "SELECT
                      COUNT(*)
                  FROM
                      `products` `a`
                      INNER JOIN `categories` `b` ON `a`.`category` = `b`.`id`
                      INNER JOIN `makers` `c` ON `a`.`maker` = `c`.`id`
                  WHERE
                      (`a`.`category` IN (" . $childs . ") OR `a`.`category2` IN (" . $childs . "))
                      AND `a`.`visible` = 1";
        if ($group) { // фильтр по функциональной группе
            $query = $query . " AND `a`.`group` = " . $group;
        }
        if ($maker) { // фильтр по производителю
            $query = $query . " AND `a`.`maker` = " . $maker;
        }
        if ( ! $hit) {
            // надо выяснить, сколько товаров будет найдено, если отметить
            // галочку «Лидер продаж»; на данный момент checkbox не отмечен,
            // но если пользователь его отметит - сколько будет найдено товаров?
            $query = $query . " AND `a`.`hit` > 0";
        }
        if ($new) { // фильтр по новинкам
            $query = $query . " AND `a`.`new` > 0";
        }
        if ( ! empty($param)) { // фильтр по параметрам подбора
            $ids = $this->getProductsByParam($group, $param);
            if (empty($ids)) {
                return 0;
            }
            $query = $query . " AND `a`.`id` IN (" . implode(',', $ids) . ")";
        }
        return $this->database->fetchOne($query);

    }

    /**
     * Функция возвращает количество новинок в категории $id и ее потомках, с
     * учетом фильтров по функциональной группе, производителю и т.п. Результат
     * работы кэшируется
     */
    public function getCountCategoryNew($id, $group, $maker, $hit, $new, $param) {

        // если не включено кэширование данных
        if ( ! $this->enableDataCache) {
            return $this->countCategoryNew($id, $group, $maker, $hit, $new, $param);
        }

        // уникальный ключ доступа к кэшу
        $key = __METHOD__ . '()-id-' . $id . '-group-' . $group . '-maker-' . $maker
               . '-hit-' . $hit . '-new-' . $new . '-param-' . md5(serialize($param));
        // имя этой функции (метода)
        $function = __FUNCTION__;
        // арументы, переданные этой функции
        $arguments = func_get_args();
        // получаем данные из кэша
        return $this->getCachedData($key, $function, $arguments);

    }

    /**
     * Функция возвращает количество новинок в категории $id и ее потомках, с
     * учетом фильтров по функциональной группе, производителю и т.п.
     */
    protected function countCategoryNew($id, $group, $maker, $hit, $new, $param) {

        $childs = $this->getAllChildIds($id);
        $childs[] = $id;
        $childs = implode(',', $childs);
        $query = "SELECT
                      COUNT(*)
                  FROM
                      `products` `a`
                      INNER JOIN `categories` `b` ON `a`.`category` = `b`.`id`
                      INNER JOIN `makers` `c` ON `a`.`maker` = `c`.`id`
                  WHERE
                      (`a`.`category` IN (" . $childs . ") OR `a`.`category2` IN (" . $childs . "))
                      AND `a`.`visible` = 1";
        if ($group) { // фильтр по функциональной группе
            $query = $query . " AND `a`.`group` = " . $group;
        }
        if ($maker) { // фильтр по производителю
            $query = $query . " AND `a`.`maker` = " . $maker;
        }
        if ($hit) { // фильтр по лидерам продаж
            $query = $query . " AND `a`.`hit` > 0";
        }
        if ( ! $new) {
            // надо выяснить, сколько товаров будет найдено, если отметить
            // галочку «Новинка»; на данный момент checkbox не отмечен, но
            // если пользователь его отметит - сколько будет найдено товаров?
            $query = $query . " AND `a`.`new` > 0";
        }
        if ( ! empty($param)) { // фильтр по параметрам подбора
            $ids = $this->getProductsByParam($group, $param);
            if (empty($ids)) {
                return 0;
            }
            $query = $query . " AND `a`.`id` IN (" . implode(',', $ids) . ")";
        }
        return $this->database->fetchOne($query);

    }
    
    /**
     * Функция возвращает ЧПУ для категории с уникальным идентификатором $id с учетом
     * фильтров и сортировки товаров; результат работы кэшируется
     */
    public function getCategoryURL($id, $group, $maker, $hit, $new, $param, $sort) {

        // если не включено кэширование данных
        if ( ! $this->enableDataCache) {
            return $this->categoryURL($id, $group, $maker, $hit, $new, $param, $sort);
        }

        // уникальный ключ доступа к кэшу
        $key = __METHOD__ . '()-id-' . $id . '-group-' . $group . '-maker-' . $maker. '-hit-' . $hit
               . '-new-' . $new . '-param-' . md5(serialize($param)) . '-sort-' . $sort;
        // имя этой функции (метода)
        $function = __FUNCTION__;
        // арументы, переданные этой функции
        $arguments = func_get_args();
        // получаем данные из кэша
        return $this->getCachedData($key, $function, $arguments);

    }
    
    /**
     * Функция возвращает ЧПУ для категории с уникальным идентификатором $id с учетом
     * фильтров и сортировки товаров
     */
    protected function categoryURL($id, $group, $maker, $hit, $new, $param, $sort) {

        $url = 'frontend/catalog/category/id/' . $id;
        if ($group) {
            $url = $url . '/group/' . $group;
        }
        if ($maker) {
            $url = $url . '/maker/' . $maker;
        }
        if ($hit) {
            $url = $url . '/hit/1';
        }
        if ($new) {
            $url = $url . '/new/1';
        }
        if ( ! empty($param)) {
            $temp = array();
            foreach ($param as $key => $value) {
                $temp[] = $key . '.' . $value;
            }
            $url = $url . '/param/' . implode('-', $temp);
        }
        if ($sort) {
            $url = $url . '/sort/' . $sort;
        }
        return $this->getURL($url);

    }
    
    /**
     * Функция возвращает массив ссылок для сортировки товаров категории $id по цене,
     * наименованию, коду (артикулу); результат работы кэшируется
     */
    public function getCategorySortOrders($id, $group, $maker, $hit, $new, $param) {

        // если не включено кэширование данных
        if ( ! $this->enableDataCache) {
            return $this->categorySortOrders($id, $group, $maker, $hit, $new, $param);
        }

        // уникальный ключ доступа к кэшу
        $key = __METHOD__ . '()-id-' . $id . '-group-' . $group . '-maker-' . $maker
               . '-hit-' . $hit . '-new-' . $new . '-param-' . md5(serialize($param));
        // имя этой функции (метода)
        $function = __FUNCTION__;
        // арументы, переданные этой функции
        $arguments = func_get_args();
        // получаем данные из кэша
        return $this->getCachedData($key, $function, $arguments);

    }

    /**
     * Функция возвращает массив ссылок для сортировки товаров категории $id по цене,
     * наименованию, коду (артикулу)
     */
    protected function categorySortOrders($id, $group, $maker, $hit, $new, $param) {

        $url = 'frontend/catalog/category/id/' . $id;
        if ($group) {
            $url = $url . '/group/' . $group;
        }
        if ($maker) {
            $url = $url . '/maker/' . $maker;
        }
        if ($hit) {
            $url = $url . '/hit/1';
        }
        if ($new) {
            $url = $url . '/new/1';
        }
        if ( ! empty($param)) {
            $temp = array();
            foreach ($param as $key => $value) {
                $temp[] = $key . '.' . $value;
            }
            $url = $url . '/param/' . implode('-', $temp);
        }
        /*
         * варианты сортировки:
         * 0 - по умолчанию,
         * 1 - по цене, по возрастанию
         * 2 - по цене, по убыванию
         * 3 - по наименованию, по возрастанию
         * 4 - по наименованию, по убыванию
         * 5 - по коду, по возрастанию
         * 6 - по коду, по убыванию
         */
        $sortorders = array();
        for ($i = 0; $i <= 6; $i++) {
            switch ($i) {
                case 0: $name = 'без сортировки';  break;
                case 1: $name = 'цена, возр.';     break;
                case 2: $name = 'цена, убыв.';     break;
                case 3: $name = 'название, возр.'; break;
                case 4: $name = 'название, убыв.'; break;
                case 5: $name = 'код, возр.';      break;
                case 6: $name = 'код, убыв.';      break;
            }
            $temp = $i ? $url . '/sort/' . $i : $url;
            $sortorders[$i] = array(
                'url'  => $this->getURL($temp),
                'name' => $name
            );
        }
        return $sortorders;

    }

}
