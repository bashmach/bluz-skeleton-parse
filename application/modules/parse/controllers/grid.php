<?php
/**
 *
 * @author   dev
 * @created  2015-09-17 10:57:04
 */
namespace Application;

use Application\Parse\TodoGrid;
use Bluz;

return
/**
 * @return \closure
 */
function () use ($view) {
    $view->grid = new TodoGrid();
};