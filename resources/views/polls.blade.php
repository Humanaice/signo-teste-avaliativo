<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enquetes</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <style>
        .poll-status {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            margin-left: 0.5rem;
        }
        .status-not-started {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .status-active {
            background-color: #dcfce7;
            color: #166534;
        }
        .status-ended {
            background-color: #fecaca;
            color: #991b1b;
        }
        @keyframes pulse-highlight {
            0% { background-color: transparent; }
            50% { background-color: rgba(255, 45, 32, 0.1); }
            100% { background-color: transparent; }
        }
        .vote-update {
            animation: pulse-highlight 1s ease-in-out;
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-50 text-black/80 dark:bg-black dark:text-white/70">
    <div class="relative min-h-screen">
        <div class="absolute -left-20 top-0 max-w-[877px] opacity-10 dark:opacity-5">
            <svg viewBox="0 0 415 415" xmlns="http://www.w3.org/2000/svg" class="h-full w-full fill-gray-700 dark:fill-white">
                <path d="M207.5 0C93.2 0 0 93.1 0 207.5 0 321.9 93.2 415 207.5 415 321.8 415 415 321.9 415 207.5 415 93.1 321.8 0 207.5 0zm0 340c-73.2 0-132.5-59.3-132.5-132.5S134.3 75 207.5 75 340 134.3 340 207.5 280.7 340 207.5 340z"/>
                <path d="M207.5 100c-59.4 0-107.5 48.1-107.5 107.5S148.1 315 207.5 315 315 266.9 315 207.5 266.9 100 207.5 100z"/>
            </svg>
        </div>

        <div class="relative w-full max-w-7xl mx-auto px-6 py-8">
            <header class="flex flex-col sm:flex-row items-center justify-between py-6">
                <h1 class="text-3xl font-bold text-black dark:text-white mb-4 sm:mb-0">Enquetes</h1>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <a href="{{ url('/') }}" class="rounded-md px-3 py-2 text-black/70 ring-1 ring-gray-300 transition hover:text-black/90 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white/70 dark:ring-zinc-800 dark:hover:text-white/90 dark:focus-visible:ring-white">
                            Voltar ao Início
                        </a>
                    </div>
                    <div class="flex items-center gap-2">
                        <a
                            href="{{ url('/api/polls/create') }}"
                            class="rounded-md px-4 py-2 bg-[#FF2D20] text-white hover:bg-[#FF2D20]/90 focus:outline-none focus:ring-2 focus:ring-[#FF2D20] focus:ring-offset-2 dark:focus:ring-offset-black transition"
                        >
                            Criar Enquete
                        </a>
                    </div>
                </div>
            </header>

            <main class="mt-6">
                <div id="polls" class="grid gap-6 md:grid-cols-2 xl:grid-cols-3"></div>
            </main>
        </div>
    </div>

    <script>
        const API_BASE_URL = '{{ url('/api') }}';

        // Função para verificar se a enquete está ativa
        function isPollActive(startDate, endDate) {
            const now = new Date();
            const start = new Date(formatDate(startDate));
            const end = new Date(formatDate(endDate));
            return now >= start && now <= end;
        }

        // Função para obter o status da enquete
        function getPollStatus(startDate, endDate) {
            const now = new Date();
            const start = new Date(formatDate(startDate));
            const end = new Date(formatDate(endDate));            
            
            if (now < start) {
                return { status: 'not-started', text: 'Não iniciada' };
            } else if (now > end) {
                return { status: 'ended', text: 'Finalizada' };
            } else {
                return { status: 'active', text: 'Em andamento' };
            }
        }

        // Função para formatar data
        function formatDate(dateString) {
            
            if (!dateString) {
                console.error('Data inválida (vazia ou nula):', dateString);
                return 'Data não informada';
            }
            
            // Converter para string se não for uma string
            if (typeof dateString !== 'string') {
                dateString = String(dateString);
            }
            
            let date;
            
            // Verificar se a string da data corresponde ao formato YYYYMMDD sem separadores
            if (/^\d{8}$/.test(dateString)) {
                const year = dateString.substring(0, 4);
                const month = dateString.substring(4, 6);
                const day = dateString.substring(6, 8);
                date = new Date(`${year}-${month}-${day}T00:00:00`);
            }
            // Verificar se é um timestamp numérico (Unix timestamp)
            else if (/^\d+$/.test(dateString)) {
                // Se for um número longo, assume que é um timestamp em milissegundos
                if (dateString.length > 10) {
                    date = new Date(parseInt(dateString));
                }
                // Caso contrário, assume que é um timestamp em segundos
                else {
                    date = new Date(parseInt(dateString) * 1000);
                }
            }
            // Verificar se a string da data corresponde ao formato YYYY-MM-DD
            else if (/^\d{4}-\d{2}-\d{2}$/.test(dateString)) {
                date = new Date(`${dateString}T00:00:00`);
            }
            // Se for uma string de data ISO completa
            else if (dateString.includes('T')) {
                date = new Date(dateString);
            }
            // Para outros formatos, tentar parsear diretamente
            else {
                date = new Date(dateString);
            }
            
            // Verificar se a data é válida
            if (isNaN(date.getTime())) {
                console.error('Data inválida após parsing:', dateString);
                return 'Data inválida';
            }
            
            const options = { 
                day: '2-digit', 
                month: '2-digit', 
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };
            
            const formattedDate = date.toLocaleDateString('pt-BR', options);            
            return formattedDate;
        }

        // Função para carregar todas as enquetes
        async function loadPolls() {
            try {
                const response = await axios.get(`${API_BASE_URL}/poll`);
                const polls = response.data.data;
                console.log('Enquetes carregadas:', polls);
                
                const pollsContainer = document.getElementById('polls');
                pollsContainer.innerHTML = '';

                if (!polls || polls.length === 0) {
                    pollsContainer.innerHTML = `
                        <div class="col-span-full flex justify-center">
                            <div class="py-10 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-200">Nenhuma enquete encontrada</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Não há enquetes cadastradas no sistema.</p>
                            </div>
                        </div>
                    `;
                    return;
                }

                polls.forEach(poll => {
                    const pollStatus = getPollStatus(poll.start_date, poll.end_date);
                    const isActive = pollStatus.status === 'active';                    
                    
                    const pollElement = document.createElement('div');
                    pollElement.classList.add('overflow-hidden', 'rounded-lg', 'bg-white', 'shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)]', 'ring-1', 'ring-white/[0.05]', 'transition', 'duration-300', 'dark:bg-zinc-900', 'dark:ring-zinc-800');
                    
                    pollElement.innerHTML = `
                        <div class="p-6">
                            <div class="flex flex-wrap items-center justify-between mb-4">
                                <h3 class="text-xl font-semibold text-black dark:text-white">${poll.title}</h3>
                                <span class="poll-status status-${pollStatus.status} mt-2 sm:mt-0">${pollStatus.text}</span>
                            </div>
                            
                            <div class="flex flex-row justify-between text-sm text-gray-500 dark:text-gray-400 mb-5">
                                <div class="">
                                    <div class="flex items-center mb-1">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span>Início: ${formatDate(poll.start_date)} a</span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span>Término: ${formatDate(poll.end_date)}</span>
                                    </div>
                                </div>
                                <div class="flex items-center mt-2">
                                    <a 
                                        href="polls/edit/${poll.id}"
                                        class="rounded-md px-3 py-1.5 text-white bg-blue-500 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-800 transition-colors"
                                    >
                                        Editar
                                    </a>
                                </div>
                            </div>

                            <div class="space-y-3" id="options-container-${poll.id}">
                                ${(poll.poll_options || []).map(option => `
                                    <div class="bg-gray-50 dark:bg-zinc-800/50 rounded-md p-3">
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                                            <span class="font-medium text-black/80 dark:text-white/90 mb-2 sm:mb-0">${option.option_text}</span>
                                            <div class="flex items-center">
                                                <span id="votes-${option.id}" class="text-sm text-gray-500 dark:text-gray-400 mr-3">
                                                    ${option.votes} votos
                                                </span>
                                                <button 
                                                    onclick="vote(${option.id}, event)" 
                                                    class="rounded-md px-3 py-1.5 text-white bg-[#FF2D20] hover:bg-[#FF2D20]/90 focus:outline-none focus:ring-2 focus:ring-[#FF2D20] focus:ring-offset-2 dark:focus:ring-offset-zinc-800 transition-colors ${!isActive ? 'opacity-50 cursor-not-allowed' : ''}"
                                                    ${!isActive ? 'disabled' : ''}
                                                >
                                                    Votar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    `;
                    pollsContainer.appendChild(pollElement);
                });
            } catch (error) {
                console.error('Erro ao carregar enquetes:', error);
                const pollsContainer = document.getElementById('polls');
                pollsContainer.innerHTML = `
                    <div class="col-span-full flex justify-center">
                        <div class="rounded-md bg-red-50 dark:bg-red-900/20 p-4">
                            <div class="flex">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                                </svg>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800 dark:text-red-200">Erro ao carregar enquetes</h3>
                                    <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                                        <p>Ocorreu um erro ao carregar as enquetes. Por favor, tente novamente mais tarde.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }
        }

        // Função para votar em uma opção
        async function vote(optionId, event) {
            // Impedir propagação de evento
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            try {
                const button = event.target;
                button.disabled = true;
                button.innerText = 'Votando...';
                button.classList.add('opacity-70');
                
                const response = await axios.post(`${API_BASE_URL}/poll-option/${optionId}/vote`);
                console.log('Voto registrado:', response.data);
                
                button.innerText = 'Voto registrado!';
                setTimeout(() => {
                    button.innerText = 'Votar';
                    button.disabled = false;
                    button.classList.remove('opacity-70');
                }, 2000);
            } catch (error) {
                console.error('Erro ao registrar voto:', error);
                alert('Erro ao registrar voto. Por favor, tente novamente.');
                const button = event.target;
                button.innerText = 'Votar';
                button.disabled = false;
                button.classList.remove('opacity-70');
            }
        }

        // Espere o Echo ser carregado antes de usá-lo
        function setupEcho() {
            console.log('Verificando Echo...');
            
            if (window.Echo) {
                console.log('Echo está disponível, configurando escuta de eventos...');
                
                // Echo está disponível
                window.Echo.channel('polls').listen('PollOptionVoted', (data) => {
                    console.log('Atualização recebida:', data);
                    const voteElement = document.getElementById(`votes-${data.option_id}`);
                    if (voteElement) {
                        voteElement.textContent = `${data.votes} votos`;
                        voteElement.classList.add('vote-update');
                        setTimeout(() => {
                            voteElement.classList.remove('vote-update');
                        }, 1000);
                    }
                });
            } else {
                // Echo ainda não está disponível, tente novamente em 100ms
                setTimeout(setupEcho, 100);
            }
        }

        // Alterna entre modo claro e escuro
        function setupDarkMode() {
            // Verifica a preferência do usuário
            const darkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (darkMode) {
                document.documentElement.classList.add('dark');
            }
        }

        // Inicie o processo quando a página carregar
        window.addEventListener('load', () => {
            setupDarkMode();
            loadPolls();
            setupEcho();
        });
    </script>
</body>
</html>