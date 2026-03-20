<?php

namespace App\Services;

use App\Models\Conversation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

        $aiContent = "Désolé, AT rencontre une difficulté technique.";

        try {
            // 3. UTILISATION DE TA VARIABLE : AT_GROQ_KEY
            $apiKey = env('AT_GROQ_KEY');

            if (!$apiKey) {
                throw new \Exception("La clé AT_GROQ_KEY est introuvable sur Render.");
            }

            // 4. Appel à Groq
            $response = Http::withoutVerifying() 
                ->withToken($apiKey)
                ->timeout(60)
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => 'llama-3.3-70b-versatile',
                    'messages' => array_merge([
                        ['role' => 'system', 'content' => 'Tu es AT, une IA créée par ALDADJ TECH.']
                    ], $history),
                ]);
        
            if ($response->failed()) {
                $aiContent = "Erreur Groq (" . $response->status() . ") : " . ($response->json('error.message') ?? "Erreur inconnue");
            } else {
                $aiContent = $response->json('choices.0.message.content') ?? "Réponse vide.";
            }
        
        } catch (\Exception $e) {
            Log::error("Erreur AtService : " . $e->getMessage());
            $aiContent = "Erreur technique : " . $e->getMessage();
        }

        // 5. Enregistrer la réponse de AT
        $conversation->messages()->create([
            'role' => 'assistant',
            'content' => $aiContent
        ]);

        return $aiContent;
    }
}