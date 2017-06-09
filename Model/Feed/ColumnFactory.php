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

class ColumnFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Model\ResourceModel\Attribute $attribute
     *
     * @return \Recolize\RecommendationEngine\Model\Feed\ColumnInterface
     */
    public function create($product, $attribute)
    {
        $className = $this->getClassName($attribute->getAttributeCode());
        if (class_exists($className) === false) {
            $className = $this->getClassName('standard');
        }

        return $this->objectManager->create($className, array('product' => $product, 'attribute' => $attribute));
    }

    /**
     * Return class name for the feed column model.
     *
     * @param string $attributeCode
     *
     * @return string
     */
    private function getClassName($attributeCode)
    {
        $namespace = '\\Recolize\\RecommendationEngine\\Model\\Feed\Column\\';
        $attributeClassName = $namespace . str_replace('_', '', ucwords(strtolower($attributeCode), "_"));

        return $attributeClassName;
    }
}