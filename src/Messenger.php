<?php

namespace Tgallice\FBMessenger;

use Tgallice\FBMessenger\Exception\ApiException;
use Tgallice\FBMessenger\Model\Attachment;
use Tgallice\FBMessenger\Model\Attachment\Template;
use Tgallice\FBMessenger\Model\Button;
use Tgallice\FBMessenger\Model\Message;
use Tgallice\FBMessenger\Model\MessageResponse;
use Tgallice\FBMessenger\Model\ThreadSetting;
use Tgallice\FBMessenger\Model\ThreadSetting\GreetingText;
use Tgallice\FBMessenger\Model\ThreadSetting\StartedButton;
use Tgallice\FBMessenger\Model\UserProfile;
use Tgallice\FBMessenger\Model\ThreadSetting\DomainWhitelisting;
use Tgallice\FBMessenger\Model\WhitelistedDomains;

class Messenger {
    use ResponseHandler;

    const SETTINGS_URL_NAMESPACE = 'messenger_profile';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var array
     */
    private $extraSettings = array();

    /**
     * @param Client $client
     */
    public function __construct(Client $client) {
        $this->client = $client;
    }

    /**
     * @param string $recipient
     * @param string|Message|Attachment|Template $message
     * @param string $notificationType
     *
     * @return MessageResponse
     *
     * @throws ApiException
     */
    public function sendMessage($recipient, $message, $notificationType = NotificationType::REGULAR) {
        $message = $this->createMessage($message);
        $options = RequestOptionsFactory::createForMessage($recipient, $message, $notificationType);
        $response = $this->client->send('POST', '/me/messages', null, [], [], $options);
        $responseData = $this->decodeResponse($response);

        return new MessageResponse($responseData['recipient_id'], $responseData['message_id']);
    }

    /**
     * @param string $recipient
     * @param string $typingIndicator
     */
    public function setTypingStatus($recipient, $typingIndicator) {
        $options = RequestOptionsFactory::createForTyping($recipient, $typingIndicator);
        $this->client->send('POST', '/me/messages', null, [], [], $options);
    }

    /**
     * @param string $userId
     * @param array $fields
     *
     * @return UserProfile
     */
    public function getUserProfile(
        $userId,
        array $fields = [
            UserProfile::FIRST_NAME,
            UserProfile::LAST_NAME,
            UserProfile::PROFILE_PIC,
            UserProfile::LOCALE,
            UserProfile::TIMEZONE,
            UserProfile::GENDER,
            UserProfile::PAYMENT_ENABLED,
        ]
    ) {
        $query = [
            'fields' => implode(',', $fields),
        ];

        $response = $this->client->get(sprintf('/%s', $userId), $query);
        $data = $this->decodeResponse($response);

        return UserProfile::create($data);
    }

    /**
     * Subscribe the app to the page
     *
     * @return bool
     */
    public function subscribe() {
        $response = $this->client->post('/me/subscribed_apps');
        $decoded = $this->decodeResponse($response);

        return $decoded['success'];
    }

    /**
     * @param $defaultText
     * @param $extraSettings
     * @param $localizedTexts
     *
     * @return mixed
     */
    public function setGreetingText($defaultText, $localizedTexts = [], array $extraSettings = []) {

        $greetingTexts = array_merge(
        [
            [
            'locale' => 'default',
            'text' => $defaultText
            ]
        ],
        $localizedTexts
        );
        $greeting = new GreetingText($greetingTexts);
        $this->extraSettings = $extraSettings;
        $setting = $this->buildSetting(ThreadSetting::TYPE_GREETING, $greeting);

        return $this->postThreadSettings($setting);
    }

    /**
     * @param string $payload
     * @param $extraSettings
     *
     * @return mixed
     */
    public function setStartedButton($payload, array $extraSettings = array()) {
        $startedButton = new StartedButton($payload);
        $this->extraSettings = $extraSettings;
        $setting = $this->buildSetting(
            ThreadSetting::TYPE_GET_STARTED,
            $startedButton
        );

        return $this->postThreadSettings($setting);
    }

