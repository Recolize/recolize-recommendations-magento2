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

namespace Recolize\RecommendationEngine\Model\Feed;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\ImportExport\Model\Export\Adapter\AbstractAdapter;
use Magento\ImportExport\Model\Export\Adapter\Csv;

class Writer extends Csv
{
    /**
     * @var string
     */
    const TMP_FILENAME_EXTENSION = '.tmp';

    /**
     * Field delimiter.
     *
     * @var string
     */
    protected $_delimiter = ';';

    /**
     * @var string
     */
    private $filename;

    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param string $filename
     * @param null $destination
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        $filename,
        $destination = null
    ) {
        $this->filename = $filename;

        // We use a temporary filename for file generation and do the rename in $this::destruct().
        AbstractAdapter::__construct($filesystem, $this->filename . self::TMP_FILENAME_EXTENSION, DirectoryList::MEDIA);
    }

    /**
     * This method only exists for compatibility with Magento 2.1 and 2.2.
     */
    public function __destruct()
    {
        $this->destruct();
    }

    /**
     * Run all actions that should be executed when the writer closes.
     *
     * Actually this is renaming the temporary filename to the real one.
     *
     * @return void
     */
    public function destruct()
    {
        $this->_directoryHandle->renameFile($this->filename . self::TMP_FILENAME_EXTENSION, $this->filename);
        parent::destruct();
    }
}