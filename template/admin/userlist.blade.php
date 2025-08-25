@extends('admin.blanck')

@section('title', $title)

@section('custom-css')
@parent {{-- Pour garder les scripts existants --}}
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
@endsection

@section('menu')
@include('admin.menu')
@endsection

@section('content')

    {{-- Header moderne style ventes --}}
    <div class="card mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 15px;">
        <div class="card-body py-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-people-fill" style="font-size: 2.5rem; margin-right: 20px;"></i>
                <div>
                    <h1 class="mb-0" style="font-size: 2rem; font-weight: 600;">Liste des comptes</h1>
                    <p class="mb-0" style="opacity: 0.9;">{{ count($userList) }} utilisateurs actifs</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Inclusion des messages --}}
    @include('admin.messages')

    {{-- Inclusion du menu utilisateur --}}
    <div class="mb-3">
        @include('admin.userlistmenu')
    </div>

    <form id="user" method="POST">
        <div class="card modern-card mb-4">
        <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <h5 class="mb-0"><i class="bi bi-people-fill me-2"></i>Liste des utilisateurs</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover dataTable" id="example">
            <thead>
                <tr>
                    <th>Select</th>
                    <th>Id</th>
                    <th>Création</th>
                    <th>Modification</th>
                    <th>Client</th>
                    <th>Contact</th>
                    <th>Gestionnaire</th>
                    <th>Solde</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                @foreach($userList as $user)
                    <tr>
                        <td class="center">
                            <input type="checkbox" name="id_array[{{ $user->userId }}]" {{ isset($params['id_array'][$user->userId]) ? 'checked' : '' }}>
                        </td>
                        <td><a href="/admin/useradd/{{ $user->userId }}">{{ $user->userId }}</a></td>
                        <td>{{ isset($user->timestamp) ? date("d/m/Y", $user->timestamp) : '' }}</td>
                        <td>{{ isset($user->last_update_timestamp) ? date("d/m/Y", $user->last_update_timestamp) : '' }}</td>
                        <td>
                            <b>Raison</b> : {{ $user->company ?? '' }}<br>
                            <b>Siren</b> : {{ $user->registration_number ?? '' }}<br>
                        </td>
                        <td>
                            <b>Nom</b> : {{ ($user->civ ?? '') . ' ' . ($user->first_name ?? '') }}<br>
                            <b>Prénom</b> : {{ $user->last_name ?? '' }}<br>
                            <b>Email</b> : {{ $user->email ?? '' }}<br>
                            <b>Tel</b> : {{ $user->mobile ?? '' }}<br>
                        </td>
                        <td>{{ isset($crmUserList[$user->vendor_id]) ? $user->vendor_id . ' - ' . $crmUserList[$user->vendor_id]->crm_user_firstname . ' ' . $crmUserList[$user->vendor_id]->crm_user_lastname : '' }}</td>
                        <td>{{ number_format($user->solde_details['solde'] ?? 0, 2) }} €</td>
                        <td>{{ $user->statut }}</td>
                    </tr>
                @endforeach
            </tbody>
                </table>
            </div>
        </div>
    </div>

        <!-- Panel d'actions -->
        <div class="card modern-card">
            <div class="card-body">
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label" for="action"><strong>Action</strong></label>
                    <div class="col-sm-10">
                        <select data-required="true" class="form-control" id="action" name="action">
                            <option value="">Choisir une action</option>
                            <option value="copy">Copier</option>
                            <option value="synch">Synchroniser vers la boutique</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" name="submit" value="valid" class="btn btn-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 10px 30px; border-radius: 25px;"><i class="bi bi-check-circle me-1"></i> Valider</button>
                <button type="submit" name="submit" value="refresh" class="btn btn-secondary" style="padding: 10px 30px; border-radius: 25px;"><i class="bi bi-arrow-clockwise me-1"></i> Rafraîchir</button>
            </div>
        </div>
    </form>

@endsection

@section('custom-scripts')
@parent {{-- Pour garder les scripts existants --}}
<!-- DataTables and jQuery UI JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<script>
    $(document).ready(function() {
        $('[data-toggle="tooltip"]').tooltip();

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
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]], // Options pour changer le nombre de lignes par page
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
