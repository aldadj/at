@extends('layouts.app')

@section('content')
<script src="https://unpkg.com/lucide@latest"></script>

<div x-data="chatBot()" 
     class="flex h-screen bg-white text-slate-900 overflow-hidden font-sans antialiased"
     style="height: 100dvh;">
    
    <div x-show="sidebarOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         @click="sidebarOpen = false" 
         class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm lg:hidden"></div>

    <div :class="{
            'translate-x-0': sidebarOpen, 
            '-translate-x-full': !sidebarOpen,
            'lg:w-64': !sidebarCollapsed,
            'lg:w-0 lg:opacity-0 lg:invisible': sidebarCollapsed
         }" 
         class="fixed inset-y-0 left-0 z-50 bg-[#f9f9f9] border-r border-gray-200 flex flex-col transition-all duration-300 ease-in-out lg:relative lg:translate-x-0">
        
        <div class="p-4 flex items-center justify-between">
            <template x-if="!sidebarCollapsed">
                <span class="text-gray-500 font-medium text-xs uppercase tracking-wider">Historique</span>
            </template>
            <button @click="sidebarOpen = false" class="lg:hidden p-2 text-gray-500">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <div class="px-4 mb-4">
            <a href="{{ route('at.chat') }}" 
               class="flex items-center gap-3 bg-white hover:bg-gray-100 text-gray-800 border border-gray-200 py-2.5 px-4 rounded-lg transition-all shadow-sm">
                <i data-lucide="plus" class="w-4 h-4 text-gray-600"></i>
                <span class="text-sm font-medium">Nouveau chat</span>
            </a>
        </div>

        <div class="flex-1 overflow-y-auto p-2 space-y-1 custom-scrollbar">
            @foreach($conversations as $conv)
                <a href="{{ route('at.chat', $conv->id) }}" 
                   class="flex items-center gap-3 p-3 text-sm rounded-lg transition-all w-full truncate {{ isset($activeConversation) && $activeConversation->id == $conv->id ? 'bg-gray-200 text-gray-900' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                    <i data-lucide="message-square" class="w-4 h-4 shrink-0"></i>
                    <span class="truncate">{{ $conv->title }}</span>
                </a>
            @endforeach
        </div>
    </div>

    <div class="flex-1 flex flex-col min-w-0 bg-white relative">
        
        <header class="sticky top-0 h-14 border-b border-gray-100 flex items-center justify-between px-4 shrink-0 bg-white/95 backdrop-blur z-30">
            <div class="flex items-center gap-2">
                <button @click="toggleSidebar()" class="p-2 text-gray-500 hover:bg-gray-100 rounded-lg transition-colors">
                    <i data-lucide="panel-left" class="w-5 h-5"></i>
                </button>
                
                <h1 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                    AT Engine <span class="text-gray-400 font-normal"></span>
                </h1>
            </div>

            <div class="flex items-center gap-3">
                <div class="w-7 h-7 bg-purple-600 rounded-full flex items-center justify-center text-white font-bold text-[10px]">U</div>
            </div>
        </header>

        <main id="chat-container" class="flex-1 overflow-y-auto custom-scrollbar scroll-smooth">
            <div id="messages-wrapper" class="max-w-3xl mx-auto pt-8 pb-32 px-4 space-y-8">
                @if($activeConversation)
                    @foreach($activeConversation->messages as $msg)
                        <div class="flex gap-4 {{ $msg->role == 'user' ? 'justify-end' : 'justify-start' }} animate-fade-in">
                            @if($msg->role != 'user')
                                <div class="w-8 h-8 rounded-full bg-green-600 flex items-center justify-center shrink-0 border border-black/5 shadow-sm">
                                    <span class="text-white text-[10px] font-black">AT</span>
                                </div>
                            @endif
                            
                            <div class="max-w-[85%] {{ $msg->role == 'user' ? 'bg-[#f4f4f4] rounded-2xl px-5 py-3' : 'pt-1' }}">
                                <div class="prose prose-slate max-w-none text-[15px] leading-relaxed text-gray-800 whitespace-pre-line">
                                    {{ $msg->content }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="h-full flex flex-col items-center justify-center text-center pt-20">
                        <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <i data-lucide="bot" class="w-6 h-6 text-gray-400"></i>
                        </div>
                        <h2 class="text-xl font-semibold text-gray-800">Comment puis-je vous aider aujourd'hui ?</h2>
                    </div>
                @endif
            </div>
            <div id="scroll-anchor" class="h-1"></div>
        </main>

        <footer class="absolute bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-white via-white to-transparent">
            <form @submit.prevent="sendMessage" class="max-w-3xl mx-auto relative group">
                <div class="relative flex items-end bg-[#f4f4f4] border border-transparent focus-within:border-gray-300 rounded-[26px] transition-all p-1.5 shadow-sm">
                    <textarea 
                        x-model="newMessage" 
                        @keydown.enter.prevent="if(!loading && newMessage.trim()) sendMessage()"
                        rows="1"
                        style="max-height: 200px"
                        placeholder="Message AT..." 
                        class="w-full bg-transparent border-none focus:ring-0 py-3 px-4 text-[15px] text-gray-900 resize-none custom-scrollbar"
                        :disabled="loading"></textarea>
                    
                    <button type="submit" 
                            :disabled="loading || !newMessage.trim()" 
                            class="mb-1 mr-1 p-2 bg-black text-white rounded-full disabled:bg-gray-200 disabled:text-gray-400 transition-all shadow-sm">
                        <template x-if="!loading">
                            <i data-lucide="arrow-up" class="w-5 h-5"></i>
                        </template>
                        <template x-if="loading">
                            <div class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                        </template>
                    </button>
                </div>
                <p class="text-[11px] text-gray-400 text-center mt-3">
                    AT peut faire des erreurs. Vérifiez les informations importantes.
                </p>
            </form>
        </footer>
    </div>
</div>

<script>
    function chatBot() {
        return {
            sidebarOpen: false,
            sidebarCollapsed: false,
            newMessage: '',
            loading: false,
            conversationId: "{{ $activeConversation->id ?? '' }}",

            init() {
                lucide.createIcons();
                this.scrollToBottom('auto');
            },

            toggleSidebar() {
                // Sur mobile on ouvre l'overlay, sur desktop on réduit la barre
                if (window.innerWidth < 1024) {
                    this.sidebarOpen = !this.sidebarOpen;
                } else {
                    this.sidebarCollapsed = !this.sidebarCollapsed;
                }
                setTimeout(() => lucide.createIcons(), 100);
            },

            scrollToBottom(behavior = 'smooth') {
                setTimeout(() => {
                    const el = document.getElementById('scroll-anchor');
                    if (el) el.scrollIntoView({ behavior, block: 'end' });
                }, 100);
            },

            async sendMessage() {
                if (this.loading || !this.newMessage.trim()) return;
                
                const text = this.newMessage;
                this.newMessage = '';
                this.loading = true;
                
                this.appendMessage('user', text);
                this.scrollToBottom();

                try {
                    const res = await axios.post("{{ route('at.send') }}", {
                        message: text,
                        conversation_id: this.conversationId
                    });
                    
                    this.appendMessage('assistant', res.data.content);
                    if (!this.conversationId) this.conversationId = res.data.conversation_id;
                    
                } catch (e) {
                    const msg = e.response?.data?.message || "Erreur de connexion.";
                    this.appendMessage('assistant', "Erreur : " + msg);
                } finally {
                    this.loading = false;
                    this.scrollToBottom();
                    lucide.createIcons();
                }
            },

            appendMessage(role, content) {
                const wrapper = document.getElementById('messages-wrapper');
                const isUser = role === 'user';
                const div = document.createElement('div');
                div.className = `flex gap-4 ${isUser ? 'justify-end' : 'justify-start'} animate-fade-in`;
                
                const avatar = !isUser ? `
                    <div class="w-8 h-8 rounded-full bg-green-600 flex items-center justify-center shrink-0 border border-black/5 shadow-sm">
                        <span class="text-white text-[10px] font-black">AT</span>
                    </div>` : '';

                div.innerHTML = `
                    ${avatar}
                    <div class="max-w-[85%] ${isUser ? 'bg-[#f4f4f4] rounded-2xl px-5 py-3' : 'pt-1'}">
                        <div class="prose prose-slate max-w-none text-[15px] leading-relaxed text-gray-800 whitespace-pre-line">
                            ${this.escapeHTML(content)}
                        </div>
                    </div>`;
                wrapper.appendChild(div);
            },

            escapeHTML(str) {
                const p = document.createElement('p');
                p.textContent = str;
                return p.innerHTML;
            }
        }
    }
</script>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #d1d5db; }
    
    @keyframes fade-in { 
        from { opacity: 0; transform: translateY(8px); } 
        to { opacity: 1; transform: translateY(0); } 
    }
    .animate-fade-in { animation: fade-in 0.4s ease-out forwards; }

    /* Empêche le rebond du scroll sur iOS */
    body { fixed: inset-0; overflow: hidden; }
</style>
@endsection