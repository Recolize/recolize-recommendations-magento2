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

class AttributeSetId extends Standard implements ColumnInterface
{
    /**
     * @var \Magento\Eav\Api\AttributeSetRepositoryInterface
     */
    private $attributeSet;

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\DataObject $attribute
     * @param \Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSet
     */
    public function __construct(
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\DataObject $attribute,
        \Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSet,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->attributeSet = $attributeSet;

        parent::__construct($product, $attribute, $logger);
    }

    /**
     * @return string
     */
    public function getValue()
    {
        $attributeSetRepository = $this->attributeSet->get($this->getProduct()->getAttributeSetId());
        return $attributeSetRepository->getAttributeSetName();
    }
}