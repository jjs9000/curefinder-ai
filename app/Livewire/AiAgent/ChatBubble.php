<?php

namespace App\Livewire\AiAgent;

use Livewire\Attributes\On;
use Livewire\Component;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;

class ChatBubble extends Component
{
    public $messages = [];
    public $userInput = '';
    public $errorMessage = '';
    public $isTyping = false;

    public function sendMessage()
    {
        $this->errorMessage = '';

        if (empty($this->userInput)) {
            $this->errorMessage = '⚠️ Message cannot be empty!';
            return;
        }

        // Store user input with explicit keys
        $userMessage = [
            'sender' => 'user',
            'text' => $this->userInput
        ];
        $this->messages[] = $userMessage;

        // Log user message
        $this->dispatch('console-log', sender: 'User', text: $this->userInput);

        // Show AI typing animation
        $this->isTyping = true;
        $this->dispatch('update-typing-status', true);

        try {
            $response = Prism::text()
                ->using(Provider::Gemini, 'gemini-1.5-flash-8b')
                ->withPrompt($this->userInput)
                ->asText();

            // Ensure AI response exists and has all required keys
            $aiMessage = [
                'sender' => 'ai',
                'text' => $response->text ?? 'No response received.',
                'finishReason' => $response->finishReason->name ?? 'Unknown',
                'promptTokens' => $response->usage->promptTokens ?? 0,
                'completionTokens' => $response->usage->completionTokens ?? 0
            ];

            // Log AI response to console
            $this->dispatch('console-log', sender: 'AI', text: $aiMessage['text']);

            // Store the AI message for delayed display
            $this->dispatch('delayed-response', aiMessage: $aiMessage);
        } catch (\Exception $e) {
            $this->errorMessage = '❌ AI Error: ' . $e->getMessage();
            $this->dispatch('console-log', sender: 'Error', text: $this->errorMessage);
            $this->isTyping = false;
            $this->dispatch('update-typing-status', false);
        }

        $this->userInput = '';
    }

    // Add this method to handle the delayed message display
    public function addMessage($message)
    {
        // Add the message to the messages array
        $this->messages[] = $message;

        // Turn off the typing indicator
        $this->isTyping = false;
    }


    public function render()
    {
        return view('livewire.ai-agent.chat-bubble');
    }
}
