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

namespace Recolize\RecommendationEngine\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class ProductExportCommand extends Command
{
    /**
     * @var \Recolize\RecommendationEngine\Model\Feed
     */
    private $feed;

    /**
     * @param \Magento\Framework\App\State $state
     * @param \Recolize\RecommendationEngine\Model\Feed $feed
     */
    public function __construct(
        \Magento\Framework\App\State $state,
        \Recolize\RecommendationEngine\Model\Feed $feed
    ) {
        $this->feed = $feed;

        $state->setAreaCode('frontend');

        parent::__construct();
    }

    /**
     * Configures of the Recolize feed generation command.
     */
    protected function configure()
    {
        $this->setName('recolize:feed:generate')
            ->setDescription('Generates the Recolize product feed.')
            ->setDefinition(array(
                new InputArgument('store-id', InputArgument::OPTIONAL, 'Store ID to generate Recolize product feed for.')
            ));
    }

    /**
     * Triggers the feed generation.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return integer|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $storeId = $input->getArgument('store-id');

        try {
            $generatedFilenames = $this->feed->generate($storeId);
        } catch (\Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
            // we must have an exit code higher than zero to indicate something was wrong
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }

        if (empty($generatedFilenames) === true) {
            $output->writeln("<comment>Recolize product feeds were NOT generated. Please check settings in Stores > Configuration > Recolize Recommendation Engine.</comment>");
        } else {
            $output->writeln("<info>Recolize product feeds were generated successfully: " . join(', ', $generatedFilenames) . "</info>");
        }

        return null;
    }
}