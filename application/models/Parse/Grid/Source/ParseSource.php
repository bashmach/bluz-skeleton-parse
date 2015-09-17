<?php

namespace Application\Parse\Grid\Source;

use Bluz\Grid;
use Bluz\Grid\Source\AbstractSource;
use Parse\ParseQuery;

class ParseSource extends AbstractSource
{
    /**
     * @var string
     */
    protected $collectionName;

    /**
     * @var ParseQuery
     */
    protected $source;

    /**
     * @param mixed $collectionName
     * @return $this
     */
    public function setSource($collectionName)
    {
        $this->collectionName = $collectionName;
        $this->source = new ParseQuery($collectionName);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function process(array $settings = [])
    {
        $this->processOrders($settings);
        $this->processFilters($settings);
        $this->source->limit($settings['limit']);
        $this->source->skip(($settings['page'] - 1) * $settings['limit']);

        // run queries
        $gridData = new Grid\Data($this->source->find());

        // get all records to set grid total
        // @todo: cache result of total query for few minutes
        $this->source = new ParseQuery($this->collectionName);
        $this->processOrders($settings);
        $this->processFilters($settings);
        $gridData->setTotal(sizeof($this->source->limit(1000)->find()));

        return $gridData;
    }

    /**
     * Apply order settings to the source
     *
     * @param array $settings
     */
    protected function processOrders(array $settings = [])
    {
        foreach ($settings['orders'] as $column => $order) {
            switch ($order) {
                case Grid\Grid::ORDER_ASC:
                    $this->source->addAscending($column);
                    break;
                case Grid\Grid::ORDER_DESC:
                    $this->source->addDescending($column);
                    break;
                default:
                    break;
            }
        }
    }

    /**
     * Apply filter settings to the source
     *
     * @param array $settings
     */
    protected function processFilters(array $settings = [])
    {
        foreach ($settings['filters'] as $column => $filter) {
            switch (key($filter)) {
                case Grid\Grid::FILTER_EQ:
                    $this->source->equalTo($column, reset($filter));
                    break;
                case Grid\Grid::FILTER_LIKE:
                    $this->source->startsWith($column, reset($filter));
                    break;
                default:
                    // throw NotImplemented?
                    break;
            }
        }
    }
}