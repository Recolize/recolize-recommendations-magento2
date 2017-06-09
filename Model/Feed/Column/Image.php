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

class Image extends Standard implements ColumnInterface
{
    /**
     * @var \Magento\Catalog\Helper\ImageFactory
     */
    private $imageFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\DataObject $attribute
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Catalog\Helper\ImageFactory $imageHelperFactory
     */
    public function __construct(
        \Magento\Catalog\Model\Product $product,
        $attribute,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Catalog\Helper\ImageFactory $imageHelperFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->imageFactory = $imageHelperFactory;
        $this->scopeConfig = $scopeConfig;

        parent::__construct($product, $attribute, $logger);
    }

    /**
     * @return string
     */
    public function getValue()
    {
        try {
            return $this->imageFactory->create()
                ->init($this->getProduct(), $this->getProductImageId())
                ->setImageFile(parent::getValue())
                ->getUrl();
        } catch (\Exception $exception) {
            $this->logger->warning($exception->getMessage());
        }

        return '';
    }

    /**
     * Return product image id to use for the feed.
     *
     * Must be one of product_small_image, product_base_image and product_thumbnail_image, etc.
     * (see magento/theme-frontend-luma/etc/view.xml)
     *
     * @return string
     */
    private function getProductImageId()
    {
        return $this->scopeConfig->getValue('recolize_recommendation_engine/product_feed/product_image_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getProduct()->getStore());
    }
}