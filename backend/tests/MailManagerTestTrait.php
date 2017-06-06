<?php

namespace Tests;

use AppBundle\Utils\MailManager;


/**
 * Проверка последних сообщений в системном мейлере
 *
 * @package Tests
 */
trait MailManagerTestTrait
{
    /**
     * Проверить, что последнее письмо содержит указанную строку
     *
     * @param string $string
     */
    public function assertLastMessageContains($string)
    {
        /** @var MailManager $mailManager */
        $mailManager = $this->getContainer()->get('mail_manager');

        $this->assertInstanceOf(MailManager::class, $mailManager);
        $lastMessage = $mailManager->getLastMessage();
        $this->assertInstanceOf(\Swift_Message::class, $lastMessage);
        $this->assertContains($string, $lastMessage->getBody());
    }
}
