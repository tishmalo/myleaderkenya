<?php

namespace App\Services\Api;

use App\Contracts\Repositories\Api\MessageRepositoryInterface;
use App\Contracts\Repositories\Api\MessageReactionRepositoryInterface;
use App\Contracts\Repositories\Api\TagRepositoryInterface;
use App\Contracts\Repositories\Kenya\CountyRepositoryInterface as KenyaCountyRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Collection;

class MessageService
{
    public function __construct(
        private MessageRepositoryInterface $messageRepository,
        private MessageReactionRepositoryInterface $reactionRepository,
        private TagRepositoryInterface $tagRepository,
        private KenyaCountyRepositoryInterface $kenyaCountyRepositoryInterface,
    ) {}

    public function getConstituencies($county){

        return $this->kenyaCountyRepositoryInterface->getConstituenciesByCounty($county);

    }

    public function getCounties(){

        return $this->kenyaCountyRepositoryInterface->getAllCounties();

    }
    public function getLatestMessages(int $limit = 50): array
    {
        $messages = $this->messageRepository->latest($limit);

        return compact('messages');
    }

    public function sendMessage(User $user, array $data): array
    {
        $messageData = [
            'username'     => $user->username ?? $user->name ?? 'Anonymous',
            'message'      => $data['message'],
            'county'       => $data['county'],
            'constituency' => $data['constituency'],
            'latitude'     => $data['latitude'] ?? null,
            'longitude'    => $data['longitude'] ?? null,
        ];

        $this->messageRepository->create($messageData);

        return [
            'message' => 'Message sent successfully to ' . $data['constituency'] . ' constituency'
        ];
    }

    public function getConstituencyMessages(string $county, string $constituency): Collection
    {
        return $this->messageRepository->getConstituencyMessages($county, $constituency);
    }

    public function getNearbyMessages(float $latitude, float $longitude): Collection
    {
        return $this->messageRepository->getNearbyMessages($latitude, $longitude, 500);
    }

    public function sendLocationMessage(User $user, array $data): array
    {
        $messageData = [
            'username'          => $user->username ?? $user->name ?? 'Anonymous',
            'message'           => $data['message'],
            'quoted_message_id' => $data['quoted_message_id'] ?? null,
            'tag_id'            => $data['tag_id'] ?? null,
            'latitude'          => $data['latitude'] ?? null,
            'longitude'         => $data['longitude'] ?? null,
            'country' =>     $data['country'] ?? '',
            'county'  =>      $data['county']  ?? '',
            'constituency' => $data['constituency'] ?? '',
            'ward' =>        $data['ward']  ?? '',
        ];
 
        // Set correct location field
        switch ($data['level']) {
            case 'country':      $messageData['country'] = $data['name']; break;
            case 'county':       $messageData['county'] = $data['name']; break;
            case 'constituency': $messageData['constituency'] = $data['name']; break;
            case 'ward':         $messageData['ward'] = $data['name']; break;
        }
 
        $message = $this->messageRepository->create($messageData);
 
        return [
            'success' => true,
            'message' => 'Message sent successfully',
            'data'    => $message->load(['quotedMessage', 'tag'])
        ];
    }

 

    public function getLocationMessages(string $level, string $name): array
    {
        $messages = $this->messageRepository->getLocationMessages($level, $name);

        return [
            'success' => true,
            'messages' => $messages
        ];
    }

    public function reactToMessage(User $user, int $messageId, string $reaction): array
    {
        $message = $this->messageRepository->findById($messageId);

        if (!$message) {
            throw new \Exception('Message not found', 404);
        }

        // Remove previous reaction by this user
        $this->reactionRepository->deleteUserReaction($messageId, $user->id);

        // Create new reaction
        $reactionModel = $this->reactionRepository->create([
            'message_id' => $messageId,
            'user_id'    => $user->id,
            'reaction'   => $reaction,
        ]);

        return [
            'success' => true,
            'message' => 'Reaction added successfully',
            'reaction' => $reactionModel
        ];
    }

    public function getTags(): array
    {
        return [
            'success' => true,
            'tags'    => $this->tagRepository->all()
        ];
    }

    public function storeMessageFromWeb(User $user, array $data): void
    {
        $messageData = [
            'username'     => $user->username ?? $user->name ?? 'Web User',
            'message'      => $data['message'],
            'county'       => $data['county'],
            'constituency' => $data['constituency'],
            'latitude'     => $data['latitude'] ?? 0,
            'longitude'    => $data['longitude'] ?? 0,
        ];

        $this->messageRepository->create($messageData);
    }

    public function deleteMessage(User $user, int $messageId): bool
    {
        $message = $this->messageRepository->findById($messageId);

        if (!$message) {
            throw new \Exception('Message not found', 404);
        }

        // Check if user owns the message (admin check done by middleware)
        if ($message->username !== $user->username) {
            throw new \Exception('Unauthorized to delete this message', 403);
        }

        return $this->messageRepository->delete($message);
    }
}
