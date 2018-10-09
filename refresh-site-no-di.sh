#!/bin/sh

php5.6 bin/magento setup:upgrade
php5.6 bin/magento cache:clean
php5.6 bin/magento setup:static-content:deploy
chown www-data -R var && chown www-data -R pub
