<?php

namespace App\Services;

use App\Models\Conversation;
use Illuminate\Support\Facades\Http;

class AtService
{
    public function chat(Conversation $conversation, string $userPrompt)
    {
        // 1. Enregistrer le message de l'utilisateur
        $conversation->messages()->create([
            'role' => 'user',
            'content' => $userPrompt
        ]);

        // 2. Récupérer l'historique
        $history = $conversation->messages()->oldest()->get()->map(function ($msg) {
            return ['role' => $msg->role, 'content' => $msg->content];
        })->toArray();

        try {
            // 3. Appel à Groq (sans le commentaire qui brise tout)
            $response = Http::withoutVerifying() 
                ->withToken(env('GROQ_API_KEY'))
                ->timeout(30)
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => 'llama-3.3-70b-versatile',
                    'messages' => array_merge([
                        ['role' => 'system', 'content' => 'Tu es AT, une IA créée par ALDADJ TECH.']
                    ], $history),
                ]);
        
            if ($response->failed()) {
                $aiContent = "Erreur Groq : " . ($response->json('error.message') ?? $response->body());
            } else {
                $aiContent = $response->json('choices.0.message.content') ?? "Format JSON inconnu.";
            }
        
        } catch (\Exception $e) {
            $aiContent = "Erreur technique : " . $e->getMessage();
        }

        // 4. Enregistrer la réponse de AT
        $conversation->messages()->create([
            'role' => 'assistant',
            'content' => $aiContent
        ]);

        return $aiContent;
    }
}