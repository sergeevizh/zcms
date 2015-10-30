<?php
/**
 * Список товаров выбранного производителя,
 * файл view/example/frontend/template/catalog/maker/center.php,
 * общедоступная часть сайта
 *
 * Переменные, которые приходят в шаблон:
 * $breadcrumbs - хлебные крошки
 * $id - уникальный идентификатор производителя
 * $name - наименование производителя
 * $thisPageUrl - URL этой страницы
 * $products - массив товаров производителя
 * $sort - выбранная сортировка
 * $sortorders - массив всех вариантов сортировки
 * $units - массив единиц измерения товара
 * $pager - постраничная навигация
 * $page - текущая страница
 *
 * $products = Array (
 *   [0] => Array (
 *     [id] => 230524
 *     [code] => 230524
 *     [name] => AVP-453 (PAL)
 *     [title] => Видеопанель вызывная цветная
 *     [image] => 6/9/690535d0ce3fd37599827a20d9ced8de.jpg
 *     [price] => 3159
 *     [unit] => 1
 *     [shortdescr] => Дверной блок, накладной, ЛС 4-х пров.; 420 Твл, ИК-подветка; -50…+50°С; 140х70х20 мм
 *     [ctg_id] => 844
 *     [ctg_name] => Видеопенели вызывные
 *     [url] => Array (
 *       [product] => /catalog/product/230524
 *       [image] => /files/catalog/products/small/6/9/690535d0ce3fd37599827a20d9ced8de.jpg
 *     )
 *     [action] => Array (
 *       [basket] => /basket/addprd
 *       [wished] => /wished/addprd
 *       [compared] => /compared/addprd
 *     )
 *   )
 *   [1] => Array (
 *     ..........
 *   )
 *   ..........
 * )
 *
 * $sortorders = Array (
 *   [0] => Array (
 *     [url] => /catalog/maker/74
 *     [name] => без сортировки
 *   )
 *   [1] => Array (
 *     [url] => /catalog/maker/74/sort/1
 *     [name] => цена, возр.
 *   )
 *   [2] => Array (
 *     [url] => /catalog/maker/74/sort/2
 *     [name] => цена, убыв.
 *   )
 *   [3] => Array (
 *     [url] => /catalog/maker/74/sort/3
 *     [name] => название, возр.
 *   )
 *   [4] => Array (
 *     [url] => /catalog/maker/74/sort/4
 *     [name] => название, убыв.
 *   )
 *   [5] => Array (
 *     [url] => /catalog/maker/74/sort/5
 *     [name] => код, возр.
 *   )
 *   [6] => Array (
 *     [url] => /catalog/maker/74/sort/6
 *     [name] => код, убыв.
 *   )
 * )
 *
 * $units = Array (
 *     0 => 'руб',
 *     1 => 'руб/шт',
 *     2 => 'руб/компл',
 *     3 => 'руб/упак',
 *     4 => 'руб/метр',
 *     5 => 'руб/пара',
 * )
 *
 * $pager = Array (
 *     [first] => 1
 *     [prev] => 2
 *     [current] => 3
 *     [next] => 4
 *     [last] => 5
 *     [left] => Array (
 *         [0] => 2
 *     )
 *     [right] => Array (
 *         [0] => 4
 *     )
 * )
 *
 */

defined('ZCMS') or die('Access denied');

/*
 * Варианты сортировки:
 * 0 - по умолчанию,
 * 1 - по цене, по возрастанию
 * 2 - по цене, по убыванию
 * 3 - по наименованию, по возрастанию
 * 4 - по наименованию, по убыванию
 * 5 - по коду, по возрастанию
 * 6 - по коду, по убыванию
 * Можно переопределить текст по умолчанию:
 */
for ($i = 0; $i <= 6; $i++) {
    switch ($i) {
        case 0: $text = 'прайс-лист'; $class = '';               break;
        case 1: $text = 'цена';       $class = 'sort-asc-blue';  break;
        case 2: $text = 'цена';       $class = 'sort-desc-blue'; break;
        case 3: $text = 'название';   $class = 'sort-asc-blue';  break;
        case 4: $text = 'название';   $class = 'sort-desc-blue'; break;
        case 5: $text = 'код';        $class = 'sort-asc-blue';  break;
        case 6: $text = 'код';        $class = 'sort-desc-blue'; break;
    }
    if ($sort && $i == $sort) {
        $class = str_replace('blue', 'orange', $class);
    }
    $sortorders[$i]['name'] = $text;
    $sortorders[$i]['class'] = $class;
}

?>

<!-- Начало шаблона view/example/frontend/template/catalog/maker/center.php -->

<?php if (!empty($breadcrumbs)): // хлебные крошки ?>
    <div id="breadcrumbs">
    <?php foreach ($breadcrumbs as $item): ?>
        <a href="<?php echo $item['url']; ?>"><?php echo $item['name']; ?></a>&nbsp;&gt;
    <?php endforeach; ?>
    </div>
<?php endif; ?>

<h1><?php echo $name; ?></h1>

<?php if (empty($products)): // нет товаров у выбранного производителя ?>
    <p>Нет товаров у выбранного производителя</p>
    <?php return; ?>
<?php endif; ?>

<div id="sort-orders">
    <ul>
        <li>Сортировка</li>
    <?php foreach($sortorders as $key => $value): ?>
        <li>
            <?php if ($key == $sort): ?>
                <span class="selected<?php echo (!empty($value['class'])) ? ' ' . $value['class'] : ''; ?>"><?php echo $value['name']; ?></span>
            <?php else: ?>
                <a href="<?php echo $value['url']; ?>"<?php echo (!empty($value['class'])) ? ' class="' . $value['class'] . '"' : ''; ?>><span><?php echo $value['name']; ?></span></a>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
    </ul>
