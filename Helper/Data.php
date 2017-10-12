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

namespace Recolize\RecommendationEngine\Helper;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\GroupedProduct\Pricing\Price\FinalPrice;
use Magento\Theme\Model\Indexer\Design\Config;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Returns whether the Recolize Recommendation extension is enabled in configuration or not.
     *
     * @return boolean
     */
    public function isExtensionEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            'recolize_recommendation_engine/general/enable_extension',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Returns the minimum price for a configurable product by taking the lowest price
     * of a used and available simple product.
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $priceAttributeName an attribute name like "price" or "final_price"
     *
     * @return boolean|integer false, if the minimum price could not be calculated
     */
    public function getMinimumPriceForConfigurableProduct(Product $product, $priceAttributeName)
    {
        if ($product->getTypeId() !== Configurable::TYPE_CODE) {
            return false;
        }

        $minimumPrice = false;
        $simpleProductCollection = $product->getTypeInstance()->getUsedProducts($product);

        foreach ($simpleProductCollection as $simpleProduct) {
            if ($simpleProduct->isAvailable() === false) {
                continue;
            }

            $simpleProductPrice = $simpleProduct->getData($priceAttributeName);

            if ($priceAttributeName === FinalPrice::PRICE_CODE) {
                $simpleProductPrice = $simpleProduct->getPriceInfo()->getPrice(FinalPrice::PRICE_CODE)->getValue();
            }

            if ($minimumPrice === false || $simpleProductPrice < $minimumPrice) {
                $minimumPrice = $simpleProductPrice;
            }
        }

        return $minimumPrice;
    }
}