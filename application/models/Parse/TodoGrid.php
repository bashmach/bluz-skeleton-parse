<?php

namespace Application\Parse;

use Application\Parse\Grid\Source\ParseSource;

/**
 * @category Application
 * @package  Parse
 */
class TodoGrid extends \Bluz\Grid\Grid
{
    protected $uid = 'todo-parse-grid';

    /**
     * init
     *
     * @return self
     */
    public function init()
    {
        $adapter = new ParseSource(); // откуда возьмется этот адаптер будет описано ниже
        $adapter->setSource("Todo"); // указываем имя необходимой коллекции

        $this->setAdapter($adapter);
        $this->setDefaultLimit(25);

        // перечисляем все свойства, по которым разрешаем фильтрацию и сортировку
        $this->setAllowOrders(['author', 'text', 'done']);
        $this->setAllowFilters(['author', 'text', 'done']);

        return $this;
    }
}