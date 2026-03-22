@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/chat.css') }}">
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://unpkg.com/lucide@latest"></script>
<script src="{{ asset('js/chat.js') }}?v={{ time() }}"></script>
<script>
   // On initialise Alpine avec les données dynamiques de Laravel
    document.addEventListener('alpine:init', () => {
        // Optionnel : si tu veux que chatBot soit globalement accessible
    });
</script>


<div x-data="chatBot({ 
    conversationId: '{{ $activeConversation->id ?? '' }}', 
    sendRoute: '{{ route('at.send') }}' 
 })" 
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
         class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm lg:hidden">
    </div>

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
        
     @include('partials._header')

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
        @include('partials._footer')
    </div>
</div>

@endsection