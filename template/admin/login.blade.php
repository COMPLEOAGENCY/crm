@extends('admin.blanck')

@section('title', $title)

@section('custom-css')
@parent {{-- Pour garder les scripts existants --}}

<style>
    body {
        background: linear-gradient(135deg, #ff4b6a, #3a8ecf, #1d4e89);
        background-size: 200% 200%;
        min-height: 100vh;
        animation: gradientBG 15s ease infinite;
    }

    .login-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;

    }

    /* Centrage du cadre de connexion */
    .login-card {
        max-width: 400px;
        width: 100%;
        margin: 20px;
        box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
    }

    .input-group-text {
        width: 40px;
    }

    .form-control {
        height: calc(1.5em + 1rem + 2px);
    }

    /* Ajouter cette animation pour un effet plus dynamique */
    @keyframes gradientBG {
        0% {
            background-position: 0% 50%;
        }

        50% {
            background-position: 100% 50%;
        }

        100% {
            background-position: 0% 50%;
        }
    }

    .logo-gradient img {
    width: 200px;
    /* Ajouter un background gradient animé */
    background: linear-gradient(45deg, #ff4b6a, #3a8ecf, #1d4e89, #ff4b6a);
    background-size: 300% 300%;
    animation: gradientLogo 8s ease infinite;
    /* Appliquer le gradient en tant que couleur de texte */
    -webkit-background-clip: text;
    color: transparent;
}

/* Animation du gradient pour l'effet dynamique */
@keyframes gradientLogo {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}


    


</style>
@endsection

@section('content')
{{-- Affichage du formulaire de connexion --}}
<div class="container-fluid h-100">
    <div class="row h-100 align-items-center">
        <div class="col-12 text-center mt-4 mb-4">
            <div class="logo-gradient">
                <img src="{{ asset('assets/img/logo-black.png') }}" class="img-fluid" alt="Logo" style="width: 200px;">
            </div>
        </div>
        <div class="col-sm-6 col-12 col-lg-4 my-auto mx-auto">
            {{-- Inclusion des messages --}}
            @include('admin.messages')
            <div class="login-wrapper">
                {{-- Formulaire de connexion --}}
                <div class="card login-card">
                    <article class="card-body">
                        <h4 class="card-title text-center mb-4 mt-1">Connexion</h4>
                        <hr>
                        <form action="" method="POST" class="form-signin">
                            {{-- Email Field --}}
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fa fa-user"></i>
                                        </span>
                                    </div>
                                    <input
                                        type="email"
                                        name="email"
                                        class="form-control @error('email') is-invalid @enderror"
                                        placeholder="Email"
                                        value=""
                                        required
                                        autocomplete="username">
                                </div>
                            </div>

                            {{-- Password Field --}}
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fa fa-lock"></i>
                                        </span>
                                    </div>
                                    <input
                                        type="password"
                                        name="password"
                                        class="form-control @error('password') is-invalid @enderror"
                                        placeholder="Mot de passe"
                                        required
                                        autocomplete="current-password">
                                </div>
                            </div>

                            {{-- Submit Button --}}
                            <div class="form-group">
                                <button type="submit" name="connexion" value="valid" class="btn btn-primary btn-block">
                                    Se connecter
                                </button>
                            </div>
                        </form>
                    </article>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('custom-scripts')
@parent {{-- Pour garder les scripts existants --}}

<script>
    $(document).ready(function() {
        // Réinitialiser le formulaire au chargement
        $('form')[0].reset();

        // Validation côté client simple
        $('form').on('submit', function(e) {
            let email = $('input[name="email"]').val();
            let password = $('input[name="password"]').val();

            if (!email || !password) {
                e.preventDefault();
                alert('Veuillez remplir tous les champs');
                return false;
            }

            if (!email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                e.preventDefault();
                alert('Veuillez entrer une adresse email valide');
                return false;
            }
        });
    });
</script>
<!-- DataTables and jQuery UI JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<script>
    $(document).ready(function() {
        $('[data-toggle="tooltip"]').tooltip();

        // Réinitialiser le formulaire au chargement
        $('form')[0].reset();

        // Validation côté client simple
        $('form').on('submit', function(e) {
            let email = $('input[name="email"]').val();
            let password = $('input[name="password"]').val();

            if (!email || !password) {
                e.preventDefault();
                alert('Veuillez remplir tous les champs');
                return false;
            }

            if (!email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                e.preventDefault();
                alert('Veuillez entrer une adresse email valide');
                return false;
            }
        });

        // Initialisation du datepicker pour les champs de date
        $(".datepicker").datepicker({
            numberOfMonths: 1,
            dateFormat: 'dd-mm-yy',
            showOtherMonths: true,
        });

        // Ajout de champs de recherche dans le tableau
        $('.dataTable thead th').each(function() {
            var title = $(this).text().trim();
            if (title !== "Select") {
                $(this).append('<input style="width:100%; min-width:20px;" type="text" placeholder="search" />');
            }
        });

        // Initialisation de DataTables
        var table = $('.dataTable').DataTable({
            deferRender: true,
            scrollY: 4000,
            scrollCollapse: true,
            scroller: true,
            paging: true,
            pageLength: 20, // Nombre d'enregistrements par page par défaut
            lengthMenu: [
                [10, 25, 50, 100],
                [10, 25, 50, 100]
            ], // Options pour changer le nombre de lignes par page
            dom: 'Bfrtip',
            buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
        });

        // Recherche par colonne
        table.columns().eq(0).each(function(colIdx) {
            $('input[type="text"]', table.column(colIdx).header()).on('keyup change', function() {
                table.column(colIdx).search(this.value).draw();
            });
        });
    });
</script>
@endsection