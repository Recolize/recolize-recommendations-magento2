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

use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Recolize\RecommendationEngine\Model\Cookie\UserData;

class Cookie
{
    /**
     * The cookie name.
     *
     * @var string
     */
    const COOKIE_NAME = 'recolize_parameter';

    /**
     * The cookie lifetime (browser session based).
     *
     * @var integer
     */
    const COOKIE_LIFETIME = PhpCookieManager::EXPIRE_AT_END_OF_SESSION_TIME;

    /**
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     */
    public function __construct(
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
    }

    /**
     * Updates the user data in the Recolize cookie.
     *
     * Events: customer_login, sales_order_place_after
     *
     * @param \Recolize\RecommendationEngine\Model\Cookie\UserData $userData the user data object
     * @return $this chaining
     */
    public function updateUserData(UserData $userData)
    {
        return $this->save($userData->toArray());
    }

    /**
     * Saves the Recolize cookie.
     *
     * @param array $additionalData the cookie data
     * @return $this chaining
     */
    private function save(array $additionalData)
    {
        try {
            $cookieValue = \Zend_Json::decode($this->get());
        } catch (\Exception $exception) {
        }

        try {
            if (empty($cookieValue) === true) {
                $cookieValue = array();
            }

            $cookieValue = \Zend_Json::encode(array_replace($cookieValue, $additionalData));
            $this->set($cookieValue);
        } catch (\Exception $exception) {}

        return $this;
    }

    /**
     * Return the current cookie value.
     *
     * @return null|string
     */
    private function get()
    {
        return $this->cookieManager->getCookie(self::COOKIE_NAME);
    }

    /**
     * Set the current cookie value.
     *
     * @param string $value
     * @param integer $duration
     *
     * @return $this
     */
    private function set($value, $duration = self::COOKIE_LIFETIME)
    {
        $cookieMetadata = $this->cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setHttpOnly(false)
            ->setPath('/');

        if ($duration !== PhpCookieManager::EXPIRE_AT_END_OF_SESSION_TIME) {
            $cookieMetadata->setDuration($duration);
        }

        $this->cookieManager->setPublicCookie(
            self::COOKIE_NAME,
            $value,
            $cookieMetadata
        );

        return $this;
    }
}