<div class="w-full h-full mx-auto p-4 rounded-lg shadow-lg transition-all duration-300 
    bg-white text-black dark:bg-gray-900 dark:text-white border border-gray-300 dark:border-gray-700 flex flex-col h-96 max-h-screen">
    
    <!-- Typing Indicator -->
    <div id="typing-indicator" class="hidden">
        <span class="dot-flashing"></span>
        <span class="dot-flashing"></span>
        <span class="dot-flashing"></span>
    </div>

    <!-- Loading Indicator -->
    <div id="loading-indicator" class="hidden flex items-center justify-center">
        <div class="loader ease-linear rounded-full border-4 border-t-4 border-gray-200 h-6 w-6"></div>
    </div>

    <!-- Chat and Input Container (Fixed Layout) -->
    <div class="flex flex-col flex-grow overflow-hidden h-96">
        
        <!-- Chat Container -->
        <div id="chat-box" class="flex-grow overflow-y-auto border-b border-gray-300 dark:border-gray-700 mb-4 p-2 space-y-2 relative bg-white bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] [background-size:16px_16px] dark:bg-gray-900 dark:bg-[radial-gradient(#374151_1px,transparent_1px)]">
            @if(empty($messages))
                <!-- Placeholder for new users -->
                <div class="flex justify-center items-center h-full">
                    <div class="text-center text-gray-500 dark:text-gray-400 animate-pulse">
                        <p class="text-lg">Start chatting with Gemini</p>
                        <p class="text-sm">Your AI assistant is ready to help!</p>
                    </div>
                </div>
            @else
                @foreach($messages as $message)
                    <div class="flex @if($message['sender'] === 'user') justify-end @else justify-start @endif">
                        <div class="px-6 py-3 rounded-xl max-w-md text-base leading-relaxed shadow-md opacity-0 animate-fade-in
                            @if($message['sender'] === 'user')
                                bg-gray-800 text-white rounded-br-none
                            @else
                                bg-gray-300 text-black dark:bg-gray-700 dark:text-white rounded-bl-none
                            @endif">
                            {!! nl2br(e($message['text'])) !!}
                        </div>
                    </div>
                @endforeach
            @endif

            <!-- Typing Animation for AI -->
            @if($isTyping)
                <div class="flex justify-start">
                    <div class="px-6 py-3 rounded-xl bg-gray-300 dark:bg-gray-700 text-black dark:text-white max-w-md text-base leading-relaxed shadow-md animate-fade-in">
                        <span class="dot-flashing"></span> <span class="dot-flashing"></span> <span class="dot-flashing"></span>
                    </div>
                </div>
            @endif
        </div>

        <!-- Error Message -->
        @if ($errorMessage)
            <div class="bg-red-500 text-white text-sm p-2 rounded-lg mb-2">
                ⚠️ {{ $errorMessage }}
            </div>
        @endif

        <!-- Input Box (Ensuring it stays at the bottom) -->
        <div class="flex items-center space-x-2 w-full mt-2 p-2">
            <input type="text" wire:model="userInput" wire:keydown.enter="sendMessage"
                   class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2
                   focus:ring-gray-800 dark:bg-gray-800 dark:text-white placeholder-gray-500 dark:placeholder-gray-400"
                   placeholder="Type your message..." {{ $isLoading ? 'disabled' : '' }}>

            <button wire:click="sendMessage" {{ $isLoading ? 'disabled' : '' }}
                    class="px-4 py-2 bg-gray-800 hover:bg-gray-900 text-white font-semibold rounded-md transition relative">
                <span class="{{ $isLoading ? 'hidden' : '' }}">Send</span>
                @if($isLoading)
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="loader ease-linear rounded-full border-4 border-t-4 border-gray-200 h-6 w-6"></div>
                    </div>
                @endif
            </button>
        </div>

    </div> <!-- Closing flex container -->
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        if (window.Livewire) {
            window.Livewire.on('update-typing-status', (status) => {
                let typingIndicator = document.getElementById('typing-indicator');
                if (typingIndicator) {
                    typingIndicator.classList.toggle('hidden', !status);
                }
            });

            window.Livewire.on('update-loading-status', (status) => {
                console.log("Loading status:", status); // Debugging line
                let loadingIndicator = document.getElementById('loading-indicator');
                if (loadingIndicator) {
                    loadingIndicator.classList.toggle('hidden', !status);
                }
            });

            window.Livewire.on('delayed-response', ({ aiMessage }) => {
                console.log("AI Response Object:", aiMessage);
                setTimeout(() => {
                    if (window.Livewire.first()) {
                        window.Livewire.first().addMessage(aiMessage);
                        // Delay scrolling to ensure the message is fully rendered
                        setTimeout(scrollToBottom, 100); // Adjust the delay as needed
                    } else {
                        console.error("Livewire component not found");
                    }
                }, 2000);
            });
        }

        // Ensure the chat box scrolls to the bottom on page load
        window.addEventListener('load', scrollToBottom);
    });

    document.addEventListener("livewire:initialized", function () {
        Livewire.on("console-log", (data) => {
            console.log("AI Chat Log:", JSON.stringify(data, null, 2));
        });
    });

    function scrollToBottom() {
        const chatBox = document.getElementById('chat-box');
        if (chatBox) {
            chatBox.scrollTop = chatBox.scrollHeight;
        }
    }
</script>
