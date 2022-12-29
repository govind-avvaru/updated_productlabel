<?php

declare(strict_types=1);

namespace I95Dev\Productlabel\Model\ResourceModel\ProductLabel\Grid;

use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\Document;
use Smile\ProductLabel\Model\ResourceModel\ProductLabel\Collection as SmileProductLabelCollection;

/**
 * Product Label Grid Collection
 */
class Collection extends \Smile\ProductLabel\Model\ResourceModel\ProductLabel\Grid\Collection
{
    private AggregationInterface $aggregations;

    /**
     * Construct.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setModel(Document::class);
    }

    /**
     * Render filters before
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @inheritdoc
     */
    protected function _renderFiltersBefore()
    {
        $this->getSelect()->joinInner(
            ['ea' => $this->getTable('eav_attribute')],
            'ea.attribute_id = main_table.attribute_id',
            ['frontend_label']
        );

        $this->getSelect()->joinLeft(
            ['eaov' => $this->getTable('eav_attribute_option_value')],
            'eaov.option_id = main_table.option_id',
            ['option_label' => 'value']
        );

    }
}
