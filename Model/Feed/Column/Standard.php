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

class Standard implements ColumnInterface
{
    /**
     * @var \Magento\Framework\DataObject
     */
    protected $attribute;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\DataObject $attribute
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\DataObject $attribute,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->product = $product;
        $this->attribute = $attribute;
        $this->logger = $logger;
    }

    /**
     * @return object
     */
    public function getValue()
    {
        try {
            switch ($this->getAttribute()->getFrontendInput()) {
                case 'select':
                    $attributeValue = (string) $this->getProduct()->getAttributeText($this->getAttribute()->getAttributeCode());
                    break;
                case 'multiselect':
                    $attributeValue = $this->getProduct()->getResource()->getAttribute($this->getAttribute()->getAttributeCode())->getFrontend()->getValue($this->getProduct());
                    break;
                default:
                    $attributeValue = $this->getProduct()->getData($this->getAttribute()->getAttributeCode());
            }
        } catch (\Exception $exception) {
            $attributeValue = $this->getProduct()->getData($this->getAttribute()->getAttributeCode());
        }

        return $attributeValue;
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    protected function getProduct()
    {
        return $this->product;
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    protected function getAttribute()
    {
        return $this->attribute;
    }
}