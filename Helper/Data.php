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
}