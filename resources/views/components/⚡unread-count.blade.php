<?php

use Livewire\Component;
use App\Models\ChatMessage;
use Livewire\Attributes\On;

new class extends Component

{
    #[On('messages-read')]
    public function refreshUnreadCount()
    {
        // Refresh the component after messages are marked as read.
    }
    
    public function getUnreadCountProperty(): int
    {
        return ChatMessage::where('receiver_id', auth()->id())
            ->whereNull('read_at')
            ->count();
    }

    public function getConversationsProperty()
    {
        if (!auth()->check()) {
            return collect();
        }

        return ChatMessage::where(function ($query) {
            $query->where('sender_id', auth()->id())
                ->orWhere('receiver_id', auth()->id());
        })
        ->with(['sender', 'receiver'])
        ->latest()
        ->get()
        ->unique(function ($message) {
            return $message->sender_id == auth()->id()
                ? $message->receiver_id
                : $message->sender_id;
        })
        ->take(10)
        ->map(function ($message) {

            $otherUserId = $message->sender_id == auth()->id()
                ? $message->receiver_id
                : $message->sender_id;

            $message->unread_count = ChatMessage::where('sender_id', $otherUserId)
                ->where('receiver_id', auth()->id())
                ->whereNull('read_at')
                ->count();

            return $message;
        });
    }
};
?>

<div
    x-data="{
        open: false,
        init() {
            window.Echo.private('user.{{ auth()->id() }}')
                .listen('ChatMessage', () => {
                    $wire.$refresh();
                });
        }
    }"
    class="position-relative d-inline-flex align-items-center"
>
    <span
        class="text-white mr-2 header-chat-icon position-relative d-flex align-items-center justify-content-center"
        title="Chat"
        data-toggle="tooltip"
        data-placement="bottom"
        style="cursor: pointer;"
        @click="open = !open"
    >
        <i class="fas fa-comment"></i>

        @if($this->unreadCount > 0)
            <span class="unread-badge">
                {{ $this->unreadCount }}
            </span>
        @endif
    </span>

    <div
        x-show="open"
        @click.outside="open = false"
        x-cloak
        class="chat-dropdown shadow"
    >
        <div class="chat-dropdown-header">
            <strong>Messages</strong>
        </div>

        @forelse($this->conversations as $conversation)

            @php
                $otherUser = $conversation->sender_id == auth()->id()
                    ? $conversation->receiver
                    : $conversation->sender;
            @endphp

            <div
                class="chat-dropdown-item {{ $conversation->unread_count > 0 ? 'has-unread' : '' }}"
                wire:key="conversation-{{ $otherUser->id }}"
                @click="
                    $dispatch('start-chat', {
                        userId: {{ $otherUser->id }}
                    });
                    open = false;
                "
            >
            <img
                src="{{ $otherUser->avatar
                    ? asset('storage/avatars/' . $otherUser->avatar)
                    : asset('images/default-avatar.jpg') }}"
                class="avatar-tiny mr-2"
                alt="{{ $otherUser->username }}'s avatar"
            >

            <div class="chat-dropdown-details">

                <div class="d-flex justify-content-between align-items-center">
                    <strong>{{ $otherUser->username }}</strong>

                    @if($conversation->unread_count > 0)
                        <span class="conversation-unread-badge">
                            {{ $conversation->unread_count }}
                        </span>
                    @endif
                </div>

                <div class="chat-message-preview">
                    @if($conversation->sender_id == auth()->id())
                        You:
                    @endif

                    {{ \Illuminate\Support\Str::limit($conversation->message, 35) }}
                </div>

                <div class="chat-message-time">
                    {{ $conversation->created_at->format('g:i A') }}
                </div>

            </div>
        </div>

        @empty

            <div class="chat-dropdown-empty">
                No conversations yet.
            </div>

        @endforelse
    </div>
</div>