<div class="col-md-6">
    <div class="card card-default">
        <div class="card-header">
            <h5>Solde compte CRM : <span class="float_number">{{ number_format($balance['solde'] ?? 0, 2) }} €</span></h5>
            <h5>Solde affiché boutique: <span class="float_number">{{ isset($shopBalance) ? number_format($shopBalance, 2) . ' €' : 'N/C' }}</span></h5>
            <h5>Factures à payer : <span class="float_number"><a target="_blank" href="/admin/invoice/{{ $user->userId }}">{{ number_format(-($balance['invoice_unpaid_ttc'] ?? 0), 2) }} €</a></span></h5>
        </div>
        <div class="card-body">
            <h5>Crédit à facturer: <span class="float_number">{{ number_format($balance['credits_aaf'] ?? 0, 2) }} €</span></h5>
            <li>+ Crédit consommés: <span class="float_number">{{ number_format($balance['credits_ht_sale'] ?? 0, 2) }} €</span></li>
            <li>- Crédits facturés: <span class="float_number">{{ number_format(-($balance['credits_billed'] ?? 0), 2) }} €</span></li>
        </div>
    </div>

    <h3 class="pt-2">Liste des utilisateurs :</h3>
    <div class="m-3">
        @foreach($subUsers as $subUser)
        <li><a target="_blank" href="/admin/user-sub-add/{{ $subUser->user_subId }}">{{ $subUser->first_name }} {{ $subUser->last_name }}</a></li>
        @endforeach
    </div>

    <h3 class="pt-2">Liste des 10 dernières ventes :</h3>
    @foreach($recentSales as $sale)
    <li><a target="_blank" href="/admin/sale-add/{{ $sale->saleId }}">Vente : {{ $sale->saleId }} - {{ date('d/m/Y H:i:s', $sale->timestamp) }}</a></li>
    @endforeach
</div>