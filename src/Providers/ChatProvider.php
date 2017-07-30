<?php

declare(strict_types=1);

namespace Demostf\API\Providers;

use Demostf\API\Demo\ChatMessage;

class ChatProvider extends BaseProvider {
    public function getChat(int $demoId) {
        $query = $this->getQueryBuilder();
        $query->select('text', '"from"', 'time')
            ->from('chat')
            ->where($query->expr()->eq('demo_id', $query->createNamedParameter($demoId, \PDO::PARAM_INT)));

        $result = $query->execute();

        return array_map(function (array $row) {
            return new ChatMessage(
                $row['from'],
                (int) $row['time'],
                $row['text']
            );
        }, $result->fetchAll());
    }

    public function storeChatMessage(int $demoId, ChatMessage $message) {
        $query = $this->getQueryBuilder();
        $query->insert('chat')
            ->values([
                'demo_id' => $query->createNamedParameter($demoId, \PDO::PARAM_INT),
                'text' => $query->createNamedParameter($message->getMessage()),
                '"from"' => $query->createNamedParameter($message->getUser()),
                'time' => $query->createNamedParameter($message->getTime(), \PDO::PARAM_INT),
                'created_at' => 'now()',
                'updated_at' => 'now()',
            ]);
        $query->execute();
    }
}
