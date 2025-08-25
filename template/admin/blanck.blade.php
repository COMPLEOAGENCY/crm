<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="{{ asset('assets/img/favicon.ico') }}" type="image/x-icon">

    <title>@yield('title', 'Default Title')</title>

    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/css/bootstrap-select.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css">

    <style type="text/css">
        /* Variables CSS globales */
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
        }

        /* Style du body avec gradient moderne */
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        /* En-tête de page moderne */
        .page-header {
            background: var(--primary-gradient);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        /* Cards modernes - sans override des cards existantes */
        .modern-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: transform 0.3s;
        }

        .modern-card:hover {
            transform: translateY(-5px);
        }

        /* Amélioration subtile des cards existantes */
        .card {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        /* Boutons avec gradient - classe optionnelle */
        .btn-gradient {
            background: var(--primary-gradient);
            border: none;
            border-radius: 25px;
            padding: 10px 30px;
            color: white;
            transition: all 0.3s;
        }

        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }

        /* Cards de statistiques */
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            background: var(--primary-gradient);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Styles existants pour les tables */
        table td {
            overflow-wrap: break-word;
            vertical-align: top;
        }

        table.dataTable {
            border: none;
            font-size: 12px;
        }

        table.dataTable tbody td {
            vertical-align: top;
        }

        /* Tables modernes - classe optionnelle */
        .table-modern {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .table-modern thead {
            background: var(--primary-gradient);
            color: white;
        }

        /* Checkboxes */
        [type="checkbox"] {
            width: 30px;
            height: 30px;
            cursor: pointer;
        }

        /* Filtres - IMPORTANT pour selectpicker */
        .filter-option {
            max-width: 80vw;
            overflow: hidden;
        }

        /* Ne pas override bootstrap-select */
        .bootstrap-select .dropdown-toggle {
            border-radius: 5px;
        }

        /* Validation et erreurs */
        .is-invalid {
            border-color: #dc3545;
            padding-right: calc(1.5em + .75rem);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='%23dc3545' viewBox='0 0 12 12'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(.375em + .1875rem) center;
            background-size: calc(.75em + .375rem) calc(.75em + .375rem);
        }

        .text-danger {
            color: #dc3545;
        }

        label.required:after {
            content: " *";
            color: #dc3545;
        }

        /* Amélioration subtile des formulaires sans casser selectpicker */
        .form-control:not(.selectpicker) {
            border-radius: 8px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .form-control:not(.selectpicker):focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        /* Badges modernes - classe optionnelle */
        .badge-modern {
            border-radius: 20px;
            padding: 5px 15px;
            font-weight: 500;
        }

        /* Alertes modernes - amélioration subtile */
        .alert {
            border-radius: 8px;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-out;
        }
    </style>
    <!-- Custom CSS -->
    @yield('custom-css')
</head>

<body>
    <!-- Header -->
    <header class="d-flex flex-column flex-md-row align-items-center mb-3 bg-white border-bottom box-shadow">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    @yield('menu')
                </div>
            </div>
        </div>
    </header>

    <!-- Main content -->
    <main class="container-fluid">

            @yield('content')


    </main>

    <!-- JavaScript and custom scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/js/bootstrap-select.min.js"></script>
    @yield('custom-scripts')
</body>

</html>