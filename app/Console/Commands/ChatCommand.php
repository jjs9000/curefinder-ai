<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;

use function Laravel\Prompts\textarea;

class ChatCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chat';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Chat using Prism';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $response = Prism::text()
            ->using(Provider::Gemini, 'gemini-1.5-flash-8b')
            ->withSystemPrompt('You are an expert mathematician who explains concepts simply.')
            ->withPrompt('Explain the Pythagorean theorem.')
            ->asText();

        echo $response->text;
    }
}
