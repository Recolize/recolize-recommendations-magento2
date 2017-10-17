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

namespace Recolize\RecommendationEngine\Model\Feed\Column;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Recolize\RecommendationEngine\Model\Feed\ColumnInterface;

class StockQty extends Standard implements ColumnInterface
{
    /**
     * @var \Magento\CatalogInventory\Model\Stock\StockItemRepository
     */
    private $stockItemRepository;

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\DataObject $attribute
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository
     */
    public function __construct(
        \Magento\Catalog\Model\Product $product,
        $attribute,
        \Psr\Log\LoggerInterface $logger,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository
    ) {
        $this->stockItemRepository = $stockItemRepository;

        parent::__construct($product, $attribute, $logger);
    }

    /**
     * @return string
     */
    public function getValue()
    {
        if ($this->getProduct()->getTypeId() === Configurable::TYPE_CODE) {
            $stockQuantity = 0;

            $simpleProductCollection = $this->getProduct()->getTypeInstance()->getSalableUsedProducts($this->getProduct());

            foreach ($simpleProductCollection as $simpleProduct) {
                $stockItemSimpleProduct = $this->getStockItem($simpleProduct->getId());
                if (empty($stockItemSimpleProduct) === true) {
                    continue;
                }

                $stockQuantity += $stockItemSimpleProduct->getQty();
            }

            return $stockQuantity;
        }

        $stockItem = $this->getStockItem($this->getProduct()->getId());

        if (empty($stockItem) === true) {
            return 0;
        }

        return $stockItem->getQty();
    }

    /**
     * Return the stock item model.
     *
     * @param integer $productId
     * 
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface|null
     */
    
    private function getStockItem($productId)
    {
        try {
            return $this->stockItemRepository->get($productId);
        } catch (\Exception $exception) {
            $this->logger->warning($exception->getMessage(), array('product_id' => $productId));
        }

        return null;
    }
}