@extends('admin.blanck')

@section('title', 'Test BalanceService - User #' . $userid)

@section('custom-css')
@parent {{-- Pour garder les scripts existants --}}
<!-- Styles personnalisés pour la page de test -->
@endsection

@section('menu')
@include('admin.menu')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="page-title">Test BalanceService - User #{{ $userid }}</h1>
        </div>
    </div>
    
    @if($user)
        <div class="alert alert-info">
            <strong>Utilisateur:</strong> {{ $user->firstName }} {{ $user->lastName }} ({{ $user->email }})
        </div>
    @endif
    
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="card modern-card">
                <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <h3 class="mb-0"><i class="bi bi-clock-history me-2"></i>Fonction Legacy (get_solde_details)</h3>
                </div>
                <div class="card-body">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Champ</th>
                                <th>Valeur</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($legacyDetails as $key => $value)
                            <tr>
                                <td>{{ $key }}</td>
                                <td>{{ number_format($value, 2, '.', ' ') }} €</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-3">
            <div class="card modern-card">
                <div class="card-header" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white;">
                    <h3 class="mb-0"><i class="bi bi-gear-fill me-2"></i>BalanceService (nouvelle architecture)</h3>
                </div>
                <div class="card-body">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Champ</th>
                                <th>Valeur</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($serviceDetails as $key => $value)
                            <tr>
                                <td>{{ $key }}</td>
                                <td>{{ number_format($value, 2, '.', ' ') }} €</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card modern-card mt-4">
        <div class="card-header" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
            <h3 class="mb-0"><i class="bi bi-arrows-diff me-2"></i>Comparaison détaillée</h3>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Champ</th>
                        <th>Legacy</th>
                        <th>Service</th>
                        <th>Différence</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($comparison as $row)
                    <tr class="{{ !$row['match'] ? 'table-danger' : '' }}">
                        <td>{{ $row['field'] }}</td>
                        <td>{{ number_format($row['legacy'], 2, '.', ' ') }}</td>
                        <td>{{ number_format($row['service'], 2, '.', ' ') }}</td>
                        <td>{{ number_format($row['difference'], 2, '.', ' ') }}</td>
                        <td>
                            @if($row['match'])
                                <span class="badge bg-success">✅ OK</span>
                            @else
                                <span class="badge bg-danger">❌ Différent</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="alert {{ $allMatch ? 'alert-success' : 'alert-danger' }} mb-4">
        @if($allMatch)
            <h4>✅ Les valeurs correspondent parfaitement!</h4>
            <p>Le BalanceService retourne les mêmes valeurs que la fonction legacy.</p>
        @else
            <h4>❌ Des différences subsistent</h4>
            <p>Vérifier le calcul dans BalanceService.</p>
        @endif
    </div>
    
    <div class="card modern-card mb-4">
        <div class="card-header" style="background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%); color: white;">
            <h5 class="mb-0"><i class="bi bi-link-45deg me-2"></i>Liens de test</h5>
        </div>
        <div class="card-body">
        <a href="/admin/user-add.php?userid={{ $userid }}" target="_blank" class="btn btn-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 10px 30px; border-radius: 25px; text-decoration: none; display: inline-block;"><i class="bi bi-clock-history me-1"></i> Page Legacy</a>
        <a href="/admin/useradd/{{ $userid }}" target="_blank" class="btn btn-gradient" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border: none; padding: 10px 30px; border-radius: 25px; text-decoration: none; display: inline-block;"><i class="bi bi-check2-circle me-1"></i> Page Migrée</a>
        <a href="/admin/test/balance/{{ $userid }}" class="btn btn-secondary" style="padding: 10px 30px; border-radius: 25px; text-decoration: none; display: inline-block;"><i class="bi bi-arrow-clockwise me-1"></i> Rafraîchir</a>
        </div>
    </div>

{{-- Section Ventes --}}
@if(isset($sales) && count($sales) > 0)
<div class="card modern-card mt-4">
    <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
        <h3 class="mb-0"><i class="bi bi-cart-check-fill me-2"></i>Dernières ventes</h3>
    </div>
    <div class="card-body">
        <ul class="list-unstyled">
            @foreach($sales as $sale)
            <li class="mb-2">
                <a target="_blank" href="/admin/lead-add.php?leadid={{ $sale['leadid'] }}">Lead n° {{ $sale['leadid'] }}</a> - 
                <a target="_blank" href="/admin/sale-add.php?saleid={{ $sale['saleId'] }}">
                    @if($sale['refund_statut'] == 'valid')
                        <span class="text-danger">Vente annulée:</span> {{ $sale['saleId'] }}
                    @else
                        Vente : {{ $sale['saleId'] }}
                    @endif
                    - {{ date('d/m/Y H:i:s', $sale['timestamp']) }}
                </a><br/>
                {{ $sale['user']['userId'] }}-{{ $sale['user']['company'] }} 
                {{ $sale['source']['sourceId'] }}-{{ $sale['source']['name'] }} 
                Pdt Vte : {{ $sale['sale_weight'] }} 
                CA : {{ $sale['price'] }} €
            </li>
            @endforeach
        </ul>
    </div>
</div>
@endif

{{-- Section Webservices --}}
@if(isset($webservices) && count($webservices) > 0)
<div class="card modern-card mt-4">
    <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
        <h3 class="mb-0"><i class="bi bi-cloud-arrow-up-fill me-2"></i>Webservices (Transferts)</h3>
    </div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Vente ID</th>
                    <th>Date transfert</th>
                    <th>Méthode</th>
                    <th>Résultat</th>
                </tr>
            </thead>
            <tbody>
                @foreach($webservices as $ws)
                <tr>
                    <td>{{ $ws['saleId'] }}</td>
                    <td>{{ date('d/m/Y H:i:s', $ws['timestamp_transfert']) }}</td>
                    <td>{{ $ws['method'] }}</td>
                    <td>{{ $ws['result'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
