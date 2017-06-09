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
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param array $attributes
     *
     * @return array
     */
    public function getProductDataArray(ProductInterface $product, array $attributes)
    {
        $productData = array();
        $attributesToExclude = $this->getAttributesToExclude();

        foreach ($attributes as $attributeCode => $attribute) {
            if (in_array($attributeCode, $attributesToExclude) === true) {
                continue;
            }

            $column = $this->columnFactory->create($product, $attribute);
            $columnValue = $column->getValue();

            if ($this->isValidColumnValue($columnValue) === false) {
                continue;
            }

            $productData[$attributeCode] = (string)$columnValue;
        }

        return $productData;
    }

    /**
     * Return all attributes to be excluded.
     *
     * @return array
     */
    private function getAttributesToExclude()
    {
        $attributesToExclude = $this->scopeConfig->getValue('recolize_recommendation_engine/product_feed/attributes_to_exclude');
        if (empty($attributesToExclude) === true) {
            return array();
        }

        return explode(',', $attributesToExclude);
    }

    /**
     * Check if given column value is valid.
     *
     * @param object $value
     *
     * @return boolean
     */
    private function isValidColumnValue($value)
    {
        if (empty($value) === false && is_numeric($value) === false && is_string($value) === false) {
            return false;
        }

        if (is_array($value) === true) {
            return false;
        }

        return true;
    }
}