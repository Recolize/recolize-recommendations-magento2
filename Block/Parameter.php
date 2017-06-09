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

class Parameter extends Template
{
    /**
     * @var \Recolize\RecommendationEngine\Model\User
     */
    private $user;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Recolize\RecommendationEngine\Model\User $user
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Recolize\RecommendationEngine\Model\User $user,
        array $data = []
    ) {
        $this->user = $user;

        parent::__construct($context, $data);
    }

    /**
     * Returns the Recolize cookie name.
     *
     * @return string the cookie name
     */
    public function getCookieName()
    {
        return \Recolize\RecommendationEngine\Model\Cookie::COOKIE_NAME;
    }

    /**
     * Returns the default user status.
     *
     * @return string the user status
     */
    public function getDefaultUserStatus()
    {
        return $this->user->getDefaultCustomerStatus();
    }

    /**
     * Returns the default user group for logged out users.
     *
     * @return string the user group
     */
    public function getDefaultUserGroup()
    {
        return $this->user->getDefaultCustomerGroup();
    }
}