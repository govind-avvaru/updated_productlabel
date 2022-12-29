<?php

declare(strict_types=1);

namespace I95Dev\Productlabel\Block\ProductLabel;

use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Smile\ProductLabel\Api\Data\ProductLabelInterface;
use Smile\ProductLabel\Model\ImageLabel\Image;
use Smile\ProductLabel\Model\ResourceModel\ProductLabel\CollectionFactory as ProductLabelCollectionFactory;
/**
 * Class ProductLabel template
 */
class ProductLabel extends \Smile\ProductLabel\Block\ProductLabel\ProductLabel
{
    protected Registry $registry;

    protected ProductLabelCollectionFactory $productLabelCollectionFactory;

    protected Image $imageHelper;

    protected ProductInterface $product;

    private CacheInterface $cache;

    private StoreManagerInterface $storeManager;

    /**
     * ProductLabel constructor.
     *
     * @param Context $context Block context
     * @param Registry $registry Registry
     * @param Image $imageHelper Image Helper
     * @param ProductLabelCollectionFactory $productLabelCollectionFactory Product Label Collection Factory
     * @param CacheInterface $cache Cache Interface
     * @param array $data Block data
     */
    public function __construct(
        Context                       $context,
        Registry                      $registry,
        Image                         $imageHelper,
        ProductLabelCollectionFactory $productLabelCollectionFactory,
        CacheInterface                $cache,
        ProductInterface              $product,
        array                         $data = []
    ) {
        $this->registry                      = $registry;
        $this->imageHelper                   = $imageHelper;
        $this->productLabelCollectionFactory = $productLabelCollectionFactory;
        $this->cache                         = $cache;
        $this->storeManager                  = $context->getStoreManager();
        $this->product                       = $product;
        parent::__construct($context,$registry,$imageHelper,$productLabelCollectionFactory,$cache,$product,$data);
    }

    /**
     * Check if product has product labels
     *
     * If it has, return an array of product labels
     *
     * @return array
     */
    public function getProductLabels(): array
    {
        $productLabels     = [];
        $productLabelList  = $this->getProductLabelsList();
        $attributesProduct = $this->getAttributesOfCurrentProduct();
        foreach ($productLabelList as $productLabel) {
            $attributeIdLabel = $productLabel['attribute_id'];
            $optionIdLabel    = $productLabel['option_id'];
            $categoryLabelId  =$productLabel['category_id'];
            $currentCategory = $this->registry->registry('current_category');
            if(isset($currentCategory))
            {
                $currentCategoryId=$currentCategory->getId();
            }

            if(empty($categoryLabelId)) {
                foreach ($attributesProduct as $attribute) {
                    if (isset($attribute['id']) && ($attributeIdLabel == $attribute['id'])) {
                        $options = $attribute['options'] ?? [];
                        if (!is_array($options)) {
                            $options = explode(',', $options);
                        }
                        if (
                            in_array($optionIdLabel, $options) &&
                            in_array($this->getCurrentView(), $productLabel['display_on'])
                        ) {
                            $productLabel['class'] = $this->getCssClass($productLabel);
                            $productLabel['image'] = $this->getImageUrl($productLabel['image']);
                            $class = $this->getCssClass($productLabel);
                            $productLabels[$class][] = $productLabel;
                        }
                    }
                }
            }
            else{
                 if(isset($currentCategoryId)){
                if($currentCategoryId==$categoryLabelId) {
                    if (in_array($this->getCurrentView(), $productLabel['display_on'])) {
                        $productLabel['class'] = $this->getCssClass($productLabel);
                        $productLabel['image'] = $this->getImageUrl($productLabel['image']);
                        $class = $this->getCssClass($productLabel);
                        $productLabels[$class][] = $productLabel;
                    }
                }
              }
            }
        }
        return $productLabels;
    }

    /**
     * Fetch proper css class according to current label and view.
     *
     * @param array $productLabel A product Label
     */
    private function getCssClass(array $productLabel): string
    {
        $class = '';

        if ($this->getCurrentView() === ProductLabelInterface::PRODUCTLABEL_DISPLAY_PRODUCT) {
            $class = $productLabel['position_product_view'] . ' product';
        }

        if ($this->getCurrentView() === ProductLabelInterface::PRODUCTLABEL_DISPLAY_LISTING) {
            $class = $productLabel['position_category_list'] . ' category';
        }

        return $class;
    }

    /**
     * Fetch product labels list : the list of all enabled product labels.
     *
     * Fetched only once and put in cache.
     *
     * @return array
     */
    private function getProductLabelsList(): array
    {
        $storeId          = $this->getStoreId();
        $cacheKey         = 'smile_productlabel_frontend_' . $storeId;
        $productLabelList = $this->cache->load($cacheKey);

        if (is_string($productLabelList)) {
            $productLabelList = json_decode($productLabelList, true);
        }

        if ($productLabelList === false) {
            /** @var \Smile\ProductLabel\Model\ResourceModel\ProductLabel\CollectionFactory */
            $productLabelsCollection = $this->productLabelCollectionFactory->create();

            // @phpstan-ignore-next-line
            $productLabelList = $productLabelsCollection
                ->addStoreFilter($storeId)
                ->addFieldToFilter('is_active', true)
                ->getData();

            $productLabelList = array_map(function ($label) {
                $label['display_on'] = explode(',', $label['display_on']);

                return $label;
            }, $productLabelList);

            $this->cache->save(
                json_encode($productLabelList),
                $cacheKey,
                [\Smile\ProductLabel\Model\ProductLabel::CACHE_TAG]
            );
        }

        return $productLabelList;
    }
    /**
     * Get current store Id.
     */
    private function getStoreId(): int
    {
        return (int) $this->storeManager->getStore()->getId();
    }
}
