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