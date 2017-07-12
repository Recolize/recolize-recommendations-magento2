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

namespace Recolize\RecommendationEngine\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    /**
     * @var \Recolize\RecommendationEngine\Model\Feed
     */
    private $feed;

    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param \Recolize\RecommendationEngine\Model\Feed $feed
     * @param \Magento\Framework\App\State $appState
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Recolize\RecommendationEngine\Model\Feed $feed,
        \Magento\Framework\App\State $appState,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->feed = $feed;
        $this->appState = $appState;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        try {
            // We use emulateAreaCode() method here to ensure clean reset after finish and to not interact with other
            // setup scripts as setAreaCode() can only be called once.
            $this->appState->emulateAreaCode(
                \Magento\Framework\App\Area::AREA_FRONTEND,
                array($this, 'generateFeed')
            );
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }
    }

    /**
     * @return array
     */
    public function generateFeed()
    {
        return $this->feed->generate();
    }
}
