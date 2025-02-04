@extends('admin.blanck')

@section('title', 'État du Serveur Redis')

@section('custom-css')
@parent {{-- Pour garder les scripts existants --}}
@endsection

@section('menu')
@include('admin.menu')
@endsection

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

            <!-- Contenu principal -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">État du Serveur Redis</h5>
                </div>
                <div class="card-body">
                    @if(isset($kpis))
                    <div class="row">
                        @foreach ($kpis as $kpi)
                        <div class="col-md-6 col-lg-3 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-2 text-muted">{{ $kpi['key'] }}</h6>
                                    <h4 class="card-title mb-2">{{ $kpi['value'] }}</h4>
                                    <p class="card-text small text-muted">{{ $kpi['description'] }}</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
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
    // Rafraîchissement automatique toutes les 30 secondes
    setInterval(function() {
        window.location.reload();
    }, 30000);
});
</script>
@endsection
