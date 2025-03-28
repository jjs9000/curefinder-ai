<x-layouts.app.sidebar :title="$title ?? null">
    <style>
        /* Fade-in animation */
        .animate-fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Typing dots animation */
        .dot-flashing {
            width: 6px;
            height: 6px;
            margin: 2px;
            background-color: white;
            border-radius: 50%;
            display: inline-block;
            animation: dotFlashing 1.4s infinite ease-in-out;
        }

        .dot-flashing:nth-child(2) {
            animation-delay: 0.2s;
        }

        .dot-flashing:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes dotFlashing {
            0% { opacity: 0.2; }
            50% { opacity: 1; }
            100% { opacity: 0.2; }
        }
    </style>
    
    <flux:main>
        {{ $slot }}
    </flux:main>
</x-layouts.app.sidebar>
