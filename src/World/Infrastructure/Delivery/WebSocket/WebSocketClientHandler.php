<?php

declare(strict_types=1);

namespace PHPMud\World\Infrastructure\Delivery\WebSocket;

use Amp\Http\Server\Request;
use Amp\Http\Server\Response;
use Amp\Websocket\Server\WebsocketClientHandler as AmpWebsocketClientHandler;
use Amp\Websocket\WebsocketClient as AmpWebsocketClient;
use PHPMud\World\Application\Access\AuthenticateCharacter\AuthenticateCharacterCommand;
use PHPMud\World\Application\Access\AuthenticateCharacter\AuthenticateCharacterCommandResponse;
use PHPMud\World\Application\Access\CreateCharacter\CreateCharacterCommand;
use PHPMud\World\Application\Access\CreateCharacter\CreateCharacterCommandResponse;
use PHPMud\World\Application\Access\IdentifyCharacter\IdentifyCharacterCommand;
use PHPMud\World\Application\Access\IdentifyCharacter\IdentifyCharacterCommandResponse;
use PHPMud\World\Application\Character\WhoAmI\WhoAmICommand;
use PHPMud\World\Application\CommandInterface;
use PHPMud\World\Application\CommandResponseInterface;
use PHPMud\World\Application\CommandRouter;
use PHPMud\World\Application\Help\Unknown\UnknownCommand;
use PHPMud\World\Application\Movement\Look\LookCommand;
use PHPMud\World\Application\Time\CurrentDayPhase\CurrentDayPhaseCommand;
use PHPMud\World\Domain\Character\CharacterId;
use PHPMud\World\Domain\Character\Sex;
use PHPMud\World\Infrastructure\Server\ClientDisconnectedException;
use PHPMud\World\Infrastructure\Server\ClientRegistry;
use PHPMud\World\Infrastructure\Server\UnexpectedErrorLogger;
use Webmozart\Assert\Assert;

final readonly class WebSocketClientHandler implements AmpWebsocketClientHandler
{
    public function __construct(
        private CommandRouter $commandRouter,
        private MessageParser $messageParser,
        private MessageSerializer $messageSerializer,
        private ClientRegistry $clientRegistry,
    ) {
    }

    public function handleClient(AmpWebsocketClient $websocketClient, Request $request, Response $response): void
    {
        $client = new WebSocketClient($websocketClient, $this->messageSerializer);
        $this->clientRegistry->add($client);
        echo 'New WebSocket client connected: '.$client->getRemoteAddress().PHP_EOL;
        try {
            while ($message = $websocketClient->receive()) {
                $data = json_decode($message->buffer(), true);
                if (!is_array($data) || !is_string($data['type'] ?? null)) {
                    $client->sendError('Invalid message.');

                    continue;
                }
                echo $client->getRemoteAddress().': '.$data['type'].PHP_EOL;
                try {
                    $this->handleMessage($client, $data);
                } catch (ClientDisconnectedException $exception) {
                    throw $exception;
                } catch (\Throwable $error) {
                    UnexpectedErrorLogger::log($client->getRemoteAddress(), $error);
                    $client->sendError('Unexpected server error.');
                }
            }
        } catch (ClientDisconnectedException) {
        } finally {
            $this->clientRegistry->remove($client);
        }
    }

    /**
     * @param array<array-key, mixed> $message
     */
    private function handleMessage(WebSocketClient $client, array $message): void
    {
        $characterId = $client->getCharacterId();
        if (null !== $characterId) {
            $client->send($this->execute($this->messageParser->parse($message, $characterId)));

            return;
        }

        match ($message['type']) {
            'identify' => $this->handleIdentify($client, $message),
            'authenticate' => $this->handleAuthenticate($client, $message),
            'create' => $this->handleCreate($client, $message),
            default => $client->send($this->execute(new UnknownCommand())),
        };
    }

    /**
     * @param array<array-key, mixed> $message
     */
    private function handleIdentify(WebSocketClient $client, array $message): void
    {
        if (!is_string($message['name'] ?? null)) {
            $client->sendError('Missing character name.');

            return;
        }

        $response = $this->execute(new IdentifyCharacterCommand(trim($message['name'])));
        Assert::isInstanceOf($response, IdentifyCharacterCommandResponse::class);
        $client->identify($response->exists() ? $response->getCharacterName() : null);
        $client->send($response);
    }

    /**
     * @param array<array-key, mixed> $message
     */
    private function handleAuthenticate(WebSocketClient $client, array $message): void
    {
        $characterName = $client->getIdentifiedCharacterName();
        if (null === $characterName) {
            $client->sendError('Identify a character first.');

            return;
        }
        if (!is_string($message['password'] ?? null)) {
            $client->sendError('Missing password.');

            return;
        }

        $response = $this->execute(new AuthenticateCharacterCommand(
            $characterName,
            $message['password'],
            $client->getFailedPasswordAttempts(),
        ));
        Assert::isInstanceOf($response, AuthenticateCharacterCommandResponse::class);
        $client->send($response);
        $characterId = $response->getCharacterId();
        if (null === $characterId) {
            $client->registerFailedPasswordAttempt();
            if ($response->areAttemptsExhausted()) {
                $client->close();
            }

            return;
        }

        $this->enterTheGame($client, $characterId);
    }

    /**
     * @param array<array-key, mixed> $message
     */
    private function handleCreate(WebSocketClient $client, array $message): void
    {
        if (!is_string($message['name'] ?? null) || !is_string($message['password'] ?? null)) {
            $client->sendError('Missing character name or password.');

            return;
        }
        $sex = is_string($message['sex'] ?? null) ? Sex::tryFrom($message['sex']) : null;
        if (null === $sex) {
            $client->sendError('Missing or invalid sex.');

            return;
        }

        $response = $this->execute(new CreateCharacterCommand(trim($message['name']), $message['password'], $sex));
        Assert::isInstanceOf($response, CreateCharacterCommandResponse::class);
        $client->send($response);
        $characterId = $response->getCharacterId();
        if (null !== $characterId) {
            $this->enterTheGame($client, $characterId);
        }
    }

    private function enterTheGame(WebSocketClient $client, CharacterId $characterId): void
    {
        $client->authenticate($characterId);
        $client->send($this->execute(new WhoAmICommand($characterId)));
        $client->send($this->execute(new LookCommand($characterId)));
        $client->send($this->execute(new CurrentDayPhaseCommand()));
    }

    private function execute(CommandInterface $command): CommandResponseInterface
    {
        return $this->commandRouter->resolve($command)->execute($command);
    }
}
