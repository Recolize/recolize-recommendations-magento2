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

class Price extends Standard implements ColumnInterface
{
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
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Tax\Helper\Data $taxData
    ) {
        $this->catalogHelper = $catalogData;
        $this->taxData = $taxData;

        parent::__construct($product, $attribute, $logger);
    }

    /**
     * Return the price value of the current product.
     *
     * We take the minimal price as for simple and virtual products this is identical to the final price, for
     * configurables this is the price of the cheapest simple.
     *
     * @return string
     */
    public function getValue()
    {
        $price = $this->getProduct()->getMinimalPrice();

        $specialPrice = $this->getProduct()->getData('special_price');
        if (empty($specialPrice) === false) {
            $price = $specialPrice;
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