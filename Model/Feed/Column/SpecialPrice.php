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

class SpecialPrice extends Standard implements ColumnInterface
{
    /**
     * @var \Recolize\RecommendationEngine\Helper\Data
     */
    private $dataHelper;

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    private $catalogHelper;

    /**
     * @var \Magento\Tax\Helper\Data
     */
    private $taxData;

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\DataObject $attribute
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Tax\Helper\Data $taxData
     */
    public function __construct(
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\DataObject $attribute,
        \Psr\Log\LoggerInterface $logger,
        \Recolize\RecommendationEngine\Helper\Data $dataHelper,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Tax\Helper\Data $taxData
    ) {
        $this->dataHelper = $dataHelper;
        $this->catalogHelper = $catalogData;
        $this->taxData = $taxData;

        parent::__construct($product, $attribute, $logger);
    }

    /**
     * Return the special price value of the current product.
     *
     * For configurable products the minimum special price has to be calculated by looking at
     * the final prices of all used simples to also consider the special price from and to dates.
     *
     * @return string
     */
    public function getValue()
    {
        if ($this->getProduct()->getTypeId() === Configurable::TYPE_CODE) {
            $price = $this->dataHelper->getMinimumPriceForConfigurableProduct($this->getProduct(), 'final_price');
        } else {
            // We use getMinimalPrice() instead of getFinalPrice(), because getMinimalPrice() also recognizes an
            // invalid special price time frame for the current timestamp. This works here as the minimal price is
            // loaded with the product collection for all kinds of products.
            $price = $this->getProduct()->getMinimalPrice();
        }
        
        $price = $this->catalogHelper->getTaxPrice($this->getProduct(), $price, $this->isExportPriceIncludingTax());

        return $price;
    }

    /**
     * Return whether the price should be exported including tax or not.
     *
     * @return boolean
     */
    private function isExportPriceIncludingTax()
    {
        return ($this->taxData->displayPriceExcludingTax() === false);
    }
}