<?php

namespace App\Repositories\Api;

use App\Contracts\Repositories\Api\MessageRepositoryInterface;
use App\Models\Message;
use Illuminate\Support\Collection;

class MessageRepository implements MessageRepositoryInterface
{
    public function create(array $data): Message
    {
        return Message::create($data);
    }

    public function getConstituencyMessages(string $county, string $constituency): Collection
    {
        return Message::inConstituency($county, $constituency)
            ->get(['username', 'message', 'latitude', 'longitude', 'created_at']);
    }

    public function getNearbyMessages(float $latitude, float $longitude, int $radius = 500): Collection
    {
        return Message::nearby($latitude, $longitude, $radius)
            ->get(['username', 'message', 'latitude', 'longitude', 'created_at']);
    }

    public function getLocationMessages(string $level, string $name, int $limit = 100): Collection
    {
        return Message::inLocation($level, $name)
            ->with(['quotedMessage', 'tag', 'reactions'])
            ->latest()
            ->take($limit)
            ->get([
                'id', 'username', 'message', 'quoted_message_id',
                'country', 'county', 'constituency', 'ward',
                'latitude', 'longitude', 'created_at', 'tag_id',
            ]);
    }

    public function findById(int $id): ?Message
    {
        return Message::find($id);
    }

    public function latest(int $limit = 50): Collection
    {
        return Message::latest()->take($limit)->get();
    }

    public function count(): int
    {
        return Message::count();
    }

    public function delete(Message $message): bool
    {
        return $message->delete();
    }
}
