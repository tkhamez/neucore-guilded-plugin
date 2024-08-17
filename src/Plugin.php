<?php

declare(strict_types=1);

namespace Neucore\Plugin\Guilded;

use Neucore\Plugin\Core\FactoryInterface;
use Neucore\Plugin\Core\OutputInterface;
use Neucore\Plugin\Data\CoreAccount;
use Neucore\Plugin\Data\CoreCharacter;
use Neucore\Plugin\Data\PluginConfiguration;
use Neucore\Plugin\Data\ServiceAccountData;
use Neucore\Plugin\Exception;
use Neucore\Plugin\GeneralInterface;
use Neucore\Plugin\ServiceInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class Plugin implements ServiceInterface, GeneralInterface
{
    private const LOG_PREFIX = 'neucore-guilded-plugin: ';
    private const SESSION_AUTH_KEY = 'guilded_plugin_auth_key';

    private ClientInterface $httpClient;

    // PluginInterface

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly PluginConfiguration $pluginConfiguration,
        private readonly FactoryInterface $factory,
    ) {
        $this->httpClient = $factory->createHttpClient();
    }

    public function onConfigurationChange(): void
    {
    }

    public function request(
        string $name,
        ServerRequestInterface $request,
        ResponseInterface $response,
        ?CoreAccount $coreAccount,
    ): ResponseInterface {
        $config = $this->factory->createSymfonyYamlParser()
            ->parse($this->pluginConfiguration->configurationData);
        if (!is_array($config)) {
            $response->getBody()->write('Configuration is incomplete.');
            return $response;
        }

        if ($name === 'link') {
            try {
                $_SESSION[self::SESSION_AUTH_KEY] = 'auth_' . bin2hex(random_bytes(rand(10, 12)));
            } catch (\Exception $e) {
                throw new Exception('Unable to generate auth key', 0, $e);
            }
            $response->getBody()->write($this->includeView($config));
            return $response;
        }

        if ($name === 'check-message') {
            $success = $this->checkMessages($config);
            $response->getBody()->write($this->includeView($config, ['success' => $success]));
            return $response;
        }

        return $response->withStatus(404);
    }

    // ServiceInterface

    public function getAccounts(array $characters): array
    {
        # TODO

        $charId = 0;
        foreach ($characters as $character) {
            if ($character->main) {
                $charId = $character->id;
            }
        }

        return [
            new ServiceAccountData($charId, '[unknown]')
        ];
    }

    public function register(
        CoreCharacter $character,
        array $groups,
        string $emailAddress,
        array $allCharacterIds
    ): ServiceAccountData {
        # TODO
        throw new Exception('Not implemented');
    }

    public function updateAccount(
        CoreCharacter $character,
        array $groups,
        ?CoreCharacter $mainCharacter
    ): void {
        # TODO
        throw new Exception('Not implemented');
    }

    public function updatePlayerAccount(CoreCharacter $mainCharacter, array $groups): void
    {
        # TODO
        throw new Exception('Not implemented');
    }

    public function moveServiceAccount(int $toPlayerId, int $fromPlayerId): bool
    {
        # TODO
        throw new Exception('Not implemented');
    }

    public function resetPassword(int $characterId): string
    {
        throw new Exception('Not implemented');
    }

    public function getAllAccounts(): array
    {
        return [];
    }

    public function getAllPlayerAccounts(): array
    {
        # TODO
        return [];
    }

    public function search(string $query): array
    {
        # TODO
        return [];
    }

    // GeneralInterface

    public function getNavigationItems(): array
    {
        return [];
    }

    public function command(array $arguments, array $options, OutputInterface $output): void
    {
    }

    // neucore-guilded-plugin

    private function includeView(array $config, array $params = []): string
    {
        $pluginId = $this->pluginConfiguration->id;
        $serverName = $config['ServerName'] ?? '';
        $serverLink = $config['ServerLink'] ?? '';
        $inviteLink = $config['InviteLink'] ?? '';
        $authKey = $_SESSION[self::SESSION_AUTH_KEY];
        $success = $params['success'] ?? null;

        ob_start();
        include __DIR__ . '/../views/link.php';
        return ob_get_clean();
    }

    private function checkMessages(array $config): bool
    {
        $botAccessToken = $config['BotAccessToken'] ?? '';
        $authChannelId = $config['AuthChannelId'] ?? '';

        // https://www.guilded.gg/docs/api/chat/ChannelMessageReadMany
        $request = $this->factory->createHttpRequest(
            'GET',
            "https://www.guilded.gg/api/v1/channels/$authChannelId/messages",
            [
                'Authorization' => "Bearer $botAccessToken",
                'Accept' => 'application/json',
                'Content-type' => 'application/json',
            ],
        );
        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            $this->logger->error(self::LOG_PREFIX . $e->getMessage());
            return false;
        }
        if ($response->getStatusCode() !== 200) {
            $this->logger->error(self::LOG_PREFIX . 'Invalid response.');
            return false;
        }

        $body = json_decode($response->getBody()->getContents());
        if (!is_object($body) || !isset($body->messages) || !is_array($body->messages)) {
            $this->logger->error(self::LOG_PREFIX . 'Invalid response body.');
            return false;
        }

        foreach ($body->messages as $message) {
            if (trim($message->content) === $_SESSION[self::SESSION_AUTH_KEY]) {

                # TODO Store Guilded user ID together with Neucore user ID in a database.
                #dump("guildedUserId: $message->createdBy");
                # TODO delete message $message->id

                return true;
            }
        }

        return false;
    }
}
