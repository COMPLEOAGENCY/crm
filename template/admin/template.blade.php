@extends('admin.blanck')

@section('title', 'Titre de la page')

@section('custom-css')
@parent {{-- Pour garder les scripts existants --}}
<!-- Ajoutez vos styles CSS personnalisés ici -->
@endsection

@section('menu')
@include('admin.menu')
@endsection

@section('content')
<div class="col-12 mt-3 mb-5 page-header">
    <h1>{{ $title }}</h1>
</div>

{{-- Inclusion des messages --}}
@include('admin.messages')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Message d'erreur -->
            @if(isset($error))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ $error }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <!-- Message de succès -->
            @if(isset($success))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ $success }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <!-- Contenu principal -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Titre de la carte</h5>
                </div>
                <div class="card-body">
                    <!-- Votre contenu ici -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom-js')
@parent {{-- Pour garder les scripts existants --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Votre code JavaScript ici
});
</script>
@endsection
