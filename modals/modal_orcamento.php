<div class="modal-overlay" id="budgetModalOverlay" aria-hidden="true">
    <div class="modal-window modal-large" role="dialog" aria-modal="true" aria-labelledby="budgetModalTitle">
        <button type="button" class="modal-close" data-close="budgetModalOverlay" title="Fechar"><i class="fas fa-times"></i></button>
        <div class="modal-header">
            <h2 id="budgetModalTitle"><i class="fas fa-file-invoice-dollar"></i> Orçamento de Moto</h2>
            <p id="budgetModalSubtitle">Registre o orçamento quando o veículo estiver em oficina.</p>
        </div>
        <form action="salvar_orcamento.php" method="post" class="modal-form">
            <input type="hidden" name="veiculo_id" id="budgetVeiculoId" value="" />
            <label>
                <span>Placa</span>
                <input id="budgetPlaca" type="text" disabled />
            </label>
            <label>
                <span>Valor das Peças (R$)</span>
                <input id="valorPecas" name="valor_pecas" type="number" step="0.01" min="0" value="0.00" required />
            </label>
            <label>
                <span>Valor Mão de Obra (R$)</span>
                <input id="valorMaoObra" name="valor_mao_obra" type="number" step="0.01" min="0" value="0.00" required />
            </label>
            <label>
                <span>Total Estimado</span>
                <input id="valorTotal" type="text" disabled value="R$ 0,00" />
            </label>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" data-close="budgetModalOverlay"><i class="fas fa-xmark"></i> Cancelar</button>
                <button type="submit" name="submit_orcamento" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Salvar orçamento</button>
            </div>
        </form>
    </div>
</div>
