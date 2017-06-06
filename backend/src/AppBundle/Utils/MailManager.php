<?php

namespace AppBundle\Utils;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Templating\EngineInterface;
use UserBundle\Entity\UserEntity;

/**
 * Отправка писем
 *
 * @package AppBundle\Utils
 */
class MailManager
{
    /**
     * @var \Swift_Mailer Системный мейлер
     */
    protected $mailer;

    /**
     * @var EngineInterface Шаблонизатор
     */
    protected $templating;

    /**
     * @var string Адрес почтового ящика с которого отправлять письма
     */
    protected $defaultFrom;

    /**
     * @var \Swift_Message Последнее отправленное сообщение
     */
    protected $lastMessage;

    /**
     * MailManager constructor.
     *
     * @param \Swift_Mailer $mailer Системный мейлер
     * @param EngineInterface $templating Шаблонизатор писем
     * @param null|string $defaultFrom Отправитель по умолчанию
     */
    public function __construct(\Swift_Mailer $mailer, EngineInterface $templating, ?string $defaultFrom)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->defaultFrom = $defaultFrom;
    }

    /**
     * Сформировать письмо
     *
     * @param UserEntity $receiver Модель получателя для формирования красивых писем с приветствием
     * @param string $subject Заголовок
     * @param string $htmlTemplate Путь к HTML-шаблону
     * @param array $templateVars Переменные для шаблона
     *
     * @return \Swift_Message
     */
    public function buildMessageToUser(UserEntity $receiver = null, string $subject, string $htmlTemplate, array $templateVars = [])
    {
        $message = \Swift_Message::newInstance()
            ->setSubject($subject);

        $templateVars['receiverUser'] = $receiver;
        $message->setTo([
            $receiver->getEmail() => $receiver->getName()
        ]);

        $htmlBody = $this->templating->render($htmlTemplate, $templateVars);

        $message->setBody($htmlBody, 'text/html');

        return $message;
    }

    /**
     * Отправить сообщение
     *
     * @param \Swift_Message $message Сформированное сообщение
     * @param string $from Отправитель (по умолчанию берется отправитель из настроек сервиса)
     * @param string $to Получатель (по умолчанию берется получатель из сообщения, если установлено)
     *
     * @return int
     */
    public function sendMessage(\Swift_Message $message, string $from = '', string $to = '')
    {
        $to = $to ?: $message->getTo();
        if (empty($to)) {
            throw new InvalidConfigurationException('Message has no receivers addresses');
        }

        $from = $from ?: $this->defaultFrom;
        if (empty($from)) {
            throw new InvalidConfigurationException('Message has no sender address');
        }

        $message
            ->setTo($to)
            ->setFrom($from);

        $this->lastMessage = $message;

        return $this->mailer->send($message);
    }

    /**
     * Получить последнее отправленное сообщение
     *
     * @return \Swift_Message
     */
    public function getLastMessage(): ?\Swift_Message
    {
        return $this->lastMessage;
    }

    /**
     * Очистка последнего сообщения
     */
    public function clearLastMessage()
    {
        $this->lastMessage = null;
    }
}
