            <nav class="bottom-nav" aria-label="Navegacao principal">
                <a href="dashboard.php" class="bottom-nav-link<?php echo navActive('dashboard.php', $currentPage); ?>">
                    <i class="fas fa-chart-pie"></i>
                    <span>Dashboard</span>
                </a>
                <a href="index.php" class="bottom-nav-link<?php echo navActive('index.php', $currentPage); ?>">
                    <i class="fas fa-car-side"></i>
                    <span>Veiculos</span>
                </a>
                <?php if ($currentPage === 'index.php'): ?>
                    <button type="button" class="bottom-nav-link nav-primary open-register-modal">
                        <i class="fas fa-plus"></i>
                        <span>Cadastrar</span>
                    </button>
                <?php else: ?>
                    <a href="cadastrar.php" class="bottom-nav-link nav-primary<?php echo navActive('cadastrar.php', $currentPage); ?>">
                        <i class="fas fa-plus"></i>
                        <span>Cadastrar</span>
                    </a>
                <?php endif; ?>
                <a href="historico.php" class="bottom-nav-link<?php echo navActive('historico.php', $currentPage); ?>">
                    <i class="fas fa-clock-rotate-left"></i>
                    <span>Historico</span>
                </a>
            </nav>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="script.js?v=app-20260512-2"></script>
</body>
</html>
