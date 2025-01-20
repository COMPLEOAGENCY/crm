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

        [type="checkbox"] {
            width: 30px;
            height: 30px;
            cursor: pointer;
        }

        .filter-option {
            max-width: 80vw;
            overflow: hidden;
        }

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
        <div class="row">

            @yield('content')

        </div>
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