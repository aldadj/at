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

        // Initialisation par défaut pour éviter le crash
        $aiContent = "Désolé, AT rencontre une difficulté technique.";

        try {
            // 3. Appel à Groq
            // Note : Il est préférable d'utiliser config('services.groq.key') 
            // Mais pour Render, assure-toi que GROQ_API_KEY est bien dans "Environment"
            $apiKey = env('AT_GROQ_KEY');

            if (!$apiKey) {
                throw new \Exception("La clé API GROQ est manquante sur le serveur.");
            }

            $response = Http::withoutVerifying() 
                ->withToken($apiKey)
                ->timeout(30)
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => 'llama-3.3-70b-versatile',
                    'messages' => array_merge([
                        ['role' => 'system', 'content' => 'Tu es AT, une IA créée par ALDADJ TECH.']
                    ], $history),
                ]);
        
            if ($response->failed()) {
                $aiContent = "Erreur Groq : " . ($response->json('error.message') ?? "Erreur HTTP " . $response->status());
            } else {
                $aiContent = $response->json('choices.0.message.content') ?? "Réponse vide du moteur.";
            }
        
        } catch (\Exception $e) {
            // On log l'erreur pour la voir dans les logs Render
            Log::error("Erreur AtService : " . $e->getMessage());
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