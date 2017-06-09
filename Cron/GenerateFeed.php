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

namespace Recolize\RecommendationEngine\Cron;

class GenerateFeed
{
    /**
     * @var \Recolize\RecommendationEngine\Model\Feed
     */
    private $feed;

    /**
     * @param \Recolize\RecommendationEngine\Model\Feed $feed
     */
    public function __construct(
        \Recolize\RecommendationEngine\Model\Feed $feed
    ) {
        $this->feed = $feed;
    }

    /**
     * Starts the feed generation.
     *
     * @return $this|bool
     */
    public function execute()
    {
        try {
            $this->feed->generate();
        } catch (\Exception $exception) {
            // Exceptions are logged, do nothing anymore.
        }

        return $this;
    }
}