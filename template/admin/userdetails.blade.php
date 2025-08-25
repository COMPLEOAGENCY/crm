<div class="col-md-6">
    <div class="card modern-card">
        <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <h5 class="mb-0"><i class="bi bi-calculator-fill me-2"></i>Comptabilité</h5>
        </div>
        <div class="card-body">
            <h5>Solde crédit CRM Total : <span class="float_number">{{ number_format($balance['solde'] ?? 0, 2) }} €</span></h5>
            <h5>Solde crédit SHOP Total : <span class="float_number">{{ isset($shopBalance) ? number_format($shopBalance, 2) . ' €' : '0.00 €' }}</span></h5>
            <h5>Solde facturation comptable : <span class="float_number"><a target="_blank" href="/admin/invoice/{{ $user->userId }}">{{ number_format(-($balance['invoice_unpaid_ttc'] ?? 0), 2) }} €</a></span></h5>
            <hr>
            <h5>Crédit à facturer : <span class="float_number">{{ number_format($balance['credits_aaf'] ?? 0, 2) }} €</span></h5>
            <ul class="list-unstyled ms-3">
                <li>+ Crédit consommés : <span class="float_number">{{ number_format($balance['credits_ht_sale'] ?? 0, 2) }} €</span></li>
                <li>- Crédits facturés : <span class="float_number">{{ number_format(-($balance['credits_billed'] ?? 0), 2) }} €</span></li>
            </ul>
        </div>
    </div>

    <div class="card modern-card mt-3">
        <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <h5 class="mb-0"><i class="bi bi-people-fill me-2"></i>Liste des utilisateurs</h5>
        </div>
        <div class="card-body">
            <ul class="list-unstyled">
                @if(isset($subUsers) && count($subUsers) > 0)
                    @foreach($subUsers as $subUser)
                    <li class="mb-2">
                        <a target="_blank" href="/admin/user-sub-add.php?user_subid={{ $subUser->user_subId }}">{{ $subUser->first_name }} {{ $subUser->last_name }}</a>
                        @if(!empty($subUser->shopId))
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <a target="_blank" href="{{ env('URL_SHOP') }}/list?user={{ $subUser->shopId }}&hash={{ md5($subUser->shopId . 'salt') }}">> Connexion</a>
                        @endif
                    </li>
                    @endforeach
                @else
                    <li class="text-muted">Aucun sous-utilisateur</li>
                @endif
            </ul>
            <a class="btn btn-primary" href="/admin/user-sub-add.php?userid={{ $user->userId ?? '' }}" target="_blank">+ ajouter un utilisateur</a>
        </div>
    </div>

    <div class="card modern-card mt-3">
        <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <h5 class="mb-0"><i class="bi bi-cart-fill me-2"></i>Liste des commandes clients</h5>
        </div>
        <div class="card-body">
            @if(isset($commands) && is_array($commands))
                <ul class="list-unstyled">
                @foreach($commands as $command)
                @php
                    $css1 = '';
                    $css2 = '';
                    $add_texte1 = '';
                    if($command['status'] == 'off') {
                        $css1 = 'color:orange;';
                        $css2 = 'color:orange;';
                        $add_texte1 = ' (Off)';
                    }
                    if($command['deleted']) {
                        $css1 = 'color:red;';
                        $css2 = 'color:red;text-decoration: line-through;';
                        $add_texte1 = ' (Archivée)';
                    }
                @endphp
                <li class="mb-2">
                    <span style="{{ $css1 }}">
                        <a style="{{ $css2 }}" target="_blank" href="/admin/clientcampaign-add.php?usercampaignid={{ $command['campaignId'] }}">
                            Commande n° {{ $command['campaignId'] }} - {{ implode(', ', $command['products']) }} - {{ $command['fullName'] }}
                            @if(!empty($command['reference']))
                                - {{ $command['reference'] }}
                            @endif
                        </a>
                        {{ $add_texte1 }}
                    </span>
                    @if(!empty($command['webservices']))
                        <ul class="list-unstyled ms-3">
                        @foreach($command['webservices'] as $ws)
                            @php
                                $ws_style = "";
                                $ws_add_texte = "";
                                if($ws['status'] == 'Off') {
                                    $ws_style = "color:orange";
                                    $ws_add_texte = " (Off)";
                                }
                            @endphp
                            <li>
                                <span class="webservice_line" style="font-size:12px;{{ $ws_style }}">
                                    WS n° {{ $ws['webserviceId'] }} - {{ date('d-m-Y', $ws['timestamp'] ?? time()) }} - Type: {{ $ws['type'] }}&nbsp;
                                    {{ empty($ws['start_date']) || $ws['start_date'] == 'non précisée' ? "Début non précisé" : "Début le : " . $ws['start_date'] }} / 
                                    {{ empty($ws['end_date']) || $ws['end_date'] == 'non précisée' ? "Fin non précisée" : "Fin le : " . $ws['end_date'] }} 
                                    Fichier : {{ $ws['file'] }}{{ $ws_add_texte }}
                                </span>
                            </li>
                        @endforeach
                        </ul>
                    @endif
                </li>
                @endforeach
                </ul>
            @endif
            <a class="btn btn-primary" href="/admin/clientcampaign-add.php?userid={{ $user->userId ?? '' }}" target="_blank">+ ajouter une commande</a>
        </div>
    </div>

    <div class="card modern-card mt-3">
        <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <h5 class="mb-0"><i class="bi bi-graph-up-arrow me-2"></i>Liste des 10 dernières ventes</h5>
        </div>
        <div class="card-body">
            <ul class="list-unstyled">
                @foreach($recentSales as $sale)
                <li class="mb-2"><a target="_blank" href="/admin/sale-add.php?saleid={{ $sale->saleId }}">Vente : {{ $sale->saleId }} - {{ date('d/m/Y H:i:s', $sale->timestamp) }}</a></li>
                @endforeach
            </ul>
        </div>
    </div>
</div>