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

namespace Recolize\RecommendationEngine\Block;

use Magento\Framework\View\Element\Template;

class JavascriptSnippet extends Template
{
    /**
     * Return the JavaScript snippet code without the <script> tags.
     *
     * @return string
     */
    public function getJavascriptSnippetCode()
    {
        return str_replace(
            array('<script type="text/javascript">//<![CDATA[', '//]]></script>'),
            '',
            $this->_scopeConfig->getValue('recolize_recommendation_engine/general/javascript_snippet')
        );
    }
}
