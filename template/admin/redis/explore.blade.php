@extends('admin.blanck')

@section('title', 'Explorateur Redis')

@section('custom-css')
@parent
<style>
.key-actions {
    white-space: nowrap;
}
</style>
@endsection

@section('menu')
@include('admin.menu')
@endsection

{{-- Inclusion des messages --}}
@include('admin.messages')

@section('content')
<div class="col-12 mt-3 mb-5 page-header">
    <h1>{{ $title }}</h1>
</div>

{{-- Inclusion des messages --}}
@include('admin.messages')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">

            <form method="get" action="">
                <div class="input-group mb-3">
                    <input type="text" class="form-control" placeholder="Rechercher des clés" name="filter" value="{{ $filter }}">
                    <button class="btn btn-outline-secondary" type="submit">Rechercher</button>
                    <button type="button" id="delete-selected-keys" class="btn btn-danger">Supprimer les clés sélectionnées</button>
                </div>
            </form>

            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="select-all-keys">
                <label class="form-check-label" for="select-all-keys">
                    Sélectionner tout
                </label>
            </div>

            <!-- Liste des clés -->
            @if(isset($keys) && count($keys) > 0)
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th width="30"></th>
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
                            <td>
                                <input type="checkbox" class="form-check-input key-checkbox" value="{{ $item['key'] }}">
                            </td>
                            <td>
                                <a href="#" class="view-key" data-key="{{ $item['key'] }}">{{ $item['key'] }}</a>
                            </td>
                            <td><span class="badge bg-info">{{ $item['type'] }}</span></td>
                            <td>{{ $item['ttl'] == -1 ? '∞' : $item['ttl'] }}</td>
                            <td>{{ $item['size'] }}</td>
                            <td class="text-end">
                                <a href="#" class="delete-key" data-key="{{ $item['key'] }}">
                                    <i style="color:red" class="bi bi-trash"></i>
                                </a>
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

<!-- Modal pour afficher la valeur -->
<div id="keyDetailsModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Détails de la clé</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <pre id="keyDetailsContent"></pre>
            </div>
        </div>
    </div>
</div>
<div id="alert-container"></div>
@endsection

@section('custom-scripts')
@parent
<script>
$(document).ready(function() {
    console.log('Document ready');
    
    const keyDetailsModal = new bootstrap.Modal($('#keyDetailsModal')[0]);

    // Sélectionner tout
    $('#select-all-keys').on('change', function() {
        console.log('Select all clicked:', $(this).prop('checked'));
        $('.key-checkbox').prop('checked', $(this).prop('checked'));
    });

    // Mettre à jour "Sélectionner tout" quand les cases individuelles changent
    $('.key-checkbox').on('change', function() {
        console.log('Individual checkbox changed');
        const allChecked = $('.key-checkbox:checked').length === $('.key-checkbox').length;
        $('#select-all-keys').prop('checked', allChecked);
    });

    // Suppression des clés sélectionnées
    $('#delete-selected-keys').on('click', function() {
        const selectedKeys = $('.key-checkbox:checked').map(function() {
            return $(this).val();
        }).get();
        
        console.log('Selected keys:', selectedKeys);
        
        if (selectedKeys.length === 0) {
            alert('Veuillez sélectionner au moins une clé à supprimer');
            return;
        }

        if (!confirm(`Êtes-vous sûr de vouloir supprimer ${selectedKeys.length} clé(s) ?`)) {
            return;
        }

        $.ajax({
            url: '/admin/redis/delete-keys',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ keys: selectedKeys }),
            success: function(response) {
                if (response.error) {
                    alert(`Erreur : ${response.error}`);
                } else {
                    window.location.reload();
                }
            },
            error: function(xhr, status, error) {
                alert(`Erreur lors de la suppression : ${error}`);
            }
        });
    });

    // Afficher la valeur d'une clé
    $('.view-key').on('click', function(e) {
        e.preventDefault();
        const key = $(this).data('key');
        
        $.ajax({
            url: '/admin/redis/get-value',
            method: 'GET',
            data: { key: key },
            success: function(response) {
                if (response.error) {
                    alert(`Erreur : ${response.error}`);
                } else {
                    $('#keyDetailsContent').text(response.value);
                    keyDetailsModal.show();
                }
            },
            error: function(xhr, status, error) {
                alert(`Erreur lors de la récupération de la valeur : ${error}`);
            }
        });
    });

    // Supprimer une clé
    $('.delete-key').on('click', function(e) {
        e.preventDefault();
        const key = $(this).data('key');
        
        if (!confirm(`Êtes-vous sûr de vouloir supprimer la clé "${key}" ?`)) {
            return;
        }

        $.ajax({
            url: '/admin/redis/delete-key',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ key: key }),
            success: function(response) {
                if (response.error) {
                    alert(`Erreur : ${response.error}`);
                } else {
                    window.location.reload();
                }
            },
            error: function(xhr, status, error) {
                alert(`Erreur lors de la suppression : ${error}`);
            }
        });
    });
});
</script>
@endsection
