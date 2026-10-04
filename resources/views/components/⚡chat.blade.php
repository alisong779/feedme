<?php

use Livewire\Component;
use App\Models\ChatMessage;
use App\Models\User;

new class extends Component
{
    public ?int $receiverId = null;

    public string $message = '';

    public function startChat(int $userId): void
    {
        $this->receiverId = $userId;
    }

    public function closeChat(): void
    {
        $this->receiverId = null;
        $this->reset('message');
    }

    public function getRecipientProperty()
    {
        if (!$this->receiverId) {
            return null;
        }

        return User::find($this->receiverId);
    }

    public function sendMessage(): void
    {
        $this->validate([
            'message' => 'required|string|max:5000',
        ]);

        $chatMessage = ChatMessage::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $this->receiverId,
            'message' => strip_tags($this->message),
        ]);

        broadcast(new \App\Events\ChatMessage($chatMessage))->toOthers();

        $this->reset('message');
    }

    public function getMessagesProperty()
    {
        if (!$this->receiverId) {
            return collect();
        }

        return ChatMessage::where(function ($query) {
            $query->where('sender_id', auth()->id())
                  ->where('receiver_id', $this->receiverId);
        })
        ->orWhere(function ($query) {
            $query->where('sender_id', $this->receiverId)
                  ->where('receiver_id', auth()->id());
        })
        ->with('sender')
        ->oldest()
        ->get();
    }
};
?>

<div
    class="livewire-chat"
    x-data="{
        receiverId: null,
        channel: null,
        minimized: false,

        startChat(userId) {
            this.receiverId = userId;
            this.minimized = false;

            $wire.startChat(userId);

            const user1 = Math.min({{ auth()->id() }}, userId);
            const user2 = Math.max({{ auth()->id() }}, userId);
            const channelName = `chat.${user1}.${user2}`;

            if (this.channel) {
                window.Echo.leave(this.channel);
            }

            this.channel = channelName;

            window.Echo.private(channelName)
                .listen('ChatMessage', () => {
                    $wire.$refresh().then(() => {
                        const chatLog = document.querySelector('.chat-log');

                        if (chatLog) {
                            chatLog.scrollTop = chatLog.scrollHeight;
                        }
                    });
                });
        },

        closeChat() {
            this.receiverId = null;
            this.minimized = false;

            if (this.channel) {
                window.Echo.leave(this.channel);
                this.channel = null;
            }

            $wire.closeChat();
        }
    }"
    @start-chat.window="startChat($event.detail.userId)"
>

    @if($receiverId)

        <div class="chat-title-bar d-flex justify-content-between align-items-center">

            <div class="d-flex align-items-center">
                <img
                    src="{{ $this->recipient?->avatar
                        ? asset('storage/avatars/' . $this->recipient->avatar)
                        : asset('images/default-avatar.jpg') }}"
                    alt="{{ $this->recipient?->username }}'s avatar"
                    class="avatar-small mr-2"
                >

                <span>{{ $this->recipient?->username }}</span>
            </div>

            <div>

                <button
                    type="button"
                    class="btn btn-sm text-white"
                    @click="minimized = !minimized"
                    title="Minimize chat"
                >
                    <i class="fas fa-minus"></i>
                </button>

                <button
                    type="button"
                    class="btn btn-sm text-white"
                    @click="closeChat()"
                    title="Close chat"
                >
                    <i class="fas fa-times"></i>
                </button>

            </div>

        </div>

        <div
            class="chat-log"
            x-show="!minimized"
            x-ref="chatLog"
            x-init="$nextTick(() => $el.scrollTop = $el.scrollHeight)"
        >

            @forelse($this->messages as $chatMessage)

                @if($chatMessage->sender_id === auth()->id())

                    <div class="chat-self">
                        <div class="chat-message">
                            <div class="chat-message-inner">
                                {{ $chatMessage->message }}

                                <div class="chat-timestamp">
                                    {{ $chatMessage->created_at->format('g:i A') }}
                                </div>
                            </div>
                        </div>
                    </div>

                @else

                    <div class="chat-other">
                        <div class="chat-message">
                            <div class="chat-message-inner">
                                <strong>{{ $chatMessage->sender->username }}:</strong>
                                {{ $chatMessage->message }}

                                <div class="chat-timestamp">
                                    {{ $chatMessage->created_at->format('g:i A') }}
                                </div>
                            </div>
                        </div>
                    </div>

                @endif

            @empty

                <div class="chat-empty text-center">
                    <img
                        src="{{ $this->recipient?->avatar
                            ? asset('storage/avatars/' . $this->recipient->avatar)
                            : asset('images/default-avatar.jpg') }}"
                        alt="{{ $this->recipient?->username }}'s avatar"
                        class="avatar-small mb-2"
                    >

                    <div>
                        <strong>{{ $this->recipient?->username }}</strong>
                    </div>

                    <div class="text-muted small">
                        Start a conversation with {{ $this->recipient?->username }}.
                    </div>
                </div>

            @endforelse

            <form wire:submit="sendMessage" class="border-top p-2">

                <div class="input-group">

                    <input
                        type="text"
                        wire:model="message"
                        class="form-control"
                        placeholder="Type a message..."
                        autocomplete="off"
                    >

                    <div class="input-group-append">

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Send
                        </button>

                    </div>

                </div>

            </form>

        </div>

    @endif

</div>