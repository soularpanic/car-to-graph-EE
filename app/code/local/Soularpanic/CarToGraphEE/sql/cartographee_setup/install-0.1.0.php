<?php

$installer = $this;
$installer->startSetup();

//$installer->addEntityType('cartographee_car', [
//    'entity_model'  => 'cartographee/car',
//    'table'         => 'cartographee/car'
//]);
//
//$installer->createEntityTables(
//    $this->getTable('cartographee/car')
//);
$installer->run("
    CREATE TABLE `{$installer->getTable('cartographee/car')}` (
      `entity_id` INT(10) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
      `alt_id` VARCHAR(514) UNIQUE,
      `make` VARCHAR(255),
      `model` VARCHAR(255),
      `year` VARCHAR(4)
    ) ENGINE=INNODB;

    CREATE TABLE `{$installer->getTable('cartographee/car_product')}` (
      `entity_id` INT(10) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
      `car_id` INT(10) UNSIGNED NOT NULL,
      `product_id` INT(10) UNSIGNED NOT NULL,
      `option` VARCHAR(255),
      `type` VARCHAR(255),
      `preselect_ids` VARCHAR(255) DEFAULT NULL,
      FOREIGN KEY (car_id)
        REFERENCES {$installer->getTable('cartographee/car')}(entity_id)
        ON DELETE CASCADE,
      FOREIGN KEY (product_id)
        REFERENCES {$installer->getTable('catalog/product')}(entity_id)
        ON DELETE CASCADE
    ) ENGINE=INNODB;
");

$installer->endSetup();