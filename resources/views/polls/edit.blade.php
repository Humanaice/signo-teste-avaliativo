<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Enquete</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50 text-black dark:bg-black dark:text-white">
    <div class="max-w-3xl mx-auto px-6 py-10">
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-3xl font-bold">Editar Enquete</h1>
            <a href="/" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition">
                Voltar para Lista
            </a>
        </div>
        
        <form id="editPollForm" class="space-y-6">
            @csrf
            <input type="hidden" name="_method" value="PUT">

            <div>
                <label for="title" class="block text-lg font-medium">Título da Enquete</label>
                <input id="title" name="title" type="text" required
                       class="mt-1 block w-full p-2 text-black rounded-md border-gray-300 shadow-sm focus:border-[#FF2D20] focus:ring-[#FF2D20]">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="start_date" class="block text-lg font-medium">Data de Início</label>
                    <input id="start_date" name="start_date" type="date" required
                           class="mt-1 block w-full p-2 text-black rounded-md border-gray-300 shadow-sm focus:border-[#FF2D20] focus:ring-[#FF2D20]">
                </div>
                <div>
                    <label for="end_date" class="block text-lg font-medium">Data de Término</label>
                    <input id="end_date" name="end_date" type="date" required
                           class="mt-1 block w-full p-2 text-black rounded-md border-gray-300 shadow-sm focus:border-[#FF2D20] focus:ring-[#FF2D20]">
                </div>
            </div>

            <div id="optionsContainer" class="space-y-4">
                <!-- Options will be dynamically loaded here -->
            </div>

            <div>
                <button type="button" id="addOption" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition">
                    Adicionar Opção
                </button>
            </div>

            <div class="flex justify-between items-center">
                <button type="submit" id="updateButton" class="px-6 py-2 bg-[#FF2D20] text-white rounded-md hover:bg-[#FF2D20]/90 transition">
                    Atualizar Enquete
                </button>
                <button type="button" id="deletePoll" class="px-6 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition">
                    Deletar Enquete
                </button>
            </div>
        </form>
        
        <!-- Modal de confirmação para deleção -->
        <div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg max-w-md w-full">
                <h3 class="text-xl font-bold mb-4 text-red-500">Confirmar Exclusão</h3>
                <p class="mb-6">Tem certeza que deseja excluir esta enquete? Esta ação não pode ser desfeita.</p>
                <div class="flex justify-end space-x-4">
                    <button id="cancelDelete" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 transition">
                        Cancelar
                    </button>
                    <button id="confirmDelete" class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition">
                        Excluir Enquete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            // Estado global da aplicação
            const state = {
                pollId: null,
                apiUrl: null,
                poll: null,
                loading: false,
                deletedOptionIds: [],
                modifiedOptions: new Map(), // Rastreia opções modificadas para evitar requisições desnecessárias
                originalOptions: [] // Armazena opções originais para comparação
            };
            
            // Elementos do DOM frequentemente acessados
            const elements = {
                title: document.getElementById('title'),
                startDate: document.getElementById('start_date'),
                endDate: document.getElementById('end_date'),
                optionsContainer: document.getElementById('optionsContainer'),
                form: document.getElementById('editPollForm'),
                updateButton: document.getElementById('updateButton'),
                deleteButton: document.getElementById('deletePoll'),
                deleteModal: document.getElementById('deleteModal'),
                confirmDelete: document.getElementById('confirmDelete'),
                cancelDelete: document.getElementById('cancelDelete'),
                addOptionButton: document.getElementById('addOption')
            };
            
            // Inicializa a aplicação
            function init() {
                // Extrai o ID da enquete da URL
                const urlMatches = window.location.pathname.match(/\/edit\/(\d+)/);
                if (!urlMatches || !urlMatches[1]) {
                    showError('ID de enquete inválido.');
                    window.location.href = '/';
                    return false;
                }
                
                state.pollId = urlMatches[1];
                state.apiUrl = `/api/poll/${state.pollId}`;
                
                // Adiciona listeners de eventos
                addEventListeners();
                
                // Carrega dados da enquete
                return loadPollData();
            }
            
            // Adiciona todos os event listeners
            function addEventListeners() {
                elements.addOptionButton.addEventListener('click', handleAddOption);
                elements.form.addEventListener('submit', handleFormSubmit);
                elements.deleteButton.addEventListener('click', () => toggleModal(true));
                elements.cancelDelete.addEventListener('click', () => toggleModal(false));
                elements.confirmDelete.addEventListener('click', handleDeletePoll);
            }
            
            // Controla a exibição do modal de confirmação
            function toggleModal(show) {
                elements.deleteModal.classList.toggle('hidden', !show);
            }
            
            // Funções de formatação e utilidades
            const formatDate = (dateString) => {
                if (!dateString) return '';
                const date = new Date(dateString);
                return date.toISOString().split('T')[0]; // Retorna YYYY-MM-DD
            };
            
            // Função para exibir mensagens de erro
            function showToast(message, type = 'success', redirect = false) {
                const toast = document.getElementById(type === 'success' ? 'success-toast' : 'error-toast');
                const messageElement = document.getElementById(type === 'success' ? 'toast-message' : 'error-message');
                
                // Define a mensagem e mostra o toast
                messageElement.textContent = message;
                toast.style.transform = 'translateY(0)';
                toast.style.opacity = '1';
                
                return new Promise(resolve => {
                    // Após 3 segundos, esconde o toast e resolve a promise
                    setTimeout(() => {
                        toast.style.transform = 'translateY(10px)';
                        toast.style.opacity = '0';
                        
                        // Dá tempo para a animação de desaparecimento
                        setTimeout(() => {
                            if (redirect) {
                                window.location.href = '/';
                            }
                            resolve();
                        }, 300);
                    }, 3000);
                });
            }

            function showError(message, redirect = false) {
                return showToast(message, 'error', redirect);
            }

            function showSuccess(message, redirect = false) {
                return showToast(message, 'success', redirect);
            }
            
            // Altera o estado do botão durante operações assíncronas
            function setButtonState(button, loading, loadingText) {
                const originalText = button.textContent;
                button.disabled = loading;
                button.textContent = loading ? loadingText : originalText;
                return originalText;
            }
            
            // Carrega os dados da enquete da API
            async function loadPollData() {
                try {
                    setLoading(true, 'Carregando dados da enquete...');
                    
                    const response = await axios.get(state.apiUrl);
                    state.poll = response.data.data;
                    console.log("Dados da enquete carregados:", state.poll);
                    
                    // Preenche os campos do formulário
                    elements.title.value = state.poll.title;
                    elements.startDate.value = formatDate(state.poll.start_date);
                    elements.endDate.value = formatDate(state.poll.end_date);
                    
                    // Limpa o container de opções
                    elements.optionsContainer.innerHTML = '';
                    
                    // Verifica se a enquete tem opções
                    if (state.poll.poll_options && state.poll.poll_options.length > 0) {
                        console.log(`Carregando ${state.poll.poll_options.length} opções existentes`);
                        
                        // Armazena as opções originais
                        state.originalOptions = [...state.poll.poll_options];
                        
                        // Preenche as opções existentes
                        state.poll.poll_options.forEach(option => {
                            addOptionToDOM(option.id, option.option_text, option.votes || 0);
                        });
                    } else {
                        console.log("Enquete não possui opções. Adicionando opções padrão.");
                        // Adiciona pelo menos 3 opções em branco
                        for (let i = 0; i < 3; i++) {
                            addOptionToDOM();
                        }
                    }
                    
                    return true;
                } catch (error) {
                    console.error('Erro ao carregar os dados da enquete:', error);
                    
                    // Mensagem de erro mais detalhada
                    let errorMessage = 'Erro ao carregar os dados da enquete.';
                    if (error.response) {
                        if (error.response.status === 404) {
                            errorMessage = 'Enquete não encontrada.';
                        } else if (error.response.data && error.response.data.message) {
                            errorMessage = `Erro: ${error.response.data.message}`;
                        }
                    } else if (error.request) {
                        errorMessage = 'Erro de conexão. Verifique sua internet.';
                    }
                    
                    await showError(errorMessage, true);
                    return false;
                } finally {
                    setLoading(false);
                }
            }
            
            // Controla o estado de carregamento
            function setLoading(isLoading, message = '') {
                state.loading = isLoading;
                if (isLoading && message) {
                    elements.optionsContainer.innerHTML = `<div class="text-center py-4">${message}</div>`;
                }
            }
            
            // Função para adicionar uma opção ao DOM
            function addOptionToDOM(id = "", text = "", votes = 0) {
                const optionDiv = document.createElement('div');
                optionDiv.classList.add('flex', 'items-center', 'space-x-4', 'mb-2');
                
                const hasVotes = votes > 0;
                const deleteButton = hasVotes ? 
                    `<button type="button" class="px-3 py-1 bg-gray-400 text-white rounded-md cursor-not-allowed" disabled title="Opções com votos não podem ser excluídas">
                        Deletar
                    </button>` : 
                    `<button type="button" class="px-3 py-1 bg-red-500 text-white rounded-md hover:bg-red-600 transition delete-option-btn">
                        Deletar
                    </button>`;
                    
                optionDiv.innerHTML = `
                    <input type="hidden" name="option_ids[]" value="${id}">
                    <input type="text" name="options[]" value="${text}" placeholder="Nova opção" required
                           class="flex-1 p-2 text-black rounded-md border-gray-300 shadow-sm focus:border-[#FF2D20] focus:ring-[#FF2D20]">
                    <span class="text-gray-500 min-w-[80px]">${votes > 0 ? `Votos: ${votes}` : ''}</span>
                    ${deleteButton}
                `;
                elements.optionsContainer.appendChild(optionDiv);

                // Adiciona evento de alteração ao input para rastrear mudanças
                const textInput = optionDiv.querySelector('input[name="options[]"]');
                if (id) {
                    textInput.addEventListener('change', () => {
                        state.modifiedOptions.set(id, textInput.value);
                    });
                }

                // Adiciona evento diretamente ao botão recém-criado somente se não tiver votos
                if (!hasVotes) {
                    const deleteButton = optionDiv.querySelector('.delete-option-btn');
                    if (deleteButton) {
                        deleteButton.addEventListener('click', handleOptionDelete);
                    }
                }

                return optionDiv;
            }

            // Handler para deletar opção
            function handleOptionDelete() {
                const optionDiv = this.closest('div');
                const optionIdInput = optionDiv.querySelector('input[name="option_ids[]"]');
                const optionId = optionIdInput.value;
                
                // Verifica se há pelo menos 3 opções
                if (elements.optionsContainer.children.length <= 3) {
                    showError('A enquete deve ter pelo menos 3 opções.');
                    return;
                }
                
                // Se o option_id não estiver vazio (opção existente), adicione à lista de deletados
                if (optionId) {
                    // Cria um modal temporário para confirmar a exclusão da opção
                    const optionText = optionDiv.querySelector('input[name="options[]"]').value;
                    
                    // Adicione temporariamente um modal de confirmação ao DOM
                    const tempModal = document.createElement('div');
                    tempModal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
                    tempModal.innerHTML = `
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg max-w-md w-full">
                            <h3 class="text-xl font-bold mb-4 text-red-500">Confirmar Exclusão</h3>
                            <p class="mb-6">Tem certeza que deseja excluir a opção "${optionText}"?</p>
                            <div class="flex justify-end space-x-4">
                                <button id="cancelDeleteOption" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 transition">Cancelar</button>
                                <button id="confirmDeleteOption" class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition">Excluir</button>
                            </div>
                        </div>
                    `;
                    document.body.appendChild(tempModal);
                    
                    // Adiciona os event listeners para os botões
                    document.getElementById('cancelDeleteOption').addEventListener('click', () => {
                        document.body.removeChild(tempModal);
                    });
                    
                    document.getElementById('confirmDeleteOption').addEventListener('click', () => {
                        state.deletedOptionIds.push(optionId);
                        optionDiv.remove();
                        console.log('Opção existente marcada para deleção:', optionId);
                        document.body.removeChild(tempModal);
                    });
                } else {
                    // Se for uma nova opção, apenas remova do DOM
                    optionDiv.remove();
                }
            }

            // Handler para adicionar nova opção
            function handleAddOption() {
                const optionDiv = addOptionToDOM();
                // Foca no campo de texto da nova opção
                const textInput = optionDiv.querySelector('input[name="options[]"]');
                textInput.focus();
            }

            // Validação do formulário
            function validateForm() {
                // Validação de datas
                const startDate = new Date(elements.startDate.value);
                const endDate = new Date(elements.endDate.value);
                
                if (endDate <= startDate) {
                    showError('A data de término deve ser posterior à data de início.');
                    return false;
                }
                
                // Verificar se há pelo menos 3 opções
                const optionsCount = document.querySelectorAll('input[name="options[]"]').length;
                if (optionsCount < 3) {
                    showError('A enquete deve ter pelo menos 3 opções.');
                    return false;
                }
                
                return true;
            }

            // Handle form submission
            async function handleFormSubmit(event) {
                event.preventDefault();
                
                if (!validateForm()) return;
                
                // Preparar o botão para estado de carregamento
                const originalButtonText = setButtonState(elements.updateButton, true, 'Atualizando...');
                
                try {
                    // 1. Atualiza a enquete (título e datas)
                    const formData = new FormData(elements.form);
                    
                    // Remove as opções do FormData para atualizar apenas a enquete
                    formData.delete('options[]');
                    formData.delete('option_ids[]');
                    
                    await axios.post(state.apiUrl, formData, {
                        headers: {
                            'Content-Type': 'multipart/form-data',
                        },
                    });
                    
                    // 2. Processa opções deletadas
                    const deletionPromises = state.deletedOptionIds.map(async id => {
                        try {
                            await axios.delete(`/api/poll-option/${id}`);
                            console.log(`Opção com ID ${id} deletada com sucesso.`);
                        } catch (error) {
                            console.error(`Erro ao deletar a opção com ID ${id}:`, error);
                            throw error; // Re-throw para tratamento no catch externo
                        }
                    });
                    
                    // 3. Atualiza opções modificadas
                    const updatePromises = [];
                    for (const [id, text] of state.modifiedOptions.entries()) {
                        if (!state.deletedOptionIds.includes(id)) { // Não atualiza se já foi deletada
                            updatePromises.push(
                                axios.put(`/api/poll-option/${id}`, {
                                    option_text: text
                                }).then(() => {
                                    console.log(`Opção com ID ${id} atualizada com sucesso.`);
                                }).catch(error => {
                                    console.error(`Erro ao atualizar a opção com ID ${id}:`, error);
                                    throw error;
                                })
                            );
                        }
                    }
                    
                    // 4. Cria novas opções
                    const newOptions = Array.from(document.querySelectorAll('input[name="options[]"]'))
                        .filter(optionInput => {
                            const optionIdInput = optionInput.closest('div').querySelector('input[name="option_ids[]"]');
                            return !optionIdInput.value; // Retorna apenas opções sem ID (novas)
                        });
                    
                    const createPromises = newOptions.map(async optionInput => {
                        try {
                            const optionText = optionInput.value;
                            if (optionText.trim() === "") return;
                            
                            await axios.post(`/api/poll-option`, {
                                poll_id: state.pollId,
                                option_text: optionText
                            });
                            
                            console.log(`Nova opção "${optionText}" criada com sucesso.`);
                        } catch (error) {
                            console.error(`Erro ao criar nova opção "${optionInput.value}":`, error);
                            throw error;
                        }
                    });
                    
                    // Aguarda todas as operações de opções finalizarem
                    await Promise.all([...deletionPromises, ...updatePromises, ...createPromises]);
                    
                    await showSuccess('Enquete atualizada com sucesso!', true);
                } catch (error) {
                    console.error('Erro ao processar atualização da enquete:', error);
                    
                    let errorMessage = 'Erro ao atualizar a enquete. Por favor, tente novamente.';
                    if (error.response && error.response.data && error.response.data.message) {
                        errorMessage = `Erro: ${error.response.data.message}`;
                    }
                    
                    await showError(errorMessage);
                } finally {
                    setButtonState(elements.updateButton, false, originalButtonText);
                }
            }

            // Handle poll deletion
            async function handleDeletePoll() {
                const originalButtonText = setButtonState(elements.confirmDelete, true, 'Excluindo...');
                
                try {
                    const response = await axios.delete(state.apiUrl);
                    if (response.status === 200) {
                        await showSuccess('Enquete deletada com sucesso!', true);
                    }
                } catch (error) {
                    console.error('Erro ao deletar a enquete:', error);
                    
                    let errorMessage = 'Erro ao deletar a enquete. Por favor, tente novamente.';
                    if (error.response && error.response.data && error.response.data.message) {
                        errorMessage = `Erro: ${error.response.data.message}`;
                    }
                    
                    await showError(errorMessage);
                } finally {
                    setButtonState(elements.confirmDelete, false, originalButtonText);
                    toggleModal(false);
                }
            }
            
            // Inicializa a aplicação
            init();
        });
    </script>
    
    <!-- Notificações toast -->    
    <div id="success-toast" class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg transform translate-y-10 opacity-0 transition-all duration-300 z-50">
        <div class="flex items-center">
            <svg class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span id="toast-message">Operação realizada com sucesso!</span>
        </div>
    </div>

    <div id="error-toast" class="fixed bottom-4 right-4 bg-red-500 text-white px-4 py-2 rounded shadow-lg transform translate-y-10 opacity-0 transition-all duration-300 z-50">
        <div class="flex items-center">
            <svg class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
            </svg>
            <span id="error-message">Ocorreu um erro!</span>
        </div>
    </div>
</body>
</html>