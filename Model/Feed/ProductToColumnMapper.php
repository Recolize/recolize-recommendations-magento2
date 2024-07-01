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

use Magento\Catalog\Api\Data\ProductInterface;

class ProductToColumnMapper
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Recolize\RecommendationEngine\Model\Feed\ColumnFactory
     */
    private $columnFactory;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Recolize\RecommendationEngine\Model\Feed\ColumnFactory $columnFactory
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        ColumnFactory $columnFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->columnFactory = $columnFactory;
    }

    /**
     * Return array with mapping of product data to feed columns.
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface&\Magento\Catalog\Model\Product $product
     * @param array $attributes
     *
     * @return array<string,string>
     */
    public function getProductDataArray(ProductInterface $product, array $attributes): array
    {
        $productData = [];
        $attributesToExclude = $this->getAttributesToExclude();

        foreach ($attributes as $attributeCode => $attribute) {
            if (in_array($attributeCode, $attributesToExclude)) {
                continue;
            }

            $column = $this->columnFactory->create($product, $attribute);
            $columnValue = $column->getValue();

            if (! $this->isValidColumnValue($columnValue)) {
                continue;
            }

            $productData[$attributeCode] = (string)$columnValue;
        }

        return $productData;
    }

    /** @return list<string> */
    private function getAttributesToExclude(): array
    {
        $attributesToExclude = $this->scopeConfig->getValue('recolize_recommendation_engine/product_feed/attributes_to_exclude');
        if (empty($attributesToExclude)) {
            return [];
        }

        return explode(',', $attributesToExclude);
    }

    /**
     * Check if given column value is valid.
     *
     * @param mixed $value
     */
    private function isValidColumnValue($value): bool
    {
        if (empty($value) === false && is_numeric($value) === false && is_string($value) === false) {
            return false;
        }

        return ! is_array($value);
    }
}