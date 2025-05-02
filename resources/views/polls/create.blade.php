<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Enquete</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50 text-black dark:bg-black dark:text-white">
    <div class="max-w-3xl mx-auto px-6 py-10">
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-3xl font-bold">Criar Enquete</h1>
            <a href="/" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition">
                Voltar para Lista
            </a>
        </div>
        
        <form id="createPollForm" class="space-y-6">
            @csrf

            <div class="mt-4 mb-4">
                <label for="title" class="block text-lg font-medium">Título da Enquete</label>
                <input id="title" name="title" type="text" required
                       class="mt-1 p-2 text-black block w-full rounded-md border-gray-300 shadow-sm focus:border-[#FF2D20] focus:ring-[#FF2D20]"
                       placeholder="Digite o título da enquete">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="start_date" class="block text-lg font-medium">Data de Início</label>
                    <input id="start_date" name="start_date" type="date" required
                           class="mt-1 p-2 text-black block w-full rounded-md border-gray-300 shadow-sm focus:border-[#FF2D20] focus:ring-[#FF2D20]">
                </div>
                <div>
                    <label for="end_date" class="block text-lg font-medium">Data de Término</label>
                    <input id="end_date" name="end_date" type="date" required
                           class="mt-1 p-2 text-black block w-full rounded-md border-gray-300 shadow-sm focus:border-[#FF2D20] focus:ring-[#FF2D20]">
                </div>
            </div>

            <div id="optionsContainer" class="mt-4 space-y-4">
                <h2 class="text-2xl font-semibold">Opções</h2>
                <div id="optionsList" class="space-y-2 mt-2">
                    <!-- Três opções iniciais obrigatórias -->
                    <div class="option-item">
                        <label for="option_1" class="block font-medium">Opção 1</label>
                        <input id="option_1" name="options[]" type="text" required
                               class="mt-1 p-2 text-black block w-full rounded-md border-gray-300 shadow-sm focus:border-[#FF2D20] focus:ring-[#FF2D20]">
                    </div>
                    <div class="option-item">
                        <label for="option_2" class="block font-medium">Opção 2</label>
                        <input id="option_2" name="options[]" type="text" required
                               class="mt-1 p-2 text-black block w-full rounded-md border-gray-300 shadow-sm focus:border-[#FF2D20] focus:ring-[#FF2D20]">
                    </div>
                    <div class="option-item">
                        <label for="option_3" class="block font-medium">Opção 3</label>
                        <input id="option_3" name="options[]" type="text" required
                               class="mt-1 p-2 text-black block w-full rounded-md border-gray-300 shadow-sm focus:border-[#FF2D20] focus:ring-[#FF2D20]">
                    </div>
                </div>
                <!-- Botão para adicionar novas opções, se desejado -->
                <div>
                    <button type="button" id="addOption" class="mt-2 px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition">
                        Adicionar opção
                    </button>
                </div>
            </div>

            <div>
                <button type="submit" id="submitButton" class="px-6 py-2 bg-[#FF2D20] text-white rounded-md hover:bg-[#FF2D20]/90 focus:outline-none focus:ring-2 focus:ring-[#FF2D20] transition">
                    Criar Enquete
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Estado da aplicação
            const state = {
                loading: false,
                lastOptionIndex: 3, // Inicia com 3 opções
                createdPollId: null
            };
            
            // Elementos do DOM
            const elements = {
                form: document.getElementById('createPollForm'),
                title: document.getElementById('title'),
                startDate: document.getElementById('start_date'),
                endDate: document.getElementById('end_date'),
                optionsList: document.getElementById('optionsList'),
                addOptionButton: document.getElementById('addOption'),
                submitButton: document.getElementById('submitButton')
            };
            
            // Inicializa a aplicação
            init();
            
            // Função de inicialização
            function init() {
                // Preenche a data inicial com a data atual
                const today = new Date();
                elements.startDate.value = formatDate(today);
                
                // Preenche a data de término com o dia seguinte
                const tomorrow = new Date();
                tomorrow.setDate(tomorrow.getDate() + 1);
                elements.endDate.value = formatDate(tomorrow);
                
                // Adiciona os event listeners
                addEventListeners();
            }
            
            // Adiciona todos os listeners de eventos
            function addEventListeners() {
                elements.addOptionButton.addEventListener('click', handleAddOption);
                elements.form.addEventListener('submit', handleFormSubmit);
            }
            
            // Funções utilitárias
            function formatDate(date) {
                return date.toISOString().split('T')[0]; // Retorna formato YYYY-MM-DD
            }
            
            function showError(message) {
                alert(message);
            }
            
            function showSuccess(message) {
                alert(message);
            }
            
            function setLoading(isLoading) {
                state.loading = isLoading;
                elements.submitButton.disabled = isLoading;
                elements.submitButton.textContent = isLoading ? 'Criando...' : 'Criar Enquete';
                
                // Desativa todos os inputs durante o carregamento
                const inputs = document.querySelectorAll('input, button:not(#submitButton)');
                inputs.forEach(input => {
                    input.disabled = isLoading;
                });
            }
            
            // Validação do formulário
            function validateForm() {
                // Verifica se o título está preenchido
                if (!elements.title.value.trim()) {
                    showError('O título da enquete é obrigatório.');
                    elements.title.focus();
                    return false;
                }
                
                // Validação de datas
                const startDate = new Date(elements.startDate.value);
                const endDate = new Date(elements.endDate.value);
                
                if (endDate <= startDate) {
                    showError('A data de término deve ser posterior à data de início.');
                    elements.endDate.focus();
                    return false;
                }
                
                // Verifica se todas as opções estão preenchidas
                const options = document.querySelectorAll('input[name="options[]"]');
                for (let i = 0; i < options.length; i++) {
                    if (!options[i].value.trim()) {
                        showError(`A opção ${i+1} é obrigatória.`);
                        options[i].focus();
                        return false;
                    }
                }
                
                // Verifica se há pelo menos 3 opções
                if (options.length < 3) {
                    showError('A enquete deve ter pelo menos 3 opções.');
                    return false;
                }
                
                return true;
            }
            
            // Manipuladores de eventos
            function handleAddOption() {
                state.lastOptionIndex++;
                const index = state.lastOptionIndex;
                
                const optionDiv = document.createElement('div');
                optionDiv.classList.add('option-item', 'mt-4');
                optionDiv.innerHTML = `
                    <div class="flex justify-between items-center">
                        <label for="option_${index}" class="block font-medium">Opção ${index}</label>
                        <button type="button" class="text-red-500 hover:text-red-700 remove-option-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                    <input id="option_${index}" name="options[]" type="text" required
                           class="mt-1 p-2 text-black block w-full rounded-md border-gray-300 shadow-sm focus:border-[#FF2D20] focus:ring-[#FF2D20]">
                `;
                
                // Adiciona evento para remover a opção
                const removeButton = optionDiv.querySelector('.remove-option-btn');
                removeButton.addEventListener('click', () => {
                    // Verifica se há pelo menos 3 opções antes de remover
                    const options = document.querySelectorAll('.option-item');
                    if (options.length <= 3) {
                        showError('A enquete deve ter pelo menos 3 opções.');
                        return;
                    }
                    
                    optionDiv.remove();
                });
                
                elements.optionsList.appendChild(optionDiv);
                
                // Foca no campo recém-criado
                const inputField = optionDiv.querySelector('input');
                inputField.focus();
            }
            
            async function handleFormSubmit(event) {
                event.preventDefault();
                
                if (!validateForm()) return;
                
                setLoading(true);
                
                try {
                    console.log('Iniciando criação da enquete...');
                    
                    // 1. Cria a enquete
                    const pollData = await createPoll();
                    if (!pollData) {
                        throw new Error('Falha ao criar enquete');
                    }
                    
                    console.log('Enquete criada, ID:', pollData.id);
                    
                    // 2. Cria as opções da enquete
                    const optionsCreated = await createPollOptions(pollData.id);
                    if (!optionsCreated) {
                        console.error('Falha ao criar opções da enquete');
                        showError('A enquete foi criada, mas houve um problema ao adicionar as opções. Por favor, tente editar a enquete para adicionar opções.');
                        window.location.href = '/';
                        return;
                    }
                    
                    // 3. Redireciona para a lista de enquetes
                    showSuccess('Enquete criada com sucesso!');
                    window.location.href = '/';
                    
                } catch (error) {
                    console.error('Erro no processo de criação da enquete:', error);
                    showError('Ocorreu um erro ao criar a enquete. Por favor, tente novamente.');
                } finally {
                    setLoading(false);
                }
            }
            
            // Funções de API
            async function createPoll() {
                try {
                    // Em vez de usar FormData, vamos construir um objeto JavaScript explícito
                    const pollData = {
                        title: elements.title.value,
                        start_date: elements.startDate.value,
                        end_date: elements.endDate.value
                    };
                    
                    console.log('Dados da enquete a serem enviados:', pollData);
                    
                    const response = await axios.post('/api/poll', pollData, {
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                        }
                    });
                    
                    if (response.status === 201 && response.data) {
                        console.log('Enquete criada com sucesso:', response.data);
                        return response.data.data || response.data;
                    } else {
                        console.error('Erro: Resposta inesperada da API', response);
                        return null;
                    }
                } catch (error) {
                    console.error('Erro ao criar enquete:', error);
                    if (error.response && error.response.data && error.response.data.message) {
                        showError(`Erro: ${error.response.data.message}`);
                    } else {
                        showError('Erro ao criar a enquete. Verifique o console para mais detalhes.');
                    }
                    return null;
                }
            }
            
            async function createPollOptions(pollId) {
                try {
                    const options = Array.from(document.querySelectorAll('input[name="options[]"]'));
                    const pollOptions = options
                        .filter(option => option.value.trim() !== '')
                        .map(option => ({
                            poll_id: pollId,
                            option_text: option.value.trim()
                        }));
                    
                    console.log('Opções a serem enviadas:', pollOptions);
                    
                    // Se não houver opções para enviar, retorne falso
                    if (pollOptions.length === 0) {
                        console.error('Nenhuma opção válida para enviar');
                        return false;
                    }
                    
                    // Dados a serem enviados na requisição
                    const requestData = { options: pollOptions };
                    
                    console.log('Objeto completo a ser enviado:', JSON.stringify(requestData));
                    
                    const response = await axios.post('/api/poll-options-batch', requestData, {
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                        }
                    });
                    
                    if (response.status === 201 || response.status === 200) {
                        console.log('Opções criadas com sucesso:', response.data);
                        return true;
                    }
                    
                    console.error('Resposta inesperada da API ao criar opções:', response);
                    return false;
                    
                } catch (error) {
                    console.error('Erro ao criar opções em lote:', error);
                    // Já que a enquete foi criada, mas as opções falharam, podemos tentar o método individual
                    return await createPollOptionsIndividually(pollId);
                }
            }
            
            // Método alternativo de criar opções individualmente se o lote falhar
            async function createPollOptionsIndividually(pollId) {
                try {
                    const options = Array.from(document.querySelectorAll('input[name="options[]"]'))
                        .filter(option => option.value.trim() !== '');
                    
                    console.log(`Tentando criar ${options.length} opções individualmente`);
                    
                    let successCount = 0;
                    
                    for (const option of options) {
                        const pollOption = {
                            poll_id: pollId,
                            option_text: option.value.trim()
                        };
                        
                        console.log('Enviando opção individual:', pollOption);
                        
                        try {
                            const response = await axios.post('/api/poll-option', pollOption, {
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                                }
                            });
                            
                            if (response.status === 201 || response.status === 200) {
                                console.log('Opção criada com sucesso:', response.data);
                                successCount++;
                            } else {
                                console.error('Falha ao criar opção:', response);
                            }
                        } catch (innerError) {
                            console.error('Erro ao criar opção individual:', innerError);
                        }
                    }
                    
                    console.log(`${successCount} de ${options.length} opções criadas com sucesso`);
                    return successCount >= 3; // Retorna true se pelo menos 3 opções foram criadas com sucesso
                } catch (error) {
                    console.error('Erro ao criar opções individualmente:', error);
                    return false;
                }
            }
        });
    </script>
</body>
</html>