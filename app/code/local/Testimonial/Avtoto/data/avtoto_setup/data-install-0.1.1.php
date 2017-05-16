<?php
$installer = $this;

$tiresCategory = Mage::getModel('catalog/category')->load(1);
$path = $tiresCategory->getPath() . '/';
$level = count(explode('/', $path))-1;

$categoryTable = $installer->getTable('catalog/category');

//main tires category
$connection = $tiresCategory->getResource()->getReadConnection();
$sql = "INSERT INTO " . $categoryTable . " (name, parent, products_count, products_count_admin, level, url_key, url_path ) " .
    " VALUES ('Шины', 1, 0, 0, '{$level}', 'shiny', 'shiny'); ";
$connection->query($sql);
$tiresCategoryId = $connection->lastInsertId();

$sql = "UPDATE SS_categories SET `path` = '1/{$tiresCategoryId}' WHERE categoryID = {$tiresCategoryId}";
$connection->query($sql);

$brandsTable = $installer->getTable('tire/tire_brands');
$directoryTable = $installer->getTable('tire/tire_directory');
$importRetailerTable = $installer->getTable('avtoto/import_retailer');

$tiresSeasonCategories = array (
    1 => array(
        'key' => 'letnie',
        'name' => 'Летние шины'
    ),
    2 => array(
        'key' => 'zimnie',
        'name' => 'Зимние шины'
    ),
    3 => array(
        'key' => 'vsesezonnye',
        'name' => 'Всесезонные шины'
    )
);

foreach($tiresSeasonCategories as $key => $value) {
    $sql = "INSERT INTO $categoryTable (`name`, parent, `path`, `level`)
      VALUES ('{$value['name']}', '{$tiresCategoryId}', CONCAT('1/', {$tiresCategoryId} ,'/' ), 2) ";
    $connection->query($sql);
    $tiresSeasonCategories[$key]['id'] = $connection->lastInsertId();

    $sql = "INSERT INTO SS_categories
        (`name`, parent,`path`, `level`)
         SELECT
            UPPER(REPLACE (brand_name, ' ', '')),
            {$tiresSeasonCategories[$key]['id']},
            CONCAT('1/', '{$tiresCategoryId}' ,'/', {$tiresSeasonCategories[$key]['id']}, '/' ),
            '3'
            FROM `{$brandsTable}` as tb
            INNER JOIN `{$directoryTable}` as tp ON tb.brand_id = tp.id_mod
            INNER JOIN `{$importRetailerTable}` as ir USING(code_normalized)
            WHERE tire_season = $key
            GROUP BY brand_id; ";
    $connection->query( $sql ) ;

    $sql = "UPDATE $categoryTable as c1
     SET c1.url_path = CONCAT ('shiny/', '{$tiresSeasonCategories[$key]['key']}', '/', c1.name)
     WHERE parent = '{$tiresSeasonCategories[$key]['id']}' ";
    $connection->query( $sql ) ;
}

$sql = "UPDATE $categoryTable SET path = CONCAT(path, categoryID ) WHERE `path` LIKE '1/{$tiresCategoryId}/%' ";
$connection->query( $sql );

$sql = "INSERT INTO SS_products
            (`categoryID`, `name`, retailer_price, enabled, in_stock, tires_id, date_added, avtomarks, description, type_id)

            SELECT
                    categories.`categoryID`,
                    CONCAT(
                     CASE tires.tire_season
                        WHEN 1 THEN 'Летняя'
                        WHEN 2 THEN 'Зимняя'
                        WHEN 3 THEN 'Всесезонная'
                     END,
                     ' шина ', UPPER(tires_brands.brand_name), ' ' , tires.tire_desc, ' ', tires.tire_size,
                        ' ', tires.tire_loading, UPPER(tires.tire_speed)),
                    tires.tire_price,
                    1,
                    tires.tire_balance,
                    tire_id,
                    TIMESTAMP(NOW()),
                    '',
                    'Tire',
                    'tire'
            FROM tires_products as tires
            INNER JOIN tires_loading ON tires.tire_loading = tires_loading.loading_id
            INNER JOIN tires_brands ON tires.id_mod = tires_brands.brand_id
            INNER JOIN import_retailer ON tires.code_normalized = import_retailer.code_normalized
            INNER JOIN SS_categories as categories ON UPPER(REPLACE(categories.`name`, ' ', '')) = UPPER(REPLACE(tires_brands.brand_name,
                ' ', ''))
            INNER JOIN SS_categories as categories2 ON categories2.categoryID = categories.parent AND tires.tire_season =
            CASE CONVERT( categories2.name USING utf8 )
                WHEN 'Летние' THEN 1
                WHEN 'Зимние' THEN 2
                WHEN 'Всесезонные' THEN 3
            END
            GROUP BY tire_id
            ";

$connection->query( $sql ) ;


$sql = "UPDATE `import_retailer` as prices
    INNER JOIN `tires_products` as directory USING (code_normalized)
        INNER JOIN `SS_products` as products ON products.tires_id = directory.tire_id

SET prices.product_id = products.productID";

$connection->query( $sql ) ;