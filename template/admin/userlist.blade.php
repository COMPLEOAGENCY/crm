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
<div class="col-12 mt-3 mb-5 page-header">
    <h1>{{ $title }}</h1>
</div>

{{-- Inclusion des messages --}}
@include('admin.messages')

{{-- Inclusion du menu utilisateur --}}
@include('admin.userlistmenu')

<form id="user" class="col-12" method="POST">
    <div>
        <table class="table table-striped table-bordered dataTable" id="example">
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
                        <td><a href="/admin/user-add.php?userid={{ $user->userId }}">{{ $user->userId }}</a></td>
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

        <!-- Panel d'actions -->
        <div class="card mt-4">
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
                <button type="submit" name="submit" value="valid" class="btn btn-primary">Valider</button>
                <button type="submit" name="submit" value="refresh" class="btn btn-light">Rafraîchir</button>
            </div>
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
