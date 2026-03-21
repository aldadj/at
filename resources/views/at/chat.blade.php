@extends('layouts.app')

@section('content')
<script src="https://unpkg.com/lucide@latest"></script>

<div x-data="chatBot()" 
     x-init="initTheme()"
     :class="{ 'dark': darkMode }"
     class="flex h-screen overflow-hidden font-sans antialiased bg-white dark:bg-[#212121] transition-colors duration-300"
     style="height: 100dvh;">
    
    <div x-show="sidebarOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         @click="sidebarOpen = false" 
         class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm lg:hidden"></div>

    <aside :class="{
            'translate-x-0': sidebarOpen, 
            '-translate-x-full': !sidebarOpen,
            'lg:w-64 lg:translate-x-0': !sidebarCollapsed,
            'lg:w-0 lg:-translate-x-full': sidebarCollapsed
         }" 
         class="fixed inset-y-0 left-0 z-50 w-72 bg-[#f9f9f9] dark:bg-[#171717] border-r border-gray-200 dark:border-white/10 flex flex-col transition-all duration-300 ease-in-out lg:relative">
        
        <div class="p-4 flex items-center justify-between">
            <span class="text-gray-500 dark:text-gray-400 font-medium text-xs uppercase tracking-wider overflow-hidden whitespace-nowrap">Historique</span>
            <button @click="sidebarOpen = false" class="lg:hidden p-2 text-gray-500 hover:bg-gray-200 dark:hover:bg-white/5 rounded-full">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <div class="px-4 mb-4">
            <a href="{{ route('at.chat') }}" 
               class="flex items-center justify-center lg:justify-start gap-3 bg-white dark:bg-[#212121] hover:bg-gray-100 dark:hover:bg-white/5 text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-white/10 py-2.5 px-4 rounded-xl transition-all shadow-sm">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span class="text-sm font-medium">Nouveau chat</span>
            </a>
        </div>

        <nav class="flex-1 overflow-y-auto px-2 space-y-1 custom-scrollbar">
            @foreach($conversations as $conv)
                <a href="{{ route('at.chat', $conv->id) }}" 
                   class="flex items-center gap-3 p-3 text-sm rounded-lg transition-all w-full truncate {{ isset($activeConversation) && $activeConversation->id == $conv->id ? 'bg-gray-200 dark:bg-white/10 text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5' }}">
                    <i data-lucide="message-square" class="w-4 h-4 shrink-0"></i>
                    <span class="truncate">{{ $conv->title }}</span>
                </a>
            @endforeach
        </nav>

        <div class="p-4 border-t border-gray-200 dark:border-white/10 bg-[#f9f9f9] dark:bg-[#171717]">
            <button @click="toggleDarkMode()" 
                    class="flex items-center gap-3 w-full p-3 rounded-xl text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-white/5 transition-colors border border-transparent hover:border-gray-300 dark:hover:border-white/10">
                <i :data-lucide="darkMode ? 'sun' : 'moon'" class="w-4 h-4 text-purple-500"></i>
                <span x-text="darkMode ? 'Mode Clair' : 'Mode Sombre'"></span>
            </button>
        </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0 bg-white dark:bg-[#212121] transition-colors duration-300 relative">
        
        <header class="h-14 border-b border-gray-100 dark:border-white/5 flex items-center justify-between px-4 bg-white/80 dark:bg-[#212121]/80 backdrop-blur-md sticky top-0 z-30">
            <div class="flex items-center gap-3">
                <button @click="toggleSidebarMenu()" class="p-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/10 rounded-lg transition-colors">
                    <i data-lucide="panel-left" class="w-5 h-5"></i>
                </button>
                <h1 class="text-sm font-bold text-gray-800 dark:text-gray-100 tracking-tight">
                    AT Engine <span class="text-purple-600 ml-1"></span>
                </h1>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-gradient-to-tr from-purple-600 to-blue-500 rounded-full flex items-center justify-center text-white font-bold text-xs shadow-lg shadow-purple-500/20">
                    U
                </div>
            </div>
        </header>

        <main id="chat-container" class="flex-1 overflow-y-auto custom-scrollbar pt-4">
            <div id="messages-wrapper" class="max-w-3xl mx-auto px-4 pb-44 space-y-8">
                @if($activeConversation)
                    @foreach($activeConversation->messages as $msg)
                        <div class="flex gap-4 {{ $msg->role == 'user' ? 'justify-end' : 'justify-start' }} animate-fade-in">
                            @if($msg->role != 'user')
                                <div class="w-8 h-8 rounded-lg bg-green-600 flex items-center justify-center shrink-0 shadow-sm mt-1">
                                    <span class="text-white text-[10px] font-black">AT</span>
                                </div>
                            @endif
                            <div class="max-w-[90%] md:max-w-[80%] {{ $msg->role == 'user' ? 'bg-[#f4f4f4] dark:bg-[#2f2f2f] rounded-2xl px-4 py-2.5 text-gray-800 dark:text-gray-200 shadow-sm' : 'text-gray-800 dark:text-gray-200' }}">
                                <div class="prose dark:prose-invert prose-sm sm:prose-base max-w-none leading-relaxed">
                                    {{ $msg->content }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
            <div id="scroll-anchor" class="h-2"></div>
        </main>

        <footer class="absolute bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-white dark:from-[#212121] via-white dark:via-[#212121] to-transparent z-20">
            <div class="max-w-3xl mx-auto">
                <form @submit.prevent="sendMessage" class="relative">
                    <div class="relative flex items-end bg-[#f4f4f4] dark:bg-[#2f2f2f] rounded-[24px] border border-transparent focus-within:border-gray-300 dark:focus-within:border-white/20 transition-all p-1.5 shadow-sm">
                        <textarea 
                            x-ref="chatInput"
                            x-model="newMessage" 
                            @keydown.enter.prevent="if(!loading && newMessage.trim()) sendMessage()"
                            @input="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                            rows="1"
                            placeholder="Écrivez votre message..." 
                            class="w-full bg-transparent border-none focus:ring-0 py-3 px-4 text-[15px] text-gray-900 dark:text-gray-100 resize-none max-h-40"
                            :disabled="loading"></textarea>
                        
                        <button type="submit" 
                                :disabled="loading || !newMessage.trim()" 
                                class="w-10 h-10 shrink-0 mb-0.5 mr-0.5 rounded-full flex items-center justify-center transition-all"
                                :class="newMessage.trim() && !loading ? 'bg-black dark:bg-white text-white dark:text-black' : 'bg-gray-300 dark:bg-white/5 text-gray-400 cursor-not-allowed'">
                            
                            <div x-show="!loading"><i data-lucide="arrow-up" class="w-5 h-5"></i></div>
                            <div x-show="loading" class="w-5 h-5 border-2 border-current border-t-transparent rounded-full animate-spin"></div>
                        </button>
                    </div>
                </form>
                <p class="text-[10px] text-gray-400 dark:text-gray-500 text-center mt-3 font-medium">
                    ALDADJ TECH — Intelligence Artificielle au Burkina
                </p>
            </div>
        </footer>
    </div>
</div>

<script>
    function chatBot() {
        return {
            sidebarOpen: false,
            sidebarCollapsed: false,
            darkMode: false,
            newMessage: '',
            loading: false,
            conversationId: "{{ $activeConversation->id ?? '' }}",

            initTheme() {
                // Détection intelligente du thème
                const savedTheme = localStorage.getItem('at-theme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                
                if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
                    this.darkMode = true;
                }
                
                this.$nextTick(() => lucide.createIcons());
                this.scrollToBottom('auto');
            },

            toggleDarkMode() {
                this.darkMode = !this.darkMode;
                localStorage.setItem('at-theme', this.darkMode ? 'dark' : 'light');
                this.$nextTick(() => lucide.createIcons());
            },

            toggleSidebarMenu() {
                if (window.innerWidth < 1024) {
                    this.sidebarOpen = !this.sidebarOpen;
                } else {
                    this.sidebarCollapsed = !this.sidebarCollapsed;
                }
                // Attendre la fin de l'animation pour rafraîchir les icônes si besoin
                setTimeout(() => lucide.createIcons(), 310);
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
                this.$refs.chatInput.style.height = 'auto';
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
                    this.appendMessage('assistant', "Erreur technique. Vérifiez votre connexion.");
                } finally {
                    this.loading = false;
                    this.scrollToBottom();
                    this.$nextTick(() => lucide.createIcons());
                }
            },

            appendMessage(role, content) {
                const wrapper = document.getElementById('messages-wrapper');
                const isUser = role === 'user';
                const div = document.createElement('div');
                div.className = `flex gap-4 ${isUser ? 'justify-end' : 'justify-start'} animate-fade-in`;
                
                const avatar = !isUser ? `
                    <div class="w-8 h-8 rounded-lg bg-green-600 flex items-center justify-center shrink-0 shadow-sm mt-1">
                        <span class="text-white text-[10px] font-black">AT</span>
                    </div>` : '';

                div.innerHTML = `
                    ${avatar}
                    <div class="max-w-[90%] md:max-w-[80%] ${isUser ? 'bg-[#f4f4f4] dark:bg-[#2f2f2f] rounded-2xl px-4 py-2.5 text-gray-800 dark:text-gray-200 shadow-sm' : 'text-gray-800 dark:text-gray-200'}">
                        <div class="prose dark:prose-invert prose-sm sm:prose-base max-w-none leading-relaxed whitespace-pre-line">
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
    /* Scrollbar invisible ou fine selon le thème */
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #4b5563; }

    @keyframes fade-in { 
        from { opacity: 0; transform: translateY(10px); } 
        to { opacity: 1; transform: translateY(0); } 
    }
    .animate-fade-in { animation: fade-in 0.3s ease-out forwards; }

    /* Nettoyage des focus sur mobile */
    textarea:focus { box-shadow: none !important; outline: none !important; }
    button { -webkit-tap-highlight-color: transparent; }
</style>
@endsection