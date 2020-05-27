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

namespace Recolize\RecommendationEngine\Model;

class Feed
{
    /**
     * @var string
     */
    private $feedFilenameTemplate = 'product-export-%s.csv';

    /**
     * @var \Recolize\RecommendationEngine\Model\Feed\WriterFactory
     */
    private $writerFactory;

    /**
     * @var \Recolize\RecommendationEngine\Model\Feed\Writer
     */
    private $writer;

    /**
     * @var \Magento\Store\Model\Store
     */
    private $store;

    /**
     * @var integer
     */
    private $itemsPerPage;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    private $appEmulation;

    /**
     * @var \Recolize\RecommendationEngine\Model\Feed\Product
     */
    private $productData;

    /**
     * @var \Recolize\RecommendationEngine\Model\Feed\ProductToColumnMapper
     */
    private $productMapper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Recolize\RecommendationEngine\Helper\Data
     */
    private $dataHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var string[]
     */
    private $generatedFilenames = array();

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param \Recolize\RecommendationEngine\Model\Feed\Product $productData
     * @param \Recolize\RecommendationEngine\Model\Feed\ProductToColumnMapper $productMapper
     * @param \Recolize\RecommendationEngine\Model\Feed\WriterFactory $writerFactory
     * @param \Magento\Store\Model\App\Emulation $appEmulation
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Store\Model\Store $store
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Recolize\RecommendationEngine\Helper\Data $dataHelper
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        Feed\Product $productData,
        Feed\ProductToColumnMapper $productMapper,
        Feed\WriterFactory $writerFactory,
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\Store $store,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Recolize\RecommendationEngine\Helper\Data $dataHelper,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->productData = $productData;
        $this->productMapper = $productMapper;
        $this->writerFactory = $writerFactory;
        $this->appEmulation = $appEmulation;
        $this->storeManager = $storeManager;
        $this->store = $store;
        $this->scopeConfig = $scopeConfig;
        $this->dataHelper = $dataHelper;
        $this->logger = $logger;
    }

    /**
     * Generate the feed for all stores or for a specific one.
     *
     * @param integer|null $storeId
     *
     * @return array returns array of generated filenames
     */
    public function generate($storeId = null)
    {
        if (empty($storeId) === true) {
            $stores = $this->storeManager->getStores();
        } else {
            $store = $this->store->load($storeId);
            $stores = array($store);
        }

        foreach ($stores as $store) {
            try {
                $this->generateForStore($store);
            } catch (\Exception $exception) {
                $this->logger->critical($exception->getMessage());
            }
        }

        return $this->generatedFilenames;
    }

    /**
     * Generate the feed for a single store.
     *
     * @param \Magento\Store\Api\Data\StoreInterface $store
     *
     * @return boolean returns true if feed has been generated successfully, false otherwise
     */
    private function generateForStore(\Magento\Store\Api\Data\StoreInterface $store)
    {
        if ($this->shouldGenerate($store) === false) {
            return false;
        }

        $this->initExport($store);

        $page = 0;
        while (true) {
            ++$page;

            $entityCollection = $this->productData->getProductCollection($store->getId());
            $this->productData->paginateCollection($page, $this->getItemsPerPage());

            if ($entityCollection->count() === 0) {
                break;
            }

            $entityCollection->addCategoryIds();

            if ($page === 1) {
                $this->writer->setHeaderCols($this->productData->getAttributeNames());
            }

            foreach ($entityCollection as $productId => $product) {
                // An additional check for the availability is added as workaround for the Magento core bug
                // https://github.com/magento/magento2/issues/8566 which causes the productCollection->addIsInStockFilterToCollection()
                // method to not work correctly and also return out of stock products.
                if ($product->isAvailable() === false) {
                    continue;
                }

                $this->writer->writeRow($this->productMapper->getProductDataArray($product, $this->productData->getAttributes()));
            }

            // Break if we reached last page or if it seems like an endless loop.
            if ($entityCollection->getCurPage() >= $entityCollection->getLastPageNumber() || $page >= 100000) {
                break;
            }

            $entityCollection->clear();
        }

        $this->finishExport();
        $this->generatedFilenames[] = $this->getFeedFilename($store);

        return true;
    }

    /**
     * Return the product export feed name.
     *
     * @param \Magento\Store\Api\Data\StoreInterface $store
     *
     * @return string
     */
    public function getFeedFilename(\Magento\Store\Api\Data\StoreInterface $store)
    {
        // Note: we use the hash() function here to bypass the Magento code sniffer check
        // as we do not use the md5 hash for passwords but for file name generation, this is not critical and cannot be changed
        return sprintf(
            $this->feedFilenameTemplate,
            hash('md5', $store->getId() . '#' . $store->getName() . '#' . $store->getCode()) . '-' . $store->getCode()
        );
    }

    /**
     * Initialize the generation process, i.e. start the store emulation and open the csv writer.
     *
     * @param \Magento\Store\Api\Data\StoreInterface $store
     *
     * @return $this
     */
    private function initExport(\Magento\Store\Api\Data\StoreInterface $store)
    {
        $this->appEmulation->startEnvironmentEmulation($store->getId(), \Magento\Framework\App\Area::AREA_FRONTEND, true);
        $this->writer = $this->writerFactory->create(array('filename' => $this->getFeedFilename($store)));

        return $this;
    }

    /**
     * Finish the feed generation, i.e. stop the store emulation and close the csv writer.
     *
     * @return $this
     */
    private function finishExport()
    {
        $this->writer->close();
        $this->appEmulation->stopEnvironmentEmulation();

        return $this;
    }

    /**
     * Determine maximum number of items per page.
     *
     * @see \Magento\CatalogImportExport\Model\Export\Product::getItemsPerPage()
     *
     * @return integer
     */
    private function getItemsPerPage()
    {
        if ($this->itemsPerPage === null) {
            $memoryLimit = trim(ini_get('memory_limit'));
            $lastMemoryLimitLetter = strtolower($memoryLimit[strlen($memoryLimit) - 1]);
            switch ($lastMemoryLimitLetter) {
                case 'g':
                    $memoryLimit *= 1024;
                    // fall-through intentional
                case 'm':
                    $memoryLimit *= 1024;
                    // fall-through intentional
                case 'k':
                    $memoryLimit *= 1024;
                    break;
                default:
                    // minimum memory required by Magento
                    $memoryLimit = 250000000;
            }

            // Tested one product to have up to such size
            $memoryPerProduct = 100000;
            // Decrease memory limit to have supply
            $memoryUsagePercent = 0.8;
            // Minimum Products limit
            $minProductsLimit = 500;
            // Maximal Products limit
            $maxProductsLimit = 5000;

            $this->itemsPerPage = intval(
                ($memoryLimit * $memoryUsagePercent - memory_get_usage(true)) / $memoryPerProduct
            );
            if ($this->itemsPerPage < $minProductsLimit) {
                $this->itemsPerPage = $minProductsLimit;
            }
            if ($this->itemsPerPage > $maxProductsLimit) {
                $this->itemsPerPage = $maxProductsLimit;
            }
        }
        return $this->itemsPerPage;
    }

    /**
     * Determines whether the feed should be generated or not depending on configuration settings.
     *
     * @param \Magento\Store\Api\Data\StoreInterface $store
     *
     * @return boolean
     */
    private function shouldGenerate(\Magento\Store\Api\Data\StoreInterface $store)
    {
        return $this->dataHelper->isExtensionEnabled()
            && $this->scopeConfig->isSetFlag('recolize_recommendation_engine/product_feed/enable_export', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
    }
}