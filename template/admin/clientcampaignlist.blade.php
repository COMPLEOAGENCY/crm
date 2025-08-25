@extends('admin.blanck')

@section('title', $title)

@section('custom-css')
@parent
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<style>
    /* Styles de boutons réutilisables */
    .btn-pill { border-radius: 9999px; padding: 10px 26px; }
    .btn-gradient { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; border: none; }
    .btn-grey { background: #6c757d; color: #fff; border: none; }
    .btn-gradient:hover, .btn-grey:hover { filter: brightness(0.95); color: #fff; }
    .btn-gradient:focus, .btn-grey:focus { box-shadow: 0 0 0 0.2rem rgba(118,75,162,0.25); }
</style>
@endsection

@section('menu')
@include('admin.menu')
@endsection

@section('content')

    <div class="card mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 15px;">
        <div class="card-body py-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-bag-check-fill" style="font-size: 2.5rem; margin-right: 20px;"></i>
                <div>
                    <h1 class="mb-0" style="font-size: 2rem; font-weight: 600;">Liste des campagnes clients</h1>
                    <p class="mb-0" style="opacity: 0.9;">{{ is_countable($campaignList) ? count($campaignList) : 0 }} campagnes</p>
                </div>
            </div>
        </div>
    </div>

    @include('admin.messages')

    <form id="clientcampaign" method="POST">
        <!-- Filtres -->
        <div class="card modern-card mb-4">
            <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filtres</h5>
            </div>
            <div class="card-body">
                <div class="form-group mb-3">
                    <label for="statut">Statut</label>
                    <select class="form-control" id="statut" name="statut">
                        <option value="" {{ empty($params['statut']) ? 'selected' : '' }}>Tous</option>
                        <option value="on" {{ (isset($params['statut']) && $params['statut']==='on') ? 'selected' : '' }}>on</option>
                        <option value="off" {{ (isset($params['statut']) && $params['statut']==='off') ? 'selected' : '' }}>off</option>
                        <option value="credit_over" {{ (isset($params['statut']) && $params['statut']==='credit_over') ? 'selected' : '' }}>credit_over</option>
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label for="userid">Client</label>
                    <select class="form-control" id="userid" name="userid">
                        <option value="" {{ empty($params['userid']) ? 'selected' : '' }}>Tous</option>
                        @if(!empty($clients))
                            @foreach($clients as $u)
                                @php
                                    $clientLabel = $u->company ?: (trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) ?: ($u->email ?? ''));
                                @endphp
                                <option value="{{ $u->userId }}" {{ (isset($params['userid']) && (string)$params['userid'] === (string)$u->userId) ? 'selected' : '' }}>
                                    {{ $u->userId }} - {{ $clientLabel }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label for="crm_userid">Gestionnaire CRM</label>
                    <select class="form-control" id="crm_userid" name="crm_userid">
                        <option value="" {{ empty($params['crm_userid']) ? 'selected' : '' }}>Tous</option>
                        @if(!empty($crmUserList))
                            @foreach($crmUserList as $crm)
                                @php
                                    $crmLabel = trim(($crm->crm_user_firstname ?? '') . ' ' . ($crm->crm_user_lastname ?? ''));
                                @endphp
                                <option value="{{ $crm->crmUserId }}" {{ (isset($params['crm_userid']) && (string)$params['crm_userid'] === (string)$crm->crmUserId) ? 'selected' : '' }}>
                                    {{ $crm->crmUserId }} - {{ $crmLabel }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label for="campaignid">Campagne</label>
                    <select class="form-control" id="campaignid" name="campaignid">
                        <option value="" {{ empty($params['campaignid']) ? 'selected' : '' }}>Toutes</option>
                        @if(!empty($campaigns))
                            @foreach($campaigns as $camp)
                                <option value="{{ $camp->campaignId }}" {{ (isset($params['campaignid']) && (string)$params['campaignid'] === (string)$camp->campaignId) ? 'selected' : '' }}>
                                    {{ $camp->campaignId }} - {{ $camp->name ?? '' }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label for="type">Type</label>
                    <select class="form-control" id="type" name="type">
                        <option value="" {{ empty($params['type']) ? 'selected' : '' }}>Tous</option>
                        <option value="push" {{ (isset($params['type']) && $params['type']==='push') ? 'selected' : '' }}>Abonnements</option>
                        <option value="alert" {{ (isset($params['type']) && $params['type']==='alert') ? 'selected' : '' }}>Alertes</option>
                    </select>
                </div>
                <div class="form-group">
                    <div>
                        <button type="submit" name="submit" value="refresh" class="btn btn-gradient btn-pill me-2">
                            <i class="bi bi-search me-1"></i> Rechercher
                        </button>
                        <button type="submit" name="submit" value="refresh" class="btn btn-grey btn-pill">
                            <i class="bi bi-arrow-clockwise me-1"></i> Rafraîchir
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card modern-card mb-4">
            <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="mb-0"><i class="bi bi-bag-check-fill me-2"></i>Campagnes clients</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover dataTable" id="campaignTable">
                        <thead>
                            <tr>
                                <th>Select</th>
                                <th>Id</th>
                                <th>Création</th>
                                <th>Modification</th>
                                <th>Type</th>
                                <th>Client</th>
                                <th>Gestionnaire</th>
                                <th>Campagne / source</th>
                                <th>Dép</th>
                                <th>Filtres</th>
                                <th>Autres</th>
                                <th>Prix</th>
                                <th>Prio</th>
                                <th>Statut</th>
                                <th>Synchro B.</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                // Indexation pour accès rapide
                                $clientsIndex = [];
                                if (!empty($clients)) {
                                    foreach ($clients as $u) { $clientsIndex[$u->userId] = $u; }
                                }
                                $crmIndex = [];
                                if (!empty($crmUserList)) {
                                    foreach ($crmUserList as $crm) { $crmIndex[$crm->crmUserId] = $crm; }
                                }
                                $campaignIndex = [];
                                if (!empty($campaigns)) {
                                    foreach ($campaigns as $camp) { $campaignIndex[$camp->campaignId] = $camp; }
                                }
                            @endphp
                            @foreach($campaignList as $c)
                                @php
                                    $user = isset($clientsIndex[$c->userid ?? null]) ? $clientsIndex[$c->userid] : null;
                                    $clientLabel = '';
                                    if ($user) {
                                        $clientLabel = ($user->userId ?? '') . ' - ' . ($user->company ?? '');
                                    } else {
                                        $clientLabel = ($c->userid ?? '');
                                    }

                                    $manager = null;
                                    if ($user && isset($user->vendor_id) && isset($crmIndex[$user->vendor_id])) {
                                        $manager = $crmIndex[$user->vendor_id];
                                    }
                                    $managerLabel = $manager ? (($manager->crmUserId ?? '') . ' - ' . trim(($manager->crm_user_firstname ?? '') . ' ' . ($manager->crm_user_lastname ?? ''))) : '';

                                    // Campagnes / source
                                    $campagneSource = '';
                                    $countMetier = 0;
                                    $cidList = [];
                                    if (!empty($c->campaignid_list)) {
                                        $tmp = json_decode($c->campaignid_list, true);
                                        if (json_last_error() === JSON_ERROR_NONE && is_array($tmp)) {
                                            $cidList = $tmp;
                                        } else {
                                            // fallback CSV / séparateurs divers
                                            $cidList = array_filter(array_map('trim', preg_split('/[,;\s]+/', (string)$c->campaignid_list)));
                                        }
                                    }
                                    if (!empty($cidList)) {
                                        foreach ($cidList as $cid) {
                                            $countMetier++;
                                            $cobj = $campaignIndex[$cid] ?? null;
                                            $campagneSource .= ($cobj ? ($cobj->campaignId . ' - ' . ($cobj->name ?? '')) : ($cid)) . ' / ';
                                        }
                                        $campagneSource = rtrim($campagneSource, ' / ');
                                    } elseif (!empty($c->campaignid)) {
                                        $cobj = $campaignIndex[$c->campaignid] ?? null;
                                        $campagneSource = $cobj ? ($cobj->campaignId . ' - ' . ($cobj->name ?? '')) : $c->campaignid;
                                    } else {
                                        $campagneSource = 'Erreur Métier';
                                    }

                                    // Dép (départements)
                                    $depLabel = (empty($c->state) ? 'Tous' : $c->state);

                                    // Autres (placeholder simplifié)
                                    $autres = '';

                                    // Filtres (limitation longueur sans Str::limit)
                                    $filtersText = $c->filters ?? '';
                                    if (is_string($filtersText) && strlen($filtersText) > 500) {
                                        $filtersText = substr($filtersText, 0, 500) . '…';
                                    }

                                    // Prio (éviter notice si propriété absente)
                                    $prio = isset($c->priority) ? $c->priority : '';
                                @endphp
                                <tr>
                                    <td class="center">
                                        <input type="checkbox" name="id_array[{{ $c->usercampaignId }}]" {{ isset($params['id_array'][$c->usercampaignId]) ? 'checked' : '' }}>
                                    </td>
                                    <td>
                                        <a href="/admin/clientcampaign-add.php?usercampaignid={{ $c->usercampaignId }}">{{ $c->usercampaignId }}</a>
                                    </td>
                                    <td>{{ isset($c->timestamp) ? date('d/m/Y', $c->timestamp) : '' }}</td>
                                    <td>{{ isset($c->timestamp_update) ? date('d/m/Y', $c->timestamp_update) : '' }}</td>
                                    <td>{{ $c->type ?? '' }}</td>
                                    <td class="left">{!! $clientLabel !!}</td>
                                    <td class="left">{!! $managerLabel !!}</td>
                                    <td class="left">{!! $campagneSource !!}</td>
                                    <td class="left">{!! $depLabel !!}</td>
                                    <td class="left">{{ $filtersText }}</td>
                                    <td class="left">{!! $autres !!}</td>
                                    <td class="left"></td>
                                    <td>{{ $prio }}</td>
                                    <td class="center">{{ $c->statut ?? '' }}</td>
                                    <td class="center">{{ (!empty($user) && !empty($user->shopId)) ? ('Ok n° : ' . $user->shopId) : 'off' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card modern-card">
            <div class="card-body">
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label" for="action"><strong>Action</strong></label>
                    <div class="col-sm-10">
                        <select data-required="true" class="form-control" id="action" name="action">
                            <option value="">Choisir une action</option>
                            <option value="copy">Copier</option>
                            <option value="delete">Supprimer (soft)</option>
                            <option value="synch" disabled>Synchroniser (bientôt)</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" name="submit" value="valid" class="btn btn-gradient btn-pill"><i class="bi bi-check-circle me-1"></i> Valider</button>
                <button type="submit" name="submit" value="refresh" class="btn btn-grey btn-pill ms-2"><i class="bi bi-arrow-clockwise me-1"></i> Rafraîchir</button>
            </div>
        </div>
    </form>

@endsection

@section('custom-scripts')
@parent
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>
    $(document).ready(function() {
        // Ajout de champs de recherche dans l'en-tête
        $('#campaignTable thead th').each(function() {
            var title = $(this).text().trim();
            if (title !== 'Select') {
                $(this).append('<input style="width:100%; min-width:20px;" type="text" placeholder="search" />');
            }
        });

        var table = $('#campaignTable').DataTable({
            deferRender: true,
            scrollY: 4000,
            scrollCollapse: true,
            paging: true,
            pageLength: 20,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            dom: 'Bfrtip',
            buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
        });

        table.columns().eq(0).each(function(colIdx) {
            $('input[type="text"]', table.column(colIdx).header()).on('keyup change', function() {
                table.column(colIdx).search(this.value).draw();
            });
        });
    });
</script>
@endsection
