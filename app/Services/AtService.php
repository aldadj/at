<?php

namespace App\Services;

use App\Models\Conversation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AtService
{
    public function chat(Conversation $conversation, string $userPrompt)
    {
        try {
            // 1. Enregistrer le message de l'utilisateur
            $conversation->messages()->create([
                'role' => 'user',
                'content' => $userPrompt
            ]);

            // 2. Récupérer l'historique
            $history = $conversation->messages()->oldest()->get()->map(function ($msg) {
                return ['role' => $msg->role, 'content' => $msg->content];
            })->toArray();

            // 3. Vérification de la clé (Diagnostic direct)
            $apiKey = env('AT_GROQ_KEY') ?: env('GROQ_API_KEY');
            if (empty($apiKey)) {
                return "ERREUR CONFIG : Clé API manquante. Ajoutez AT_GROQ_KEY dans le fichier .env et lancez 'php artisan config:clear'.";
            }

            // 4. Appel à Groq avec gestion de timeout plus longue
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
                $errorMsg = $response->json('error.message') ?? $response->body();
                $aiContent = "ERREUR API (" . $response->status() . ") : " . $errorMsg;
            } else {
                $aiContent = $response->json('choices.0.message.content') ?? "Réponse vide de Groq.";
            }
        
        } catch (\Exception $e) {
            // On renvoie l'erreur PHP réelle pour qu'elle s'affiche dans la bulle de chat
            Log::error("AtService Exception : " . $e->getMessage());
            $aiContent = "ERREUR TECHNIQUE : " . $e->getMessage();
        }

        // 5. Enregistrer le résultat (même si c'est une erreur, pour debug)
        $conversation->messages()->create([
            'role' => 'assistant',
            'content' => $aiContent
        ]);

        return $aiContent;
    }
}