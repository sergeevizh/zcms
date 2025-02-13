<?php
/**
 * Класс Addprd_Wished_Frontend_Controller отвечает за добавление товара в список
 * отложенных (избранное), взаимодействует с моделью Wished_Frontend_Model,
 * общедоступная часть сайта
 */
class Addprd_Wished_Frontend_Controller extends Wished_Frontend_Controller {

    public function __construct($params = null) {
        parent::__construct($params);
    }

    /**
     * Функция добавляет товар в список отложенных и делает редирект на страницу
     * со списком отложенных товаров или обратно на страницу, где была нажата
     * кнопка «Добавить в отложенные»
     */
    protected function input() {

        // данные должны быть отправлены методом POST
        if ( ! $this->isPostMethod()) {
            $this->notFoundRecord = true;
            return;
        }

        // если не передан id товара или id товара не число
        if ( ! (isset($_POST['product_id']) && ctype_digit($_POST['product_id']) && $_POST['product_id'] > 0)) {
            $this->notFoundRecord = true;
            return;
        } else {
            $product_id = (int)$_POST['product_id'];
        }

        // добавляем товар в список отложенных
        $this->wishedFrontendModel->addToWished($product_id);

        // куда перенаправить посетителя после добавления товара в список отложенных?
        if (!isset($_POST['return'])) {
            $this->redirect($this->wishedFrontendModel->getURL('frontend/wished/index'));
        }

        $url = 'frontend/wished/index';
        if ($_POST['return'] == 'product') { // перенаправляем на страницу товара
            if (isset($_POST['return_prd_id']) && ctype_digit($_POST['return_prd_id'])) {
                $url = 'frontend/catalog/product/id/' . $_POST['return_prd_id'];
            }
        } elseif ($_POST['return'] == 'category') { // перенаправляем на страницу категории
            if (isset($_POST['return_ctg_id']) && ctype_digit($_POST['return_ctg_id'])) {
                $url = 'frontend/catalog/category/id/' . $_POST['return_ctg_id'];
                if (isset($_POST['group']) && ctype_digit($_POST['group']) && $_POST['group'] > 0) {
                    $url = $url . '/group/' . $_POST['group'];
                }
                if (isset($_POST['maker']) && ctype_digit($_POST['maker']) && $_POST['maker'] > 0) {
                    $url = $url . '/maker/' . $_POST['maker'];
                }
                if (isset($_POST['hit']) && $_POST['hit'] == 1) {
                    $url = $url . '/hit/1';
                }
                if (isset($_POST['new']) && $_POST['new'] == 1) {
                    $url = $url . '/new/1';
                }
                if (isset($_POST['filter']) && preg_match('~^\d+\.\d+(-\d+\.\d+)*$~', $_POST['filter'])) {
                    $url = $url . '/filter/' . $_POST['filter'];
                }
                if (isset($_POST['sort']) && ctype_digit($_POST['sort']) && $_POST['sort'] > 0) {
                    $url = $url . '/sort/' . $_POST['sort'];
                }
                if (isset($_POST['perpage']) && ctype_digit($_POST['perpage'])) { // TODO: проверка in_array()
                    $url = $url . '/perpage/' . $_POST['perpage'];
                }
                if (isset($_POST['page']) && ctype_digit($_POST['page']) && $_POST['page'] > 1) {
                    $url = $url . '/page/' . $_POST['page'];
                }
            }
        } elseif ($_POST['return'] == 'maker') { // перенаправляем на страницу производителя
            if (isset($_POST['return_mkr_id']) && ctype_digit($_POST['return_mkr_id'])) {
                $url = 'frontend/catalog/maker/id/' . $_POST['return_mkr_id'];
                if (isset($_POST['group']) && ctype_digit($_POST['group']) && $_POST['group'] > 0) {
                    $url = $url . '/group/' . $_POST['group'];
                }
                if (isset($_POST['hit']) && $_POST['hit'] == 1) {
                    $url = $url . '/hit/1';
                }
                if (isset($_POST['new']) && $_POST['new'] == 1) {
                    $url = $url . '/new/1';
                }
                if (isset($_POST['filter']) && preg_match('~^\d+\.\d+(-\d+\.\d+)*$~', $_POST['filter'])) {
                    $url = $url . '/filter/' . $_POST['filter'];
                }
                if (isset($_POST['sort']) && ctype_digit($_POST['sort']) && $_POST['sort'] > 0) {
                    $url = $url . '/sort/' . $_POST['sort'];
                }
                if (isset($_POST['perpage']) && ctype_digit($_POST['perpage'])) { // TODO: проверка in_array()
                    $url = $url . '/perpage/' . $_POST['perpage'];
                }
                if (isset($_POST['page']) && ctype_digit($_POST['page']) && $_POST['page'] > 1) {
                    $url = $url . '/page/' . $_POST['page'];
                }
            }
        } elseif ($_POST['return'] == 'group') { // перенаправляем на страницу функциональной группы
            if (isset($_POST['return_grp_id']) && ctype_digit($_POST['return_grp_id'])) {
                $url = 'frontend/catalog/group/id/' . $_POST['return_grp_id'];
                if (isset($_POST['maker']) && ctype_digit($_POST['maker']) && $_POST['maker'] > 0) {
                    $url = $url . '/maker/' . $_POST['maker'];
                }
                if (isset($_POST['hit']) && $_POST['hit'] == 1) {
                    $url = $url . '/hit/1';
                }
                if (isset($_POST['new']) && $_POST['new'] == 1) {
                    $url = $url . '/new/1';
                }
                if (isset($_POST['filter']) && preg_match('~^\d+\.\d+(-\d+\.\d+)*$~', $_POST['filter'])) {
                    $url = $url . '/filter/' . $_POST['filter'];
                }
                if (isset($_POST['sort']) && ctype_digit($_POST['sort']) && $_POST['sort'] > 0) {
                    $url = $url . '/sort/' . $_POST['sort'];
                }
                if (isset($_POST['perpage']) && ctype_digit($_POST['perpage'])) { // TODO: проверка in_array()
                    $url = $url . '/perpage/' . $_POST['perpage'];
                }
                if (isset($_POST['page']) && ctype_digit($_POST['page']) && $_POST['page'] > 1) {
                    $url = $url . '/page/' . $_POST['page'];
                }
            }
        } elseif ($_POST['return'] == 'compare') { // перенаправляем на страницу отложенных для сравнения товаров
            $url = 'frontend/compare/index';
            if (isset($_POST['page']) && ctype_digit($_POST['page']) && $_POST['page'] > 1) {
                $url = $url . '/page/' . $_POST['page'];
            }
        } elseif ($_POST['return'] == 'viewed') { // перенаправляем на страницу просмотренных товаров
            $url = 'frontend/viewed/index';
            if (isset($_POST['page']) && ctype_digit($_POST['page']) && $_POST['page'] > 1) {
                $url = $url . '/page/' . $_POST['page'];
            }
        } elseif ($_POST['return'] == 'search') { // перенаправляем на страницу результатов поиска по каталогу
            if (!empty($_POST['query'])) {
                $query = rawurlencode(iconv_substr($_POST['query'], 0, 64));
                $url = 'frontend/catalog/search/query/' . $query;
                if (isset($_POST['page']) && ctype_digit($_POST['page']) && $_POST['page'] > 1) {
                    $url = $url . '/page/' . $_POST['page'];
                }
            } else {
                $url = 'frontend/catalog/search';
            }
        }

        $this->redirect($this->wishedFrontendModel->getURL($url));

    }

}