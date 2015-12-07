<?php
/**
 * Абстрактный класс Base, родительский для всех контроллеров,
 * моделей, представлений
 */
abstract class Base {

    /**
     * для хранения всех объектов приложения, экземпляр класса Register
     */
    protected $register;

    /**
     * настройки приложения, экземпляр класса Config
     */
    protected $config;
    
    /**
     * для хранения экземпляра класса базы данных Database
     */
    protected $database;

    /**
     * административная часть сайта?
     */
    protected $backend = false;


    public function __construct() {

        // все объекты приложения, экземпляр класса Register
        $this->register = Register::getInstance();
        // настройки приложения, экземпляр класса Config
        $this->config = Config::getInstance();
        // экземпляр класса базы данных
        $this->database = Database::getInstance();
        // административная часть сайта?
        $this->backend = $this->register->router->isBackend();
        // сохраняем в реестре экземпляр текущего класса
        $class = str_replace('_', '', lcfirst(get_class($this)));
        if (isset($this->register->$class)) {
            throw new Exception('Попытка создать второй экземпляр класса ' . get_class($this));
        }
        $this->register->$class = $this;

    }

    /**
     * Функция осуществляет редирект на переданный в качестве параметра URL
     */
    protected function redirect($url) {
        header('Location: ' . $url);
        die();
    }

    /**
     * Функция возвращает true, если данные пришли методом POST
     */
    protected function isPostMethod() {
        /*
         * Условие if не имеет отношения к обычной работе приложения, когда формируется
         * страница сайта по запросу от браузера. Настройка $this->config->cache->make
         * установлена в конфигурации, когда приложение запущено из командной строки для
         * формирования кэша. См. комментарии в начале конструктора класса Router в файле
         * app/include/Router.php и исходный код файла cache/make-cache.php.
         */
        if (isset($this->config->cache->make)) {
            return false;
        }
        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }

    /*
     * Четыре функции для обмена данными между страницами с помощью сессий.
     * Если требуется предать какие-то данные от одной страницы другой, то
     * первая вызывает setSessionData(), вторая вызывает getSessionData().
     */

    /**
     * Функция сохраняет данные в сессии
     */
    protected function setSessionData($key, $data) {
        $_SESSION['zcmsSessionData'][$key] = $data;
    }

    /**
     * Функция возвращает сохраненные в сессии данные
     */
    protected function getSessionData($key) {
        if (!isset($_SESSION['zcmsSessionData'][$key])) {
            throw new Exception('Данные сессии с ключом ['.$key.'] не найдены');
        }
        return $_SESSION['zcmsSessionData'][$key];
    }

    /**
     * Функция удаляет сохраненные в сессии данные
     */
    protected function unsetSessionData($key) {
        if (isset($_SESSION['zcmsSessionData'][$key])) {
            unset($_SESSION['zcmsSessionData'][$key]);
        }
    }

    /**
     * Функция проверяет существование сохраненных в сессии данных
     */
    protected function issetSessionData($key) {
        return isset($_SESSION['zcmsSessionData'][$key]);
    }

}