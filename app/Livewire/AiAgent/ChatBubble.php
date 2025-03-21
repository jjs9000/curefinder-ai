<?php

namespace App\Livewire\AiAgent;

use Livewire\Component;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;
use App\Models\ChatMessage;
use Illuminate\Support\Facades\Auth;

class ChatBubble extends Component
{
    public $messages = [];
    public $userInput = '';
    public $errorMessage = '';
    public $isTyping = false;
    public $isLoading = false;

    public function mount()
    {
        // Load existing messages for the authenticated user
        $this->messages = ChatMessage::where('user_id', Auth::id())
            ->orderBy('created_at', 'asc')
            ->get()
            ->toArray();
    }

    public function sendMessage()
    {
        // Set loading state to true
        $this->isLoading = true;
        $this->dispatch('update-loading-status', true);

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

        // Save the message to the database
        ChatMessage::create([
            'user_id' => Auth::id(),
            'sender' => 'user',
            'text' => $this->userInput
        ]);

        // Log user message
        $this->dispatch('console-log', sender: 'User', text: $this->userInput);

        // Show AI typing animation
        $this->isTyping = true;
        $this->dispatch('update-typing-status', true);

        // Process AI response asynchronously
        $this->processAIResponse($this->userInput);

        // Clear user input immediately
        $this->userInput = '';
    }

    public function processAIResponse($input)
    {
        try {
            $response = Prism::text()
                ->using(Provider::Gemini, 'gemini-1.5-flash-8b')
                ->withPrompt($input)
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

            // Store the AI message in the database after dispatching the delayed response
            ChatMessage::create([
                'user_id' => Auth::id(),
                'sender' => 'ai',
                'text' => $aiMessage['text']
            ]);
        } catch (\Exception $e) {
            $this->errorMessage = '❌ AI Error: ' . $e->getMessage();
            $this->dispatch('console-log', sender: 'Error', text: $this->errorMessage);
            $this->isTyping = false;
            $this->dispatch('update-typing-status', false);

            // // Reset loading state after error
            // $this->isLoading = false;
            // $this->dispatch('update-loading-status', false);
        }
    }

    // Add this method to handle the delayed message display
    public function addMessage($message)
    {
        // Add the message to the messages array
        $this->messages[] = $message;

        // Turn off the typing indicator
        $this->isTyping = false;

        // Reset loading state after successful processing
        $this->isLoading = false;
    }


    public function render()
    {
        return view('livewire.ai-agent.chat-bubble');
    }
}
