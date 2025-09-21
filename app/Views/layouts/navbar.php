<nav class="navbar navbar-expand-lg navbar-dark navbar-gob">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="/">
            <img src="https://framework-gb.cdn.gob.mx/landing/img/logoheader.svg" alt="Gobierno de México" height="40" class="me-3">
            <div>
                <div class="fw-bold fs-5">INIFAP Zacatecas</div>
                <small>Geoportal Agrícola</small>
            </div>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page ?? '') === 'mapa' ? 'active' : '' ?>" href="/">
                        <i class="bi bi-geo-alt me-1"></i>Mapa Principal
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page ?? '') === 'cultivos' ? 'active' : '' ?>" href="/cultivos">
                        <i class="bi bi-grid me-1"></i>Cultivos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" onclick="mostrarAyuda()">
                        <i class="bi bi-question-circle me-1"></i>Ayuda
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>