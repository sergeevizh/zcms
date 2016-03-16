<?php
/**
 * Класс Index_Index_Frontend_Controller фомирует главную страницу общедоступной части сайта
 */
class Index_Index_Frontend_Controller extends Frontend_Controller {

    public function __construct($params = null) {
        parent::__construct($params);
    }

    /**
     * Функция получает от модели данные, необходимые для формирования главной
     * страницы сайта
     */
    protected function input() {

        /*
         * сначала обращаемся к родительскому классу Frontend_Controller, чтобы
         * установить значения по умолчанию для всех переменных, необходимых для
         * формирования главной страницы сайта, потом переопределяем их значения,
         * если необходимо
         */
        parent::input();

        // получаем от модели данные о главной странице
        $index = $this->indexFrontendModel->getIndexPage();

        $this->title = $index['title'];
        if ( ! empty($index['keywords'])) {
            $this->keywords = $index['keywords'];
        }
        if ( ! empty($index['description'])) {
            $this->description = $index['description'];
        }

        // получаем от модели массив баннеров
        $banners = $this->indexFrontendModel->getAllBanners();

        // получаем от модели массив последних новостей
        $companyNews = $this->indexFrontendModel->getCompanyNews();
        $generalNews = $this->indexFrontendModel->getGeneralNews();

        // получаем от модели массив лидеров продаж
        $hitProducts = $this->indexFrontendModel->getHitProducts();

        // получаем от модели массив новых товаров
        $newProducts = $this->indexFrontendModel->getNewProducts();
        
        // единицы измерения товара
        $units = $this->indexFrontendModel->getUnits();

        /*
         * переменные, которые будут переданы в шаблон center.php
         */
        $this->centerVars = array(
            // заголовок h1 главной страницы
            'name'        => $index['name'],
            // текст главной страницы в формате html
            'text'        => $index['body'],
            // массив баннеров
            'banners'     => $banners,
            // массив лидеров продаж
            'hitProducts' => $hitProducts,
            // массив новых товаров
            'newProducts' => $newProducts,
            // массив единиц измерения товара
            'units'       => $units,
            // массив новостей компании
            'companyNews' => $companyNews,
            // массив событий отрасли
            'generalNews' => $generalNews,
        );

    }

}
