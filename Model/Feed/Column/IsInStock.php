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

use Recolize\RecommendationEngine\Model\Feed\ColumnInterface;

class IsInStock extends Standard implements ColumnInterface
{
    /**
     * @var \Magento\CatalogInventory\Model\Stock\StockItemRepository
     */
    private $stockItemRepository;

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param $attribute
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
        if (empty($this->getStockItem()) === false && $this->getStockItem()->getIsInStock() === true) {
            return 1;
        }

        return 0;
    }

    /**
     * Return the stock item model.
     *
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface|null
     */
    private function getStockItem()
    {
        try {
            return $this->stockItemRepository->get($this->getProduct()->getId());
        } catch (\Exception $exception) {
            $this->logger->warning($exception->getMessage(), array('product_id' => $this->getProduct()->getId()));
        }

        return null;
    }
}