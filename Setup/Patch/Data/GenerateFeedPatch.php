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

namespace Recolize\RecommendationEngine\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class GenerateFeedPatch implements DataPatchInterface
{
    /** @var ModuleDataSetupInterface */
    private $moduleDataSetup;

    /** @var \Recolize\RecommendationEngine\Model\Feed */
    private $feed;

    /** @var \Magento\Framework\App\State */
    private $appState;

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Recolize\RecommendationEngine\Model\Feed $feed,
        \Magento\Framework\App\State $appState,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->feed = $feed;
        $this->appState = $appState;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        try {
            // We use emulateAreaCode() method here to ensure clean reset after finish and to not interact with other
            // setup scripts as setAreaCode() can only be called once.
            $this->appState->emulateAreaCode(
                \Magento\Framework\App\Area::AREA_FRONTEND,
                [$this, 'generateFeed']
            );
        } catch (\Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public function generateFeed()
    {
        return $this->feed->generate();
    }
}
