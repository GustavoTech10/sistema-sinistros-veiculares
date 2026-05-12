<div class="modal-overlay" id="statusModalOverlay" aria-hidden="true">
    <div class="modal-window modal-large" role="dialog" aria-modal="true" aria-labelledby="statusModalTitle">
        <button type="button" class="modal-close" data-close="statusModalOverlay" title="Fechar"><i class="fas fa-times"></i></button>
        <div class="modal-header">
            <h2 id="statusModalTitle"><i class="fas fa-right-left"></i> Trocar status do veículo</h2>
            <p id="statusModalSubtitle">Atualize o status e mantenha o histórico completo.</p>
        </div>
        <form action="trocar_status.php" method="post" class="modal-form">
            <input type="hidden" name="veiculo_id" id="statusVeiculoId" value="" />
            <label>
                <span>Placa</span>
                <input id="statusPlaca" type="text" disabled />
            </label>
            <label>
                <span>Status Atual</span>
                <select name="status" required>
                    <option value="Comunicado">Comunicado</option>
                    <option value="Regulagem">Regulagem</option>
                    <option value="Em Oficina">Em Oficina</option>
                    <option value="Sindicância">Sindicância</option>
                    <option value="Validação de Sindicância">Validação de Sindicância</option>
                    <option value="Em Perícia">Em Perícia</option>
                    <option value="Entregue">Entregue</option>
                </select>
            </label>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" data-close="statusModalOverlay"><i class="fas fa-xmark"></i> Cancelar</button>
                <button type="submit" name="submit_troca_status" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Salvar status</button>
            </div>
        </form>
    </div>
</div>
