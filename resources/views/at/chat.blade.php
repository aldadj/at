@extends('layouts.app')

@section('content')
<script src="https://unpkg.com/lucide@latest"></script>

<div x-data="chatBot()" 
     class="flex h-screen bg-[#0f0f0f] text-gray-200 overflow-hidden font-sans">
    
    <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm lg:hidden"></div>

    <div :class="{
            'translate-x-0': sidebarOpen, 
            '-translate-x-full': !sidebarOpen,
            'lg:w-72': !sidebarCollapsed,
            'lg:w-20': sidebarCollapsed
         }" 
         class="fixed inset-y-0 left-0 z-50 bg-[#161616] border-r border-white/5 flex flex-col transition-all duration-300 ease-in-out lg:relative lg:translate-x-0">
        
        <div class="p-4 border-b border-white/5 flex items-center" :class="sidebarCollapsed ? 'justify-center' : 'justify-between'">
            <template x-if="!sidebarCollapsed">
                <span class="text-[#4CAF50] font-black tracking-widest text-xs uppercase">Menu</span>
            </template>
            <button @click="sidebarCollapsed = !sidebarCollapsed" class="hidden lg:block p-2 hover:bg-white/5 rounded-lg text-gray-400 transition-colors">
                <i :data-lucide="sidebarCollapsed ? 'panel-left-open' : 'panel-left-close'" class="w-5 h-5"></i>
            </button>
        </div>

        <div class="p-4">
            <a href="{{ route('at.chat') }}" 
               class="group flex items-center gap-3 bg-[#4CAF50] hover:bg-white text-white hover:text-[#121212] font-bold py-3 rounded-xl transition-all shadow-lg"
               :class="sidebarCollapsed ? 'justify-center px-0' : 'px-4'">
                <i data-lucide="plus" class="w-5 h-5"></i>
                <span x-show="!sidebarCollapsed" class="uppercase text-xs tracking-widest overflow-hidden whitespace-nowrap">Nouveau</span>
            </a>
        </div>

        <div class="flex-1 overflow-y-auto p-3 space-y-2 custom-scrollbar">
            @foreach($conversations as $conv)
                <div class="group relative flex items-center">
                    <a href="{{ route('at.chat', $conv->id) }}" 
                       class="flex items-center gap-3 p-3 text-sm rounded-xl transition-all w-full {{ isset($activeConversation) && $activeConversation->id == $conv->id ? 'bg-[#4CAF50]/10 text-[#4CAF50] border border-[#4CAF50]/20' : 'text-gray-400 hover:bg-white/5' }}"
                       :class="sidebarCollapsed ? 'justify-center' : ''"
                       title="{{ $conv->title }}">
                        <i data-lucide="message-square" class="w-4 h-4 shrink-0"></i>
                        <span x-show="!sidebarCollapsed" class="truncate">{{ $conv->title }}</span>
                    </a>
                </div>
            @endforeach
        </div>
    </div>

    <div class="flex-1 flex flex-col min-w-0 bg-[#0f0f0f]">
        <header class="h-16 border-b border-white/5 flex items-center justify-between px-6">
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = true" class="lg:hidden p-2 text-gray-400 hover:bg-white/5 rounded-lg">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <h1 class="text-lg font-black tracking-tighter uppercase italic text-white">AT <span class="text-[#4CAF50]">Engine</span></h1>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest hidden sm:inline">Connecté</span>
                <div class="w-2 h-2 bg-[#4CAF50] rounded-full animate-pulse"></div>
            </div>
        </header>

        <main id="chat-container" class="flex-1 overflow-y-auto p-4 lg:p-8 space-y-8 custom-scrollbar scroll-smooth">
            <div id="messages-wrapper" class="space-y-8 max-w-4xl mx-auto">
                @if($activeConversation)
                    @foreach($activeConversation->messages as $msg)
                        <div class="flex {{ $msg->role == 'user' ? 'justify-end' : 'justify-start' }} animate-fade-in">
                            <div class="max-w-[85%] lg:max-w-2xl px-5 py-3 rounded-2xl {{ $msg->role == 'user' ? 'bg-[#4CAF50] text-white rounded-tr-none' : 'bg-[#1a1a1a] text-gray-300 border border-white/5 rounded-tl-none' }} shadow-xl">
                                <p class="text-sm leading-relaxed whitespace-pre-line">{{ $msg->content }}</p>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
            <div id="scroll-anchor" class="h-10"></div>
        </main>

        <footer class="p-4 lg:p-6 border-t border-white/5 bg-[#0f0f0f]">
            <form @submit.prevent="sendMessage" class="max-w-4xl mx-auto flex gap-4 items-center">
                <div class="relative flex-1">
                    <input type="text" x-model="newMessage" :disabled="loading" placeholder="Écrivez à AT..." 
                           class="w-full bg-[#1a1a1a] border border-white/10 rounded-2xl p-4 pr-14 text-sm text-white focus:outline-none focus:border-[#4CAF50]/50 transition-all shadow-inner">
                    <button type="submit" :disabled="loading || !newMessage.trim()" 
                            class="absolute right-2 top-1/2 -translate-y-1/2 p-2 bg-[#4CAF50] text-white rounded-xl active:scale-95 disabled:bg-gray-800 transition-all">
                        <template x-if="!loading"><i data-lucide="send" class="w-5 h-5"></i></template>
                        <template x-if="loading"><div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div></template>
                    </button>
                </div>
            </form>
        </footer>
    </div>
</div>

<script>
    function chatBot() {
        return {
            sidebarOpen: false,
            sidebarCollapsed: false, // État de réduction
            newMessage: '',
            loading: false,
            conversationId: "{{ $activeConversation->id ?? '' }}",

            init() {
                lucide.createIcons();
                this.scrollToBottom('auto');
            },

            scrollToBottom(behavior = 'smooth') {
                setTimeout(() => {
                    const el = document.getElementById('scroll-anchor');
                    if (el) el.scrollIntoView({ behavior });
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
                    this.appendMessage('assistant', "Erreur de connexion.");
                } finally {
                    this.loading = false;
                    this.scrollToBottom();
                    lucide.createIcons();
                }
            },

            appendMessage(role, content) {
                const wrapper = document.getElementById('messages-wrapper');
                const isUser = role === 'user';
                const html = `
                    <div class="flex ${isUser ? 'justify-end' : 'justify-start'} animate-fade-in">
                        <div class="max-w-[85%] lg:max-w-2xl px-5 py-3 rounded-2xl ${isUser ? 'bg-[#4CAF50] text-white rounded-tr-none' : 'bg-[#1a1a1a] text-gray-300 border border-white/5 rounded-tl-none'} shadow-lg">
                            <p class="text-sm leading-relaxed whitespace-pre-line">${content}</p>
                        </div>
                    </div>`;
                wrapper.insertAdjacentHTML('beforeend', html);
            }
        }
    }
</script>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #4CAF50; border-radius: 10px; }
    @keyframes fade-in { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade-in { animation: fade-in 0.3s ease-out forwards; }
</style>
@endsection