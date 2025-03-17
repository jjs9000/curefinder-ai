<?php

namespace App\Livewire\AiAgent;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Livewire\Component;

class ChatBubble extends Component
{
    public string $userConfigJson;

    public function mount()
    {
        $userConfig = [];
        
        if (Auth::check()) {
            $user = Auth::user();
            $userConfig = [
                'user_id' => $user->id,
                'user_hash' => hash_hmac('sha256', $user->id, Config::get('services.chatbase.secret')),
                'user_metadata' => [
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ];
        } else {
            $userConfig = [
                'user_id' => 'guest',
                'user_hash' => '',
                'user_metadata' => [
                    'name' => 'Guest',
                    'email' => '',
                ],
            ];
        }
        
        // Convert to JSON string for direct use in JavaScript
        $this->userConfigJson = json_encode($userConfig);
    }

    public function render()
    {
        return view('livewire.ai-agent.chat-bubble');
    }
}
