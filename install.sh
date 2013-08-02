ROOT=~/workspace/
cd $ROOT
curl -s http://getcomposer.org/installer | php
php composer.phar install
#configure apache and shit
chmod -R 777 logs cache

