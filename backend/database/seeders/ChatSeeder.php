<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Seeder;

class ChatSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        if ($users->count() < 3) {
            return;
        }

        $user1 = $users[0]; // Admin
        $user2 = $users[1]; // Moderator
        $user3 = $users[2]; // Demo user

        // 1. Direct conversation between user1 and user3
        $directChat = Conversation::create([
            'type' => 'direct',
            'title' => null,
            'avatar_url' => null,
            'created_by' => $user1->id,
            'last_message_at' => now(),
        ]);

        ConversationParticipant::create([
            'conversation_id' => $directChat->id,
            'user_id' => $user1->id,
            'role' => 'member',
            'joined_at' => now()->subDays(2),
        ]);

        ConversationParticipant::create([
            'conversation_id' => $directChat->id,
            'user_id' => $user3->id,
            'role' => 'member',
            'joined_at' => now()->subDays(2),
        ]);

        $msg1 = Message::create([
            'conversation_id' => $directChat->id,
            'sender_id' => $user1->id,
            'body' => 'Hey! Welcome to Nova Social. How are things looking?',
            'is_read' => true,
            'created_at' => now()->subHours(5),
        ]);

        $msg2 = Message::create([
            'conversation_id' => $directChat->id,
            'sender_id' => $user3->id,
            'body' => 'Everything is working very smoothly! Loving the clean API structure.',
            'is_read' => true,
            'created_at' => now()->subHours(4),
        ]);

        $msg3 = Message::create([
            'conversation_id' => $directChat->id,
            'sender_id' => $user1->id,
            'body' => 'Awesome! Check out our new design docs here.',
            'media_url' => 'https://images.unsplash.com/photo-1507238691740-187a5b1d37b8?w=800&fit=crop',
            'media_type' => 'image',
            'is_read' => false,
            'created_at' => now()->subMinutes(15),
        ]);

        $directChat->update(['last_message_at' => $msg3->created_at]);

        // Update read watermarks
        ConversationParticipant::where('conversation_id', $directChat->id)
            ->where('user_id', $user1->id)
            ->update(['last_read_message_id' => $msg3->id]);
        ConversationParticipant::where('conversation_id', $directChat->id)
            ->where('user_id', $user3->id)
            ->update(['last_read_message_id' => $msg2->id]);

        // 2. Group conversation
        $groupChat = Conversation::create([
            'type' => 'group',
            'title' => 'Nova Core Creators',
            'avatar_url' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=400&fit=crop',
            'created_by' => $user1->id,
            'last_message_at' => now(),
        ]);

        $groupMembers = [$user1, $user2, $user3];
        if ($users->count() >= 5) {
            $groupMembers[] = $users[3];
            $groupMembers[] = $users[4];
        }

        foreach ($groupMembers as $member) {
            ConversationParticipant::create([
                'conversation_id' => $groupChat->id,
                'user_id' => $member->id,
                'role' => ($member->id === $user1->id) ? 'admin' : 'member',
                'joined_at' => now()->subDays(5),
            ]);
        }

        $groupMsg1 = Message::create([
            'conversation_id' => $groupChat->id,
            'sender_id' => $user1->id,
            'body' => 'Welcome team! Let us use this channel for roadmap discussions and releases.',
            'is_read' => true,
            'created_at' => now()->subHours(3),
        ]);

        $groupMsg2 = Message::create([
            'conversation_id' => $groupChat->id,
            'sender_id' => $user2->id,
            'body' => 'Sounds great. Moderation rules are set up and active.',
            'is_read' => true,
            'created_at' => now()->subHours(2),
        ]);

        $groupChat->update(['last_message_at' => $groupMsg2->created_at]);
    }
}
