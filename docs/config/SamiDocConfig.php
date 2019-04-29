<?php
/*******************************************************************
 *
 * (c) 2019 Stephan Preßl, www.prestep.at <development@prestep.at>
 * All rights reserved
 *
 * Modification, distribution or any other action on or with
 * this file is permitted unless explicitly granted by IIDO
 * www.iido.at <development@iido.at>
 *
 *******************************************************************/

use Sami\Sami;
use Symfony\Component\Finder\Finder;

$iterator = Finder::create()
    ->files()
    ->name('*.php')
    ->exclude('Resources')
    ->exclude('ContaoManager')
    ->exclude('IIDOBasicBundle')
    ->in('./src');

return new Sami($iterator, [
    'title'     => 'Contao IIDO Basic Bundle',
    'build_dir' => __DIR__ . '/../build',
    'cache_dir' => __DIR__ . '/../cache'
]);