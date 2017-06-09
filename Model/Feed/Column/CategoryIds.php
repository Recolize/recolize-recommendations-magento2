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

class CategoryIds extends Standard implements ColumnInterface
{
    /**
     * @return string
     */
    public function getValue()
    {
        $attributeValue = parent::getValue();
        if (is_array($attributeValue) === false) {
            return $attributeValue;
        }

        return join(',', $attributeValue);
    }
}