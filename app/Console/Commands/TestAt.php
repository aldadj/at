<?php

namespace App\Services;

use App\Models\Conversation;
use Illuminate\Support\Facades\Http;

class AtService
{
    public function chat(Conversation $conversation, string $userPrompt)
    {
        $conversation->messages()->create([
            'role' => 'user',
            'content' => $userPrompt
        ]);

        $history = $conversation->messages()->oldest()->get()->map(function ($msg) {
            return ['role' => $msg->role, 'content' => $msg->content];
        })->toArray();

        try {
           
            $response = Http::withoutVerifying() // Ajoute ceci pour ignorer le SSL sur ton PC local
    ->withToken(env('GROQ_API_KEY'))
    // ... reste du code
                ->timeout(30)
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => 'llama-3.3-70b-versatile',
                    'messages' => array_merge([
                        ['role' => 'system', 'content' => 'Tu es AT, une IA créée par Ali Loukmanz à Ouagadougou.']
                    ], $history),
                ]);
        
            if ($response->failed()) {
                // Cela va nous dire exactement pourquoi Groq refuse (ex: clé invalide)
                $aiContent = "Erreur Groq : " . $response->body();
            } else {
                $aiContent = $response->json('choices.0.message.content') ?? "Format JSON inconnu.";
            }
        
        } catch (\Exception $e) {
            $aiContent = "Erreur technique : " . $e->getMessage();
        }

        $conversation->messages()->create([
            'role' => 'assistant',
            'content' => $aiContent
        ]);

        return $aiContent;
    }
}