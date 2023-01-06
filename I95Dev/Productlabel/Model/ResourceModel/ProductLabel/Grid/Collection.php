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
            ['cae'=>$this->getTable('catalog_category_entity')],
            'cae.entity_id = main_table.category_id',
            ['row_id']);
      $this->getSelect()->joinLeft(
            ['ca' => $this->getTable('catalog_category_entity_varchar')],
            'ca.row_id=cae.row_id',
            ['value AS category']
        );
     $this->getSelect()->joinLeft(
            ['eaov' => $this->getTable('eav_attribute_option_value')],
            'eaov.option_id = main_table.option_id',
            ['option_label' => 'value']
        )->group('main_table.product_label_id');

    }
}