</div>

<div class="products-list-line">
    <?php foreach ($products as $product): ?>
        <div>
            <div class="product-line-heading">
                <h2><a href="<?php echo $product['url']['product']; ?>"><?php echo $product['name']; ?></a></h2>
                <?php if (!empty($product['title'])): ?>
                    <h3><?php echo $product['title']; ?></h3>
                <?php endif; ?>
            </div>
            <div class="product-line-image">
                <a href="<?php echo $product['url']['product']; ?>"><img src="<?php echo $product['url']['image']; ?>" alt="" /></a>
            </div>
            <div class="product-line-info">
                <div>
                    <span>Цена, <?php echo $units[$product['unit']]; ?>:</span>
                    <span>
                        <span><strong><?php echo number_format($product['price'], 2, '.', ''); ?></strong><span>розничная</span></span>
                        <span><strong><?php echo number_format($product['price'], 2, '.', ''); ?></strong><span>мелкий опт</span></span>
                        <span><strong><?php echo number_format($product['price'], 2, '.', ''); ?></strong><span>оптовая</span></span>
                    </span>
                </div>
                <div>
                    <span>Код:</span>
                    <span><strong><?php echo $product['code']; ?></strong></span>
                </div>
                <div>
                    <span>Производитель:</span>
                    <span><span class="selected"><?php echo $name; ?></span></span>
                </div>
            </div>
            <div class="product-line-basket">
                <form action="<?php echo $product['action']['basket']; ?>" method="post" class="add-basket-form">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>" />
                    <input type="text" name="count" value="1" size="5" />
                    <input type="hidden" name="return" value="maker" />
                    <input type="hidden" name="return_mkr_id" value="<?php echo $id; ?>" />
                    <?php if ($sort): ?>
                        <input type="hidden" name="sort" value="<?php echo $sort; ?>" />
                    <?php endif; ?>
                    <?php if ($page > 1): ?>
                        <input type="hidden" name="page" value="<?php echo $page; ?>" />
                    <?php endif; ?>
                    <input type="submit" name="submit" value="В корзину" title="Добавить в корзину" />
                </form>
                <form action="<?php echo $product['action']['wished']; ?>" method="post" class="add-wished-form">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>" />
                    <input type="hidden" name="return" value="maker" />
                    <input type="hidden" name="return_mkr_id" value="<?php echo $id; ?>" />
                    <?php if ($sort): ?>
                        <input type="hidden" name="sort" value="<?php echo $sort; ?>" />
                    <?php endif; ?>
                    <?php if ($page > 1): ?>
                        <input type="hidden" name="page" value="<?php echo $page; ?>" />
                    <?php endif; ?>
                    <input type="submit" name="submit" value="Отложить" title="Добавить в отложенные" />
                </form>
                <form action="<?php echo $product['action']['compared']; ?>" method="post" class="add-compared-form">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>" />
                    <input type="hidden" name="return" value="maker" />
                    <input type="hidden" name="return_mkr_id" value="<?php echo $id; ?>" />
                    <?php if ($sort): ?>
                        <input type="hidden" name="sort" value="<?php echo $sort; ?>" />
                    <?php endif; ?>
                    <?php if ($page > 1): ?>
                        <input type="hidden" name="page" value="<?php echo $page; ?>" />
                    <?php endif; ?>
                    <input type="submit" name="submit" value="К сравнению" title="Добавить к сравнению" />
                </form>
            </div>
            <div class="product-line-descr"><?php echo $product['shortdescr']; ?></div>
        </div>
    <?php endforeach; ?>
</div>

<?php if (!empty($pager)): // постраничная навигация ?>
    <ul class="pager">
    <?php if (isset($pager['first'])): ?>
        <li>
            <a href="<?php echo $thisPageUrl; ?><?php echo ($pager['first'] != 1) ? '/page/'.$pager['first'] : ''; ?>" class="first-page"></a>
        </li>
    <?php endif; ?>
    <?php if (isset($pager['prev'])): ?>
        <li>
            <a href="<?php echo $thisPageUrl; ?><?php echo ($pager['prev'] != 1) ? '/page/'.$pager['prev'] : ''; ?>" class="prev-page"></a>
        </li>
    <?php endif; ?>
    <?php if (isset($pager['left'])): ?>
        <?php foreach ($pager['left'] as $left) : ?>
            <li>
                <a href="<?php echo $thisPageUrl; ?><?php echo ($left != 1) ? '/page/'.$left : ''; ?>"><?php echo $left; ?></a>
            </li>
        <?php endforeach; ?>
    <?php endif; ?>

        <li>
            <span><?php echo $pager['current']; // текущая страница ?></span>
        </li>

    <?php if (isset($pager['right'])): ?>
        <?php foreach ($pager['right'] as $right) : ?>
            <li>
                <a href="<?php echo $thisPageUrl; ?>/page/<?php echo $right; ?>"><?php echo $right; ?></a>
            </li>
        <?php endforeach; ?>
    <?php endif; ?>
    <?php if (isset($pager['next'])): ?>
        <li>
            <a href="<?php echo $thisPageUrl; ?>/page/<?php echo $pager['next']; ?>" class="next-page"></a>
        </li>
    <?php endif; ?>
    <?php if (isset($pager['last'])): ?>
        <li>
            <a href="<?php echo $thisPageUrl; ?>/page/<?php echo $pager['last']; ?>" class="last-page"></a>
        </li>
    <?php endif; ?>
    </ul>
<?php endif; ?>

<!-- Конец шаблона view/example/frontend/template/catalog/maker/center.php -->

