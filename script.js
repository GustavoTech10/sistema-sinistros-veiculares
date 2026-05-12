document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');
    const vehiclesTable = document.getElementById('vehiclesTable');
    const vehiclesList = document.getElementById('vehiclesList');
    const cadastroModal = document.getElementById('cadastroModalOverlay');
    const editarModal = document.getElementById('editarModalOverlay');
    const statusModal = document.getElementById('statusModalOverlay');
    const budgetModal = document.getElementById('budgetModalOverlay');
    const registerButtons = document.querySelectorAll('.open-register-modal');
    const editButtons = document.querySelectorAll('.edit-btn');
    const statusButtons = document.querySelectorAll('.status-btn');
    const budgetButtons = document.querySelectorAll('.budget-btn');
    const deleteButtons = document.querySelectorAll('.delete-btn');
    const closeButtons = document.querySelectorAll('[data-close]');
    const placaInput = document.getElementById('placaInput');
    const editPlacaInput = document.getElementById('editPlacaInput');
    const editVeiculoId = document.getElementById('editVeiculoId');
    const editProprietario = document.getElementById('editProprietario');
    const editCondutor = document.getElementById('editCondutor');
    const editCidade = document.getElementById('editCidade');
    const editProcesso = document.getElementById('editProcesso');
    const editTipoVeiculo = document.getElementById('editTipoVeiculo');
    const statusVeiculoId = document.getElementById('statusVeiculoId');
    const statusPlaca = document.getElementById('statusPlaca');
    const budgetVeiculoId = document.getElementById('budgetVeiculoId');
    const budgetPlaca = document.getElementById('budgetPlaca');
    const valorPecas = document.getElementById('valorPecas');
    const valorMaoObra = document.getElementById('valorMaoObra');
    const valorTotal = document.getElementById('valorTotal');

    function formatCurrency(value) {
        return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
    }

    const updateBudgetTotal = () => {
        const pecas = Number(valorPecas.value) || 0;
        const maoObra = Number(valorMaoObra.value) || 0;
        valorTotal.value = formatCurrency(pecas + maoObra);
    };

    if (valorPecas) valorPecas.addEventListener('input', updateBudgetTotal);
    if (valorMaoObra) valorMaoObra.addEventListener('input', updateBudgetTotal);

    const openModal = (modal) => {
        if (!modal) return;
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
    };
    const closeModal = (modal) => {
        if (!modal) return;
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
    };

    registerButtons.forEach(button => {
        button.addEventListener('click', () => {
            openModal(cadastroModal);
        });
    });

    editButtons.forEach(button => {
        button.addEventListener('click', () => {
            editVeiculoId.value = button.dataset.id || '';
            editPlacaInput.value = button.dataset.placa || '';
            editProprietario.value = button.dataset.proprietario || '';
            editCondutor.value = button.dataset.condutor || '';
            editCidade.value = button.dataset.cidade || '';
            editProcesso.value = button.dataset.processo || '';
            editTipoVeiculo.value = button.dataset.tipo || 'Moto';
            openModal(editarModal);
        });
    });

    statusButtons.forEach(button => {
        button.addEventListener('click', () => {
            statusVeiculoId.value = button.dataset.id;
            statusPlaca.value = button.dataset.placa;
            openModal(statusModal);
        });
    });

    budgetButtons.forEach(button => {
        button.addEventListener('click', () => {
            budgetVeiculoId.value = button.dataset.id;
            budgetPlaca.value = button.dataset.placa;
            valorPecas.value = '0.00';
            valorMaoObra.value = '0.00';
            updateBudgetTotal();
            openModal(budgetModal);
        });
    });

    closeButtons.forEach(button => {
        button.addEventListener('click', () => {
            const modalId = button.dataset.close;
            closeModal(document.getElementById(modalId));
        });
    });

    [cadastroModal, editarModal, statusModal, budgetModal].forEach(modal => {
        if (!modal) return;
        modal.addEventListener('click', (event) => {
            if (event.target === modal) closeModal(modal);
        });
    });

    deleteButtons.forEach(button => {
        button.addEventListener('click', () => {
            const id = button.dataset.id;
            const placa = button.dataset.placa;
            const confirmDelete = confirm(`Remover o veículo ${placa}? Esta ação não pode ser desfeita.`);
            if (confirmDelete) {
                window.location.href = `excluir.php?id=${encodeURIComponent(id)}`;
            }
        });
    });

    const applyPlateMask = (input) => {
        if (!input) return;
        input.addEventListener('input', () => {
            let value = input.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
            if (value.length > 3) {
                value = value.slice(0, 3) + '-' + value.slice(3);
            }
            if (value.length > 8) {
                value = value.slice(0, 8);
            }
            input.value = value;
        });
    };

    applyPlateMask(placaInput);
    applyPlateMask(editPlacaInput);

    if (searchInput && vehiclesList) {
        searchInput.addEventListener('input', () => {
            const term = searchInput.value.trim().toLowerCase();
            vehiclesList.querySelectorAll('[data-search]').forEach(card => {
                const text = (card.dataset.search || '').toLowerCase();
                card.style.display = text.includes(term) ? '' : 'none';
            });
        });
    }

    if (searchInput && vehiclesTable) {
        searchInput.addEventListener('input', () => {
            const term = searchInput.value.trim().toLowerCase();
            [...vehiclesTable.rows].forEach(row => {
                const placa = row.cells[0]?.textContent.toLowerCase() || '';
                const proprietario = row.cells[1]?.textContent.toLowerCase() || '';
                const condutor = row.cells[2]?.textContent.toLowerCase() || '';
                const cidade = row.cells[3]?.textContent.toLowerCase() || '';
                row.style.display = placa.includes(term) || proprietario.includes(term) || condutor.includes(term) || cidade.includes(term) ? '' : 'none';
            });
        });
    }
});
