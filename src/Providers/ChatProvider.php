<?php

declare(strict_types=1);

namespace Demostf\API\Providers;

use Demostf\API\Demo\ChatMessage;
use PDO;

class ChatProvider extends BaseProvider {
    /**
     * @return ChatMessage[]
     */
    public function getChat(int $demoId): array {
        $query = $this->getQueryBuilder();
        $query->select('text', '"from"', 'time')
            ->from('chat')
            ->where($query->expr()->eq('demo_id', $query->createNamedParameter($demoId, PDO::PARAM_INT)))
            ->orderBy('time', 'ASC')
            ->addOrderBy('id', 'ASC');

        $result = $query->execute();

        return array_map(function (array $row) {
            return new ChatMessage(
                $row['from'],
                (int) $row['time'],
                $row['text']
            );
        }, $result->fetchAll());
    }

    public function storeChatMessage(int $demoId, ChatMessage $message): void {
        $query = $this->getQueryBuilder();
        $query->insert('chat')
            ->values([
                'demo_id' => $query->createNamedParameter($demoId, PDO::PARAM_INT),
                'text' => $query->createNamedParameter($message->getMessage()),
                '"from"' => $query->createNamedParameter($message->getUser()),
                'time' => $query->createNamedParameter($message->getTime(), PDO::PARAM_INT),
                'created_at' => 'now()',
                'updated_at' => 'now()',
            ]);
        $query->execute();
    }
}
