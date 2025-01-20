{{-- messages.blade.php --}}
<div class="messages-container">
    @if(isset($messages['success']) && !empty($messages['success']))
        @foreach($messages['success'] as $successMessage)
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <div class="alert-content">
                    <h4 class="alert-heading">Félicitations!</h4>
                    <p>{{ $successMessage['message'] }}</p>
                    @if(!empty($successMessage['details']))
                        <hr>
                        <ul>
                            @foreach($successMessage['details'] as $detail)
                                <li>{{ $detail }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endforeach
    @endif

    @if(isset($messages['error']) && !empty($messages['error']))
        @foreach($messages['error'] as $errorMessage)
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="alert-content">
                    <h4 class="alert-heading">Attention!</h4>
                    <p>{{ $errorMessage['message'] }}</p>
                    @if(!empty($errorMessage['details']))
                        <hr>
                        <ul>
                            @foreach($errorMessage['details'] as $detail)
                                <li>{{ $detail }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endforeach
    @endif
</div>

@section('custom-css')
    @parent {{-- Pour garder les styles CSS existants --}}
    <style>
        /* Styles généraux pour le conteneur de messages */
        .messages-container {
            width: 100%;
            padding: 1rem;
        }

        /* Styles pour les alertes */
        .alert {
            position: relative;
            margin-bottom: 1rem;
            width: 100%;
            border: 1px solid transparent;
            border-radius: 0.25rem;
            opacity: 1;
            transition: opacity 0.15s linear;
        }

        /* Animation de fade */
        .alert.fade {
            opacity: 0;
            transition: opacity 0.15s linear;
        }

        .alert.fade.show {
            opacity: 1;
        }

        /* Styles spécifiques pour les types d'alertes */
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }

        /* Styles pour l'en-tête de l'alerte */
        .alert-heading {
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
            font-weight: 600;
        }

        /* Styles pour le bouton de fermeture */
        .alert .close {
            position: absolute;
            top: 0.75rem;
            right: 1.25rem;
            padding: 0.25rem 1rem;
            color: inherit;
            font-size: 1.5rem;
            line-height: 1;
            background: transparent;
            border: 0;
            opacity: 0.5;
            cursor: pointer;
            transition: opacity 0.15s linear;
        }

        .alert .close:hover {
            opacity: 1;
        }

        /* Styles pour la liste des détails */
        .alert ul {
            margin-top: 0.5rem;
            margin-bottom: 0;
            padding-left: 1.5rem;
        }

        .alert hr {
            border-top-color: inherit;
            opacity: 0.2;
            margin: 1rem 0;
        }
    </style>
@endsection

@section('custom-scripts')
    @parent {{-- Pour garder les scripts existants --}}
    <script>
        $(document).ready(function() {
            // Fonction pour gérer la fermeture des alertes avec animation
            $('.alert .close').on('click', function(e) {
                e.preventDefault();
                var $alert = $(this).closest('.alert');
                
                $alert.removeClass('show');
                setTimeout(function() {
                    $alert.remove();
                }, 150);
            });

            // Auto-fermeture des alertes après 5 secondes
            setTimeout(function() {
                $('.alert').each(function() {
                    var $alert = $(this);
                    $alert.removeClass('show');
                    setTimeout(function() {
                        $alert.remove();
                    }, 150);
                });
            }, 5000);
        });
    </script>
@endsection