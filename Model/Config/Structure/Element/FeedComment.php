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

namespace Recolize\RecommendationEngine\Model\Config\Structure\Element;

use Magento\Config\Model\Config\CommentInterface;

class FeedComment implements CommentInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Recolize\RecommendationEngine\Model\Feed
     */
    private $feed;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Recolize\RecommendationEngine\Model\Feed $feed
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Recolize\RecommendationEngine\Model\Feed $feed
    ) {
        $this->storeManager = $storeManager;
        $this->feed = $feed;
    }

    /**
     * Return the dynamic comment for the Recolize product feed export.
     *
     * @param string $elementValue
     *
     * @return string
     */
    public function getCommentText($elementValue)
    {
        $commentString = __('If set to \'Yes\' the Recolize Product Feed will be generated at the configured cron schedule. Please copy the path depending on your StoreView into your domain settings in the <a href="https://tool.recolize.com/domains?utm_source=magento2-extension-admin-area&utm_medium=web&utm_campaign=Magento 2 Extension Admin" target="_blank">Recolize Tool</a>:') . '<br />';
        foreach ($this->storeManager->getStores() as $store) {
            $commentString .= sprintf(
                '<b>%s</b>: <nobr>%s</nobr><br />',
                $store->getName(),
                $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $this->feed->getFeedFileName($store)
            );
        }
        $commentString .= '<br />' . __('You can set this setting to \'No\' if you already have other product feeds like Google Shopping, CSV-based product exports, etc. Then you have to enter these feed urls into the <a href="https://tool.recolize.com/domains?utm_source=magento2-extension-admin-area&utm_medium=web&utm_campaign=Magento 2 Extension Admin" target="_blank">Recolize Tool</a>.');

        return $commentString;
    }
}