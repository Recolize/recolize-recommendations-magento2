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

class User
{
    /**
     * Customer status for a new customer.
     *
     * @var string
     */
    const STATUS_NEW_CUSTOMER = 'new_customer';

    /**
     * Customer status for a returning customer.
     *
     * @var string
     */
    const STATUS_RETURNING_CUSTOMER = 'returning_customer';

    /**
     * @var \Magento\Customer\Model\Session\Proxy
     */
    private $customerSession;

    /**
     * @var \Magento\Checkout\Model\Session\Proxy
     */
    private $checkoutSession;

    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param \Magento\Customer\Model\Session\Proxy $customerSession
     * @param \Magento\Checkout\Model\Session\Proxy $checkoutSession
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        \Magento\Customer\Model\Session\Proxy $customerSession,
        \Magento\Checkout\Model\Session\Proxy $checkoutSession,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->groupRepository = $groupRepository;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Check if customer is logged in or not.
     *
     * @return boolean
     */
    public function isCustomerLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * Get an encrypted logged in customer id.
     *
     * @return integer|null the encrypted customer id; null, if the customer is not logged in
     */
    public function getCustomerId()
    {
        $internalCustomerId = $this->getInternalCustomerId();

        if (empty($internalCustomerId) === true) {
            return $internalCustomerId;
        }

        return sha1($internalCustomerId);
    }

    /**
     * Returns the default customer group.
     *
     * @return string the default customer group
     */
    public function getDefaultCustomerGroup()
    {
        $customerGroupCode = $this->groupRepository->getById(\Magento\Customer\Model\Group::NOT_LOGGED_IN_ID)->getCode();

        return $this->replaceSpecialCharacters($customerGroupCode);
    }

    /**
     * Returns current customer group.
     *
     * @return string
     */
    public function getCustomerGroup()
    {
        $customerGroupId = $this->customerSession->getCustomerGroupId();
        $customerGroupCode = $this->groupRepository->getById($customerGroupId)->getCode();

        return $this->replaceSpecialCharacters($customerGroupCode);
    }

    /**
     * Returns current customer status that is either taken from saved value in customer session or calculated via
     * last order.
     *
     * @return string
     */
    public function getCustomerStatus()
    {
        $customerStatus = $this->getDefaultCustomerStatus();

        if ($this->hasOrders() === true) {
            $customerStatus = self::STATUS_RETURNING_CUSTOMER;
        }

        return $customerStatus;
    }

    /**
     * Returns the default customer status.
     *
     * @return string the default customer status
     */
    public function getDefaultCustomerStatus()
    {
        return self::STATUS_NEW_CUSTOMER;
    }

    /**
     * Returns the Magento internal customer id if the customer is logged in.
     *
     * @return integer|null the internal Magento customer id; null if not available/not logged in
     */
    private function getInternalCustomerId()
    {
        return $this->customerSession->getId();
    }

    /**
     * Replaces special characters in a given string.
     *
     * @param string $text the text with possible special characters
     * @return string a cleaned text
     */
    private function replaceSpecialCharacters($text)
    {
        return str_replace('\'', '', $text);
    }

    /**
     * Checks whether the current customer has previous orders or not.
     *
     * @return boolean
     */
    private function hasOrders()
    {
        $orderCount = 0;

        if ($this->isCustomerLoggedIn() === true) {
            $criteria = $this->searchCriteriaBuilder
                ->addFilter('customer_id', $this->getInternalCustomerId())
                ->setCurrentPage(1)
                ->setPageSize(1)
                ->create();
            $orderCount = $this->orderRepository->getList($criteria)->getTotalCount();
        } else {
            $lastOrder = $this->checkoutSession->getLastRealOrder();
            if ($lastOrder->isEmpty() === false) {
                $orderCount = 1;
            }
        }

        return ($orderCount > 0);
    }
}