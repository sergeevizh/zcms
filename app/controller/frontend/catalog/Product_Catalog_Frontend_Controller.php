<?php
/**
 * Класс Product_Catalog_Frontend_Controller формирует страницу товара,
 * получает данные от модели Product_Catalog_Frontend_Model, общедоступная
 * часть сайта
 */
class Product_Catalog_Frontend_Controller extends Catalog_Frontend_Controller {

    public function __construct($params = null) {
        parent::__construct($params);
    }

    /**
     * Функция получает от модели данные, необходимые для формирования страницы
     * товара каталога
     */
    protected function input() {

        /*
         * сначала обращаемся к родительскому классу Catalog_Frontend_Controller,
         * чтобы установить значения переменных, которые нужны для работы всех его
         * потомков, потом переопределяем эти переменные (если необходимо) и
         * устанавливаем значения перменных, которые нужны для работы только
         * Product_Catalog_Frontend_Controller
         */
        parent::input();

        // если не передан id товара или id товара не число
        if ( ! (isset($this->params['id']) && ctype_digit($this->params['id'])) ) {
            $this->notFoundRecord = true;
            return;
        } else {
            $this->params['id'] = (int)$this->params['id'];
        }

        /*
         * получаем от модели данные, необходимые для формирования страницы товара, и
         * записываем их в массив переменных, который будет передан в шаблон center.php
         */
        $this->getProduct();
        // товар не найден в таблице БД products
        if ($this->notFoundRecord) {
            return;
        }

        /*
         * Условие if не имеет отношения к обычной работе приложения, когда формируется
         * страница товара по запросу от браузера. Настройка $this->config->cache->make
         * установлена в конфигурации, когда приложение запущено из командной строки для
         * формирования кэша. См. комментарии в начале конструктора класса Router в файле
         * app/include/Router.php и исходный код файла cache/make-cache.php.
         */
        if ( ! isset($this->config->cache->make)) {
            // добавляем товар в список просмотренных
            $this->viewedFrontendModel->addToViewed($this->params['id']);
        }

        // переопределяем переменную, которая будет передана в шаблон left.php,
        // чтобы раскрыть ветку текущей категории
        $this->leftVars['catalogMenu'] = $this->menuCatalogFrontendModel->getCatalogMenu($this->centerVars['ctg_id']);

    }

    /**
     * Функция получает от модели данные о товаре и сохраняет их в массиве,
     * который будет передан в шаблон center.php
     */
    private function getProduct() {

        // получаем от модели данные о товаре
        $product = $this->productCatalogFrontendModel->getProduct($this->params['id']);
        // если запрошенный товар не найден в БД
        if (empty($product)) {
            $this->notFoundRecord = true;
            return;
        }

        $this->title = $product['name'] . ' ' . $product['title'];
        if (!empty($product['keywords'])) {
            $this->keywords = $product['keywords'];
        }
        if (!empty($product['description'])) {
            $this->description = $product['description'];
        }

        // формируем хлебные крошки
        $breadcrumbs = $this->productCatalogFrontendModel->getCategoryPath($product['ctg_id']); // путь до категории
        // если товар размещен в двух категориях
        $breadcrumbs2 = null;
        if ( ! empty($product['second'])) {
            $breadcrumbs2 = $this->productCatalogFrontendModel->getCategoryPath($product['second']); // путь до категории
        }

        // технические характеристики
        $techdata = array();
        if ( ! empty($product['techdata'])) {
            $techdata = unserialize($product['techdata']);
        }

        // фото товара
        if ((!empty($product['image'])) && is_file('files/catalog/imgs/medium/' . $product['image'])) {
            $image['medium'] = $this->config->site->url . 'files/catalog/imgs/medium/' . $product['image'];
        } else {
            $image['medium'] = $this->config->site->url . 'files/catalog/imgs/medium/nophoto.jpg';
        }
        if ((!empty($product['image'])) && is_file('files/catalog/imgs/big/' . $product['image'])) {
            $image['big'] = $this->config->site->url . 'files/catalog/imgs/big/' . $product['image'];
        } else {
            $image['big'] = $this->config->site->url . 'files/catalog/imgs/big/nophoto.jpg';
        }

        // единицы измерения товара
        $units = $this->productCatalogFrontendModel->getUnits();

        // получаем от модели массив рекомендованных товаров
        $recommendedProducts = $this->basketFrontendModel->getRecommendedProducts($this->params['id']);
        
        // получаем от модели массив похожих товаров
        $likedProducts = $this->productCatalogFrontendModel->getLikedProducts(
            $this->params['id'],
            $product['grp_id'],
            $product['ctg_id'],
            $product['mkr_id'],
            $product['title']
        );

        /*
         * массив переменных, которые будут переданы в шаблон center.php
         */
        $this->centerVars = array(
            // уникальный идентификатор товара
            'id'           => $this->params['id'],
            // хлебные крошки
            'breadcrumbs'  => $breadcrumbs,
            // хлебные крошки
            'breadcrumbs2' => $breadcrumbs2,
            // URL этой страницы
            'thisPageUrl'  => $this->productCatalogFrontendModel->getURL('frontend/catalog/product/id/' . $this->params['id']),
            // заголовок h1 - торговое наименование товара
            'name'         => $product['name'],
            // заголовок h2 - функциональное наименование товара
            'title'        => $product['title'],
            // идентификатор родительской категории
            'ctg_id'       => $product['ctg_id'],
            // код (артикул) товара
            'code'         => $product['code'],
            // розничная цена
            'price'        => $product['price'],
            // цена, мелкий опт
            'price2'       => $product['price2'],
            // оптовая цена
            'price3'       => $product['price3'],
            // единица измерения
            'unit'         => $product['unit'],
            // массив единиц измерения товара
            'units'        => $units,
            // производитель
            'maker'        => array(
                'id'   => $product['mkr_id'],
                'name' => $product['mkr_name'],
                'url'  => $this->productCatalogFrontendModel->getURL('frontend/catalog/maker/id/' . $product['mkr_id'] . '/group/' . $product['grp_id']),
            ),
            // функциональная группа
            'group'        => array(
                'id'   => $product['grp_id'],
                'name' => $product['grp_name'],
                'url'  => $this->productCatalogFrontendModel->getURL('frontend/catalog/group/id/' . $product['grp_id'] . '/maker/' . $product['mkr_id']),
            ),
            // новый товар?
            'new'          => $product['new'],
            // лидер продаж?
            'hit'          => $product['hit'],
            // краткое описание
            'shortdescr'   => $product['shortdescr'],
            // фото товара
            'image'        => $image,
            // назначение изделия
            'purpose'      => $product['purpose'],
            // технические характеристики
            'techdata'     => $techdata,
            // особенности
            'features'     => $product['features'],
            // комплектация
            'complect'     => $product['complect'],
            // доп. оборудование
            'equipment'    => $product['equipment'],
            // доп. информация
            'padding'      => $product['padding'],
            // файлы документации
            'docs'         => $product['docs'],
            // файлы сертификатов
            'certs'        => $product['certs'],
            // атирибут action тега form формы для добавления товара в корзину, в избранное, в список сравнения
            'action'       => array(
                'basket'   => $this->productCatalogFrontendModel->getURL('frontend/basket/addprd'),
                'wished'   => $this->productCatalogFrontendModel->getURL('frontend/wished/addprd'),
                'compare'  => $this->productCatalogFrontendModel->getURL('frontend/compare/addprd'),
            ),
            // массив рекомендованных товаров
            'recommendedProducts' => $recommendedProducts,
            // массив похожих товаров
            'likedProducts' => $likedProducts,
        );

    }

}
