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

namespace Recolize\RecommendationEngine\Observer;

use Magento\Framework\Event\ObserverInterface;

class UpdateUserDataInCookie implements ObserverInterface
{
    /**
     * @var \Recolize\RecommendationEngine\Model\User
     */
    private $user;

    /**
     * @var \Recolize\RecommendationEngine\Model\Cookie
     */
    private $cookie;

    /**
     * @var \Recolize\RecommendationEngine\Model\Cookie\UserData
     */
    private $cookieUserData;

    /**
     * @param \Recolize\RecommendationEngine\Model\User $user
     * @param \Recolize\RecommendationEngine\Model\Cookie $cookie
     */
    public function __construct(
        \Recolize\RecommendationEngine\Model\User $user,
        \Recolize\RecommendationEngine\Model\Cookie $cookie,
        \Recolize\RecommendationEngine\Model\Cookie\UserData $cookieUserData
    ) {
        $this->user = $user;
        $this->cookie = $cookie;
        $this->cookieUserData = $cookieUserData;
    }

    /**
     * Updates the user data in the Recolize cookie.
     *
     * Events: customer_login, sales_order_place_after, checkout_cart_product_add_after, wishlist_add_product
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->cookieUserData
            ->setUserId($this->user->getCustomerId())
            ->setStatus($this->user->getCustomerStatus())
            ->setGroup($this->user->getCustomerGroup());

        $product = $observer->getEvent()->getProduct();
        if (empty($product) === false) {
            if ($observer->getEvent()->getName() === 'checkout_cart_product_add_after') {
                $this->cookieUserData
                    ->setItemAction('add_to_cart')
                    ->setClickedItemId($product->getId());
            } elseif ($observer->getEvent()->getName() === 'wishlist_add_product') {
                $this->cookieUserData
                    ->setItemAction('add_to_wishlist')
                    ->setClickedItemId($product->getId());
            }
        }

        $this->cookie->updateUserData($this->cookieUserData);

        return $this;
    }
}
