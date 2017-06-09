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

namespace Recolize\RecommendationEngine\Model\Cookie;

class UserData
{
    /**
     * @var integer
     */
    private $userId;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $group;

    /**
     * @var integer
     */
    private $clickedItemId;

    /**
     * @var string
     */
    private $itemAction;

    /**
     * Return array representation of cookie user data.
     *
     * @return array
     */
    public function toArray()
    {
        $userDataArray = array();

        if (empty($this->userId) === false) {
            $userDataArray = array(
                'User' => array(
                    'id' => $this->userId
                )
            );
        }

        $userDataArray = array_merge_recursive(
            $userDataArray,
            array(
                'User' => array(
                    'status' => $this->status,
                    'group' => $this->group
                )
            )
        );

        if (empty($this->itemAction) === false && empty($this->clickedItemId) === false) {
            $userDataArray['itemAction'] = $this->itemAction;
            $userDataArray['clickedItemId'] = $this->clickedItemId;
        }

        return $userDataArray;
    }

    /**
     * @param integer $userId
     *
     * @return $this
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @param string $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @param string $group
     *
     * @return $this
     */
    public function setGroup($group)
    {
        $this->group = $group;
        return $this;
    }

    /**
     * @param integer $clickedItemId
     *
     * @return $this
     */
    public function setClickedItemId($clickedItemId)
    {
        $this->clickedItemId = $clickedItemId;
        return $this;
    }

    /**
     * @param string $itemAction
     *
     * @return $this
     */
    public function setItemAction($itemAction)
    {
        $this->itemAction = $itemAction;
        return $this;
    }
}