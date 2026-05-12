            <nav class="bottom-nav" aria-label="Navegação principal">
                <a href="dashboard.php" class="bottom-nav-link<?php echo navActive('dashboard.php', $currentPage); ?>">
                    <i class="fas fa-chart-pie"></i>
                    <span>Dashboard</span>
                </a>
                <a href="index.php" class="bottom-nav-link<?php echo navActive('index.php', $currentPage); ?>">
                    <i class="fas fa-car-side"></i>
                    <span>Veículos</span>
                </a>
                <a href="cadastrar.php" class="bottom-nav-link nav-primary<?php echo navActive('cadastrar.php', $currentPage); ?>">
                    <i class="fas fa-plus"></i>
                    <span>Cadastrar</span>
                </a>
                <a href="historico.php" class="bottom-nav-link<?php echo navActive('historico.php', $currentPage); ?>">
                    <i class="fas fa-clock-rotate-left"></i>
                    <span>Histórico</span>
                </a>
                <a href="index.php" class="bottom-nav-link">
                    <i class="fas fa-circle-user"></i>
                    <span>Perfil</span>
                </a>
            </nav>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="script.js?v=app-20260512-1"></script>
</body>
</html>