    /**
     * @return mixed
     */
    public function deleteStartedButton() {
        $setting = $this->buildSetting(
            ThreadSetting::TYPE_CALL_TO_ACTIONS,
            ThreadSetting::NEW_THREAD
        );

        return $this->deleteThreadSettings($setting);
    }

    /**
     * @param Button[] $menuButtons
     * @param $extraSettings
     *
     * @return mixed
     */
    public function setPersistentMenu(array $menuButtons, array $extraSettings = array()) {
        if (count($menuButtons) > 5) {
            throw new \InvalidArgumentException('You should not set more than 5 menu items.');
        }
        $this->extraSettings = $extraSettings;
        $setting = $this->buildSetting(
            ThreadSetting::TYPE_CALL_TO_ACTIONS,
            ThreadSetting::EXISTING_THREAD,
            $menuButtons
        );

        return $this->postThreadSettings($setting);
    }

    /**
     * @return mixed
     */
    public function deletePersistentMenu() {
        $setting = $this->buildSetting(
            ThreadSetting::TYPE_CALL_TO_ACTIONS,
            ThreadSetting::EXISTING_THREAD
        );

        return $this->deleteThreadSettings($setting);
    }

    /**
     * @return mixed
     */
    public function deleteGreetingText() {
        $setting = $this->buildSetting(ThreadSetting::TYPE_GREETING);

        return $this->deleteThreadSettings($setting);
    }

    /**
     * Messenger Factory
     *
     * @param string $token
     *
     * @return Messenger
     */
    public static function create($token) {
        $client = new Client($token);

        return new self($client);
    }

    /**
     * @param array $setting
     *
     * @return mixed
     */
    private function postThreadSettings(array $setting) {
        $response = $this->client->post('/me/'. self::SETTINGS_URL_NAMESPACE, $setting);
        return $this->decodeResponse($response);
    }

    /**
     * @param array $setting
     *
     * @return mixed
     */
    private function deleteThreadSettings(array $setting) {
        $response = $this->client->send('DELETE', '/me/'. self::SETTINGS_URL_NAMESPACE, $setting);
        return $this->decodeResponse($response);
    }
    /**
     * @param array $domains
     * @param string $action
     *
     * @return mixed
     */
    public function setDomainWhitelisting($domains, $action = DomainWhitelisting::TYPE_ADD) {
        $domainWhitelisting = new DomainWhitelisting($domains, $action);
        $setting = $this->buildSetting(ThreadSetting::TYPE_DOMAIN_WHITELISTING, null, $domainWhitelisting, true);

        return $this->postThreadSettings($setting);
    }

    /**
     * @return WhitelistedDomains
     */
    public function getDomainWhitelisting() {
        $query = [
            'fields' => DomainWhitelisting::WHITELISTED_DOMAINS,
        ];

        $response = $this->client->get('/me/thread_settings', $query);
        $data = $this->decodeResponse($response);

        return WhitelistedDomains::create($data);
    }

    /**
     * @param string $type
     * @param mixed $value
     * @param bool $mergeValueWithSetting
     *
     * @return array
     */
    private function buildSetting($type, $value = null, $mergeValueWithSetting = false) {
        $setting = [];

        if ($mergeValueWithSetting === true) {
            $setting = array_merge($setting, $value->jsonSerialize());
        } else if (!empty($value)) {

            $setting[$type] = $value;
        }

        $setting = array_merge($setting, $this->extraSettings);

        return $setting;
    }

    /**
     * @param string|Message|Attachment|Template $message
     *
     * @return Message
     */
    private function createMessage($message) {
        if ($message instanceof Message) {
            return $message;
        }

        if ($message instanceof Template) {
            $message = new Attachment(Attachment::TYPE_TEMPLATE, $message);
        }

        if (is_string($message) || $message instanceof Attachment) {
            return new Message($message);
        }

        throw new \InvalidArgumentException('$message should be a string, Message, Attachment or Template');
    }
}
