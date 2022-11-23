# HRX delivery module for Magento 2.0 - 2.4 versions

## Before installing

Shop information must be configured.

Depending on whats done with magento might need marketplace.magento.com user to get access tokens for composer. Instructions: https://devdocs.magento.com/guides/v2.4/install-gde/prereq/connect-auth.html

## Installation - FTP **(Step 1)**

Copy `app` folder into magento root folder. Continue with terminal commands.

## Installation - Terminal commands **(Step 2)**


```
composer require hrx/api-lib

php bin/magento setup:upgrade

php -d memory_limit=2G bin/magento setup:di:compile

php -d memory_limit=2G bin/magento setup:static-content:deploy --language lt_LT

php -d memory_limit=2G bin/magento setup:static-content:deploy --language en_US

php bin/magento cache:flush
```

## Configuration **(Step 3)**

Stores -> Configuration, select Sales -> Shipping Methods / Delivery methods -> HRX delivery

Fill in all the required information.

## Important, for Magento 2.0 version

After install, rename file Hrx/Shipping/view/adminhtml/layout/sales_order_view.xml to Hrx/Shipping/view/adminhtml/layout/sales_order_view_.xml, due to parcel terminal displaying in Magento Order view is not supported