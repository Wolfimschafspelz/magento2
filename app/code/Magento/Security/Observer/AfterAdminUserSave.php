<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Save UserExpiration on admin user record.
 */
class AfterAdminUserSave implements ObserverInterface
{
    /**
     * @var \Magento\Security\Model\UserExpirationFactory
     */
    private $userExpirationFactory;

    /**
     * @var \Magento\Security\Model\ResourceModel\UserExpiration
     */
    private $userExpirationResource;

    /**
     * AfterAdminUserSave constructor.
     *
     * @param \Magento\Security\Model\UserExpirationFactory $userExpirationFactory
     * @param \Magento\Security\Model\ResourceModel\UserExpiration $userExpirationResource
     */
    public function __construct(
        \Magento\Security\Model\UserExpirationFactory $userExpirationFactory,
        \Magento\Security\Model\ResourceModel\UserExpiration $userExpirationResource
    ) {

        $this->userExpirationFactory = $userExpirationFactory;
        $this->userExpirationResource = $userExpirationResource;
    }

    /**
     * Save user expiration.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /* @var $user \Magento\User\Model\User */
        $user = $observer->getEvent()->getObject();
        if ($user->getId()) {
            $expiresAt = $user->getExpiresAt();
            /** @var \Magento\Security\Model\UserExpiration $userExpiration */
            $userExpiration = $this->userExpirationFactory->create();
            $this->userExpirationResource->load($userExpiration, $user->getId());

            if (empty($expiresAt)) {
                // delete it if the admin user clears the field
                if ($userExpiration->getId()) {
                    $this->userExpirationResource->delete($userExpiration);
                }
            } else {
                if (!$userExpiration->getId()) {
                    $userExpiration->setId($user->getId());
                }
                $userExpiration->setExpiresAt($expiresAt);
                $this->userExpirationResource->save($userExpiration);
            }
        }
    }
}
