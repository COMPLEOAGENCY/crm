@extends('admin.blanck')

@section('title', 'Explorateur Redis')

@section('custom-css')
@parent {{-- Pour garder les scripts existants --}}
<style>
.key-actions {
    white-space: nowrap;
}
</style>
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
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="card-title mb-0">Explorateur Redis</h5>
                        </div>
                        <div class="col">
                            <form method="get" class="mb-0">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Rechercher des clés (ex: user:*)" name="filter" value="{{ $filter }}">
                                    <button class="btn btn-primary" type="submit">Rechercher</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if(isset($keys) && count($keys) > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Clé</th>
                                    <th>Type</th>
                                    <th>TTL</th>
                                    <th>Taille</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($keys as $item)
                                <tr>
                                    <td>{{ $item['key'] }}</td>
                                    <td><span class="badge bg-info">{{ $item['type'] }}</span></td>
                                    <td>{{ $item['ttl'] == -1 ? '∞' : $item['ttl'] }}</td>
                                    <td>{{ $item['size'] }}</td>
                                    <td class="text-end key-actions">
                                        <button class="btn btn-sm btn-danger delete-key" data-key="{{ $item['key'] }}" title="Supprimer">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <p class="text-muted mb-0">Aucune clé trouvée pour le filtre : {{ $filter }}</p>
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
    // Gestionnaire de suppression de clé
    document.querySelectorAll('.delete-key').forEach(button => {
        button.addEventListener('click', async function() {
            const key = this.dataset.key;
            if (!confirm(`Êtes-vous sûr de vouloir supprimer la clé "${key}" ?`)) {
                return;
            }

            try {
                const response = await fetch('/admin/redis/delete-key', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ key })
                });

                const data = await response.json();
                if (data.error) {
                    throw new Error(data.error);
                }

                // Recharger la page pour montrer les clés mises à jour
                window.location.reload();
            } catch (error) {
                alert(`Erreur lors de la suppression : ${error.message}`);
            }
        });
    });
});
</script>
@endsection
