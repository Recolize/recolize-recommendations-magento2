<?php
/**
 * Recolize GmbH
 *
 * @section LICENSE
 * This source file is subject to the GNU General Public License Version 3 (GPLv3).
 *
 * @category Recolize
 * @package Recolize\RecommendationEngine
 * @author Recolize GmbH <service@recolize.com>
 * @copyright since 2015 Recolize GmbH (http://www.recolize.com)
 * @license http://opensource.org/licenses/GPL-3.0 GNU General Public License Version 3 (GPLv3).
 */

namespace Recolize\RecommendationEngine\Model\Feed;

class Product
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $entityCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private $entityCollection;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute[]
     */
    private $attributeCollection;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    private $productStatus;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    private $productVisibility;

    /**
     * @var \Magento\CatalogInventory\Helper\Stock
     */
    private $stockHelper;

    /**
     * @var \Recolize\RecommendationEngine\Model\Feed\AttributeFactory
     */
    private $attributeFactory;

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param \Magento\CatalogInventory\Helper\Stock $stockHelper
     * @param \Recolize\RecommendationEngine\Model\Feed\AttributeFactory $attributeFactory
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Catalog\Model\Product $product,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\CatalogInventory\Helper\Stock $stockHelper,
        AttributeFactory $attributeFactory
    ) {
        $this->entityCollectionFactory = $collectionFactory;
        $this->attributeCollection = $product->getAttributes();
        $this->productStatus = $productStatus;
        $this->productVisibility = $productVisibility;
        $this->stockHelper = $stockHelper;
        $this->attributeFactory = $attributeFactory;
    }

    /**
     * Return the product collection after applying all filters.
     *
     * @param integer $storeId
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProductCollection($storeId)
    {
        // Note: we cannot use Magento 2 repositories as ProductRepository does not support filtering by store.
        $this->getEntityCollection(true);
        $this->applyCollectionFilters($storeId);

        $this->entityCollection->addAttributeToSelect($this->getAttributeNames());

        return $this->entityCollection;
    }

    /**
     * Paginate through the current collection.
     *
     * @param integer $pageNum
     * @param integer $pageSize
     *
     * @return $this
     */
    public function paginateCollection($pageNum, $pageSize)
    {
        $this->getEntityCollection()->setPage($pageNum, $pageSize);

        return $this;
    }

    /**
     * Return all available attributes for the product.
     *
     * @return array
     */
    public function getAttributes()
    {
        $additionalAttributes = array(
            'stock_qty' => $this->attributeFactory->create(array('data' => array('attribute_code' => 'stock_qty'))),
            'is_in_stock' => $this->attributeFactory->create(array('data' => array('attribute_code' => 'is_in_stock'))),
            'url' => $this->attributeFactory->create(array('data' => array('attribute_code' => 'url')))
        );

        return array_merge($this->attributeCollection, $additionalAttributes);
    }

    /**
     * Return all available attribute names for the product.
     *
     * @return array
     */
    public function getAttributeNames()
    {
        return array_keys($this->getAttributes());
    }

    /**
     * Return the plain product collection without any filters.
     *
     * @param boolean $resetCollection whether to reset the collection if it is already loaded or not
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private function getEntityCollection($resetCollection = false)
    {
        if ($resetCollection === true || empty($this->entityCollection) === true) {
            $this->entityCollection = $this->entityCollectionFactory->create();
        }
        return $this->entityCollection;
    }

    /**
     * Apply all required filters like visibility, status, etc. to the collection.
     *
     * @param integer $storeId
     *
     * @return $this
     */
    private function applyCollectionFilters($storeId)
    {
        $this->entityCollection
            ->addStoreFilter($storeId)
            ->addUrlRewrite()
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addWebsiteNamesToResult()
            ->addAttributeToFilter('status', ['in' => $this->productStatus->getVisibleStatusIds()])
            ->setVisibility($this->productVisibility->getVisibleInSiteIds());

        $this->stockHelper->addIsInStockFilterToCollection($this->entityCollection);

        return $this;
    }
}