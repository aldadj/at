console.log("Le script chat.js est bien chargé !");

function chatBot(config) {
    return {
        sidebarOpen: false,
        sidebarCollapsed: false,
        darkMode: false,
        newMessage: '',
        loading: false,
        // On récupère l'ID depuis la configuration passée au démarrage
        conversationId: config.conversationId,

        initTheme() {
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
                // On utilise l'URL de la route passée en paramètre
                const res = await axios.post(config.sendRoute, {
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