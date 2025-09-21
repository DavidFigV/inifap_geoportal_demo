<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'INIFAP Zacatecas - Geoportal Agrícola' ?></title>
    
    <!-- Bootstrap 5.3.3 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css" />
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Estilos gubernamentales -->
    <style>
        :root {
            --gob-wine: #6b1e3e;
            --gob-green: #046307;
            --gob-blue: #1f4788;
            --gob-gray: #f7f7f7;
            --gob-gold: #f4b942;
        }

        .navbar-gob {
            background-color: var(--gob-wine) !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .btn-gob-primary {
            background-color: var(--gob-wine);
            border-color: var(--gob-wine);
            color: white;
        }

        .btn-gob-primary:hover {
            background-color: #8b2c56;
            border-color: #8b2c56;
            color: white;
        }

        .btn-gob-secondary {
            background-color: var(--gob-green);
            border-color: var(--gob-green);
            color: white;
        }

        .btn-gob-secondary:hover {
            background-color: #057a0a;
            border-color: #057a0a;
            color: white;
        }

        .text-gob-wine {
            color: var(--gob-wine) !important;
        }

        .bg-gob-gray {
            background-color: var(--gob-gray) !important;
        }

        .footer-gob {
            background-color: var(--gob-wine);
            color: white;
            padding: 20px 0;
            margin-top: 40px;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
    </style>
    
    <?= $extraCSS ?? '' ?>
</head>
<body>