<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Services\AtService;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    protected $atService;

    public function __construct(AtService $atService)
    {
        $this->atService = $atService;
    }

    public function index($id = null)
    {
        $conversations = Conversation::latest()->get();
        $activeConversation = $id ? Conversation::with('messages')->find($id) : null;
    
        // IMPORTANT : On retire le "at." car le fichier est chat.blade.php
        return view('chat', compact('conversations', 'activeConversation'));
    }

    public function destroy(Conversation $conversation)
    {
        $conversation->messages()->delete();
        $conversation->delete();

        return redirect()->route('at.chat')->with('success', 'Discussion supprimée.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'conversation_id' => 'nullable|exists:conversations,id'
        ]);

        // 1. On récupère ou on crée la conversation
        if ($request->conversation_id) {
            $conversation = Conversation::find($request->conversation_id);
        } else {
            // On crée un titre propre à partir du premier message
            $title = strlen($request->message) > 30 
                     ? substr($request->message, 0, 30) . '...' 
                     : $request->message;
            $conversation = Conversation::create(['title' => $title]);
        }

        // 2. On appelle le service une SEULE fois et on récupère la réponse
        $aiResponse = $this->atService->chat($conversation, $request->message);

        // 3. GESTION DE LA RÉPONSE (AJAX vs Classique)
        if ($request->ajax() || $request->wantsJson()) {
            // Si c'est du JavaScript (Alpine.js/Axios), on renvoie du JSON
            return response()->json([
                'content' => $aiResponse,
                'conversation_id' => $conversation->id
            ]);
        }

        // Sinon (envoi classique), on redirige vers la page de chat
        return redirect()->route('at.chat', $conversation->id);
    }
}