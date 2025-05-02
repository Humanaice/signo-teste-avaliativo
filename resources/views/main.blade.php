<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Enquetes - Sistema de Votação</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .poll-status {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
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
        .loader {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(255, 45, 32, 0.2);
            border-radius: 50%;
            border-top-color: #FF2D20;
            animation: spin 1s ease-in-out infinite;
            margin: 2rem auto;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-50 text-black/80 dark:bg-black dark:text-white/70">
    <div class="relative min-h-screen">
        <!-- Background Circle -->
        <div class="absolute -left-20 top-0 max-w-[877px] opacity-10 dark:opacity-5">
            <svg viewBox="0 0 415 415" xmlns="http://www.w3.org/2000/svg" class="h-full w-full fill-gray-700 dark:fill-white">
                <path d="M207.5 0C93.2 0 0 93.1 0 207.5 0 321.9 93.2 415 207.5 415 321.8 415 415 321.9 415 207.5 415 93.1 321.8 0 207.5 0zm0 340c-73.2 0-132.5-59.3-132.5-132.5S134.3 75 207.5 75 340 134.3 340 207.5 280.7 340 207.5 340z"/>
                <path d="M207.5 100c-59.4 0-107.5 48.1-107.5 107.5S148.1 315 207.5 315 315 266.9 315 207.5 266.9 100 207.5 100z"/>
            </svg>
        </div>

        <div class="relative w-full max-w-7xl mx-auto px-6 py-8">
            <!-- Header -->
            <header class="flex flex-col sm:flex-row items-center justify-between py-6">
                <h1 class="text-3xl font-bold text-black dark:text-white mb-4 sm:mb-0">Enquetes</h1>
                <div class="flex items-center gap-4">
                    <a
                        href="{{ url('/create') }}"
                        class="rounded-md px-4 py-2 bg-[#FF2D20] text-white hover:bg-[#FF2D20]/90 focus:outline-none focus:ring-2 focus:ring-[#FF2D20] focus:ring-offset-2 dark:focus:ring-offset-black transition"
                        aria-label="Criar nova enquete"
                    >
                        <span class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                            </svg>
                            Criar Enquete
                        </span>
                    </a>
                </div>
            </header>

            <!-- Main Content -->
            <main class="mt-6">
                <!-- Loading State -->
                <div id="loading-indicator" class="py-10 text-center">
                    <div class="loader"></div>
                    <p class="mt-4 text-gray-500 dark:text-gray-400">Carregando enquetes...</p>
                </div>
                
                <!-- Error Message (hidden by default) -->
                <div id="error-message" class="hidden col-span-full flex justify-center">
                    <div class="rounded-md bg-red-50 dark:bg-red-900/20 p-4 max-w-2xl w-full">
                        <div class="flex">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                            </svg>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800 dark:text-red-200">Erro ao carregar enquetes</h3>
                                <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                                    <p id="error-details">Ocorreu um erro ao carregar as enquetes. Por favor, tente novamente mais tarde.</p>
                                </div>
                                <div class="mt-4">
                                    <button type="button" id="retry-button" class="rounded bg-red-50 dark:bg-red-900/30 px-2 py-1.5 text-sm font-medium text-red-800 dark:text-red-200 hover:bg-red-100 dark:hover:bg-red-900/40 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2 dark:focus:ring-offset-black">
                                        Tentar novamente
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Empty State (hidden by default) -->
                <div id="empty-state" class="hidden col-span-full flex justify-center">
                    <div class="py-10 text-center max-w-md">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-200">Nenhuma enquete encontrada</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Não há enquetes cadastradas no sistema.</p>
                        <div class="mt-6">
                            <a href="{{ url('/create') }}" class="inline-flex items-center rounded-md bg-[#FF2D20] px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#FF2D20]/90 focus:outline-none focus:ring-2 focus:ring-[#FF2D20] focus:ring-offset-2 dark:focus:ring-offset-black">
                                <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                                </svg>
                                Criar primeira enquete
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Polls Grid -->
                <div id="polls" class="hidden grid gap-6 md:grid-cols-2 xl:grid-cols-3"></div>
            </main>
        </div>
    </div>

    <!-- Success Toast -->
    <div id="success-toast" class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg transform translate-y-10 opacity-0 transition-all duration-300 z-50">
        <div class="flex items-center">
            <svg class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span id="toast-message">Voto registrado com sucesso!</span>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Configurações da aplicação
            const config = {
                apiBaseUrl: '{{  url('/api') }}',
                csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                dateFormat: { 
                    day: '2-digit', 
                    month: '2-digit', 
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                },
                toastDuration: 3000,
                retryDelay: 3000,
                animationDuration: 300
            };

            // Estado da aplicação
            const state = {
                polls: [],
                loading: true,
                error: null,
                votingInProgress: new Set()
            };

            // Elementos do DOM
            const elements = {
                pollsContainer: document.getElementById('polls'),
                loadingIndicator: document.getElementById('loading-indicator'),
                errorMessage: document.getElementById('error-message'),
                errorDetails: document.getElementById('error-details'),
                retryButton: document.getElementById('retry-button'),
                emptyState: document.getElementById('empty-state'),
                successToast: document.getElementById('success-toast'),
                toastMessage: document.getElementById('toast-message')
            };

            // Inicialização
            init();

            function init() {
                setupEventListeners();
                setupDarkMode();
                loadPolls();
                setupEcho();
            }

            // Configuração de listeners de eventos
            function setupEventListeners() {
                elements.retryButton.addEventListener('click', loadPolls);
            }

            // Funções de UI
            function showLoading(show = true) {
                elements.loadingIndicator.style.display = show ? 'block' : 'none';
                state.loading = show;
            }

            function showError(show = true, message = null) {
                elements.errorMessage.classList.toggle('hidden', !show);
                if (message && show) {
                    elements.errorDetails.textContent = message;
                }
            }

            function showEmptyState(show = true) {
                elements.emptyState.classList.toggle('hidden', !show);
            }

            function showPolls(show = true) {
                elements.pollsContainer.classList.toggle('hidden', !show);
                if (show) {
                    elements.pollsContainer.classList.add('fade-in');
                }
            }

            function showToast(message, type = 'success') {
                elements.toastMessage.textContent = message;
                elements.successToast.style.transform = 'translateY(0)';
                elements.successToast.style.opacity = '1';
                
                if (type === 'error') {
                    elements.successToast.classList.remove('bg-green-500');
                    elements.successToast.classList.add('bg-red-500');
                } else {
                    elements.successToast.classList.add('bg-green-500');
                    elements.successToast.classList.remove('bg-red-500');
                }

                setTimeout(() => {
                    elements.successToast.style.transform = 'translateY(10px)';
                    elements.successToast.style.opacity = '0';
                }, config.toastDuration);
            }

            // Funções de utilidade
            function formatDate(dateString) {
                if (!dateString) return 'Data não informada';
                
                try {
                    // Padroniza: converter tudo para objeto Date
                    let date;
                    
                    if (typeof dateString === 'object' && dateString instanceof Date) {
                        date = dateString;
                    } else if (typeof dateString === 'number') {
                        // Timestamp numérico
                        date = new Date(dateString > 9999999999 ? dateString : dateString * 1000);
                    } else {
                        // String de data
                        dateString = String(dateString).trim();
                        
                        // YYYYMMDD sem separadores
                        if (/^\d{8}$/.test(dateString)) {
                            const year = dateString.substring(0, 4);
                            const month = dateString.substring(4, 6);
                            const day = dateString.substring(6, 8);
                            date = new Date(`${year}-${month}-${day}T00:00:00`);
                        } 
                        // ISO date string (including partial ISO string with just date)
                        else {
                            date = new Date(dateString);
                        }
                    }
                    
                    // Verificar se a data é válida
                    if (isNaN(date.getTime())) {
                        console.warn('Data inválida após parsing:', dateString);
                        return 'Data inválida';
                    }
                    
                    return date.toLocaleDateString('pt-BR', config.dateFormat);
                } catch (error) {
                    console.error('Erro ao formatar data:', error, dateString);
                    return 'Data inválida';
                }
            }

            function getPollStatus(startDate, endDate) {
                const now = new Date();
                let start, end;
                
                try {
                    start = new Date(startDate);
                    end = new Date(endDate);
                    
                    if (isNaN(start.getTime()) || isNaN(end.getTime())) {
                        return { status: 'error', text: 'Datas inválidas' };
                    }
                    
                    if (now < start) {
                        return { status: 'not-started', text: 'Não iniciada' };
                    } else if (now > end) {
                        return { status: 'ended', text: 'Finalizada' };
                    } else {
                        return { status: 'active', text: 'Em andamento' };
                    }
                } catch (error) {
                    console.error('Erro ao calcular status da enquete:', error);
                    return { status: 'error', text: 'Erro no cálculo' };
                }
            }

            // Funções de data
            async function loadPolls() {
                showLoading(true);
                showError(false);
                showEmptyState(false);
                showPolls(false);
                
                try {
                    const response = await axios.get(`${config.apiBaseUrl}/poll`);
                    const polls = response.data.data;
                    
                    state.polls = polls;
                    renderPolls();
                    
                    showLoading(false);
                    
                    if (!polls || polls.length === 0) {
                        showEmptyState(true);
                    } else {
                        showPolls(true);
                    }
                } catch (error) {
                    console.error('Erro ao carregar enquetes:', error);
                    showLoading(false);
                    
                    let errorMessage = 'Ocorreu um erro ao carregar as enquetes. Por favor, tente novamente mais tarde.';
                    if (error.response) {
                        if (error.response.status === 404) {
                            errorMessage = 'API não encontrada. Verifique se o servidor está em execução.';
                        } else if (error.response.data && error.response.data.message) {
                            errorMessage = `Erro: ${error.response.data.message}`;
                        }
                    } else if (error.request) {
                        errorMessage = 'Não foi possível conectar ao servidor. Verifique sua conexão com a internet.';
                    }
                    
                    showError(true, errorMessage);
                }
            }

            function renderPolls() {
                const pollsContainer = elements.pollsContainer;
                pollsContainer.innerHTML = '';
                
                state.polls.forEach(poll => {
                    const pollElement = createPollElement(poll);
                    pollsContainer.appendChild(pollElement);
                });
            }

            function createPollElement(poll) {
                const pollStatus = getPollStatus(poll.start_date, poll.end_date);
                const isActive = pollStatus.status === 'active';
                
                const pollElement = document.createElement('div');
                pollElement.classList.add(
                    'overflow-hidden', 'rounded-lg', 'bg-white', 
                    'shadow-[0px_14px_34px_0px_rgba(0,0,0,0.08)]', 
                    'ring-1', 'ring-white/[0.05]', 'transition', 
                    'duration-300', 'dark:bg-zinc-900', 'dark:ring-zinc-800'
                );
                
                pollElement.innerHTML = `
                    <div class="p-6">
                        <div class="flex flex-wrap items-center justify-between mb-4">
                            <h3 class="text-xl font-semibold text-black dark:text-white">${escapeHtml(poll.title)}</h3>
                            <span class="poll-status status-${pollStatus.status} mt-2 sm:mt-0">${pollStatus.text}</span>
                        </div>
                        
                        <div class="flex flex-row justify-between text-sm text-gray-500 dark:text-gray-400 mb-5">
                            <div class="">
                                <div class="flex items-center mb-1">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span>Início: ${formatDate(poll.start_date)}</span>
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
                                    href="/edit/${poll.id}"
                                    class="rounded-md px-3 py-1.5 text-white bg-blue-500 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-800 transition-colors"
                                    aria-label="Editar enquete ${poll.title}"
                                >
                                    Editar
                                </a>
                            </div>
                        </div>

                        <div class="space-y-3" id="options-container-${poll.id}">
                            ${(poll.poll_options || []).map(option => createOptionElement(option, isActive)).join('')}
                        </div>
                    </div>
                `;
                
                // Adicionar listeners de eventos
                const voteButtons = pollElement.querySelectorAll('.vote-button');
                voteButtons.forEach(button => {
                    const optionId = button.getAttribute('data-option-id');
                    button.addEventListener('click', (event) => handleVote(optionId, event));
                });
                
                return pollElement;
            }

            function createOptionElement(option, isActive) {
                return `
                    <div class="bg-gray-50 dark:bg-zinc-800/50 rounded-md p-3">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                            <span class="font-medium text-black/80 dark:text-white/90 mb-2 sm:mb-0">${escapeHtml(option.option_text)}</span>
                            <div class="flex items-center">
                                <span id="votes-${option.id}" class="text-sm text-gray-500 dark:text-gray-400 mr-3">
                                    ${option.votes} ${option.votes === 1 ? 'voto' : 'votos'}
                                </span>
                                <button 
                                    class="vote-button rounded-md px-3 py-1.5 text-white bg-[#FF2D20] hover:bg-[#FF2D20]/90 focus:outline-none focus:ring-2 focus:ring-[#FF2D20] focus:ring-offset-2 dark:focus:ring-offset-zinc-800 transition-colors ${!isActive ? 'opacity-50 cursor-not-allowed' : ''}"
                                    ${!isActive ? 'disabled' : ''}
                                    data-option-id="${option.id}"
                                    aria-label="Votar em ${option.option_text}"
                                >
                                    Votar
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            }

            async function handleVote(optionId, event) {
                // Evitar cliques duplicados
                if (state.votingInProgress.has(optionId)) return;
                
                const button = event.target;
                
                try {
                    state.votingInProgress.add(optionId);
                    button.disabled = true;
                    button.innerText = 'Votando...';
                    button.classList.add('opacity-70');
                    
                    const response = await axios.post(
                        `${config.apiBaseUrl}/poll-option/${optionId}/vote`,
                        {}, // Dados vazios, apenas o ID na URL
                        {
                            headers: {
                                'X-CSRF-TOKEN': config.csrfToken
                            }
                        }
                    );
                    
                    button.innerText = 'Votado';
                    
                    // Atualiza o contador de votos imediatamente (não espera pelo Echo)
                    const voteElement = document.getElementById(`votes-${optionId}`);
                    if (voteElement && response.data && response.data.votes !== undefined) {
                        const votes = response.data.votes;
                        voteElement.textContent = `${votes} ${votes === 1 ? 'voto' : 'votos'}`;
                        voteElement.classList.add('vote-update');
                        setTimeout(() => {
                            voteElement.classList.remove('vote-update');
                        }, 1000);
                    }
                    
                    // Mostra mensagem de sucesso
                    showToast('Voto registrado com sucesso!');
                    
                    // Reabilita o botão após um tempo
                    setTimeout(() => {
                        button.innerText = 'Votar';
                        button.disabled = false;
                        button.classList.remove('opacity-70');
                        state.votingInProgress.delete(optionId);
                    }, 2000);
                    
                } catch (error) {
                    console.error('Erro ao registrar voto:', error);
                    
                    let errorMessage = 'Erro ao registrar voto. Por favor, tente novamente.';
                    if (error.response && error.response.data && error.response.data.message) {
                        errorMessage = error.response.data.message;
                    }
                    
                    showToast(errorMessage, 'error');
                    
                    button.innerText = 'Votar';
                    button.disabled = false;
                    button.classList.remove('opacity-70');
                    state.votingInProgress.delete(optionId);
                }
            }

            // Configuração do Echo para atualizações em tempo real
            function setupEcho() {
                if (window.Echo) {
                    configureEchoListeners();
                } else {
                    // Tentar novamente em 100ms se o Echo não estiver disponível
                    setTimeout(setupEcho, 100);
                }
            }

            function configureEchoListeners() {
                window.Echo.channel('polls')
                    .listen('PollOptionVoted', handleEchoVoteUpdate)
                    .listen('PollCreated', () => {
                        loadPolls(); // Recarrega a lista quando uma nova enquete é criada
                    })
                    .listen('PollUpdated', () => {
                        loadPolls(); // Recarrega a lista quando uma enquete é atualizada
                    })
                    .listen('PollDeleted', () => {
                        loadPolls(); // Recarrega a lista quando uma enquete é deletada
                    });
            }

            function handleEchoVoteUpdate(data) {
                const voteElement = document.getElementById(`votes-${data.option_id}`);
                if (voteElement) {
                    const votes = data.votes;
                    voteElement.textContent = `${votes} ${votes === 1 ? 'voto' : 'votos'}`;
                    voteElement.classList.add('vote-update');
                    setTimeout(() => {
                        voteElement.classList.remove('vote-update');
                    }, 1000);
                }
            }

            // Configuração do tema escuro
            function setupDarkMode() {
                // Verifica a preferência do usuário
                const darkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (darkMode) {
                    document.documentElement.classList.add('dark');
                }
                
                // Observa mudanças na preferência do sistema
                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
                    if (e.matches) {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                });
            }

            // Funções de segurança
            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
        });
    </script>
</body>
</html>