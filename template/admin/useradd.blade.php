@extends('admin.blanck')

@section('title', $title)

@section('custom-css')
@parent {{-- Pour garder les scripts existants --}}
<style>
    .modern-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
        border-radius: 10px;
        margin-bottom: 2rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .modern-header {
            padding: 1.5rem;
        }
        
        .modern-header h1 {
            font-size: 1.75rem;
        }
        
        .card-header h5 {
            font-size: 1.1rem;
        }
        
        .card-footer .btn {
            font-size: 0.9rem;
            padding: 0.4rem 0.8rem;
        }
        
        .col-md-6 {
            margin-bottom: 1.5rem;
        }
    }
    
    @media (max-width: 576px) {
        .modern-header {
            padding: 1rem;
        }
        
        .modern-header h1 {
            font-size: 1.5rem;
        }
        
        .card-footer {
            padding: 1rem;
        }
        
        .card-footer .d-flex {
            justify-content: center !important;
        }
        
        .card-footer .btn {
            width: 100%;
            margin-bottom: 0.5rem;
        }
    }
    
    /* Gap utility for flexbox */
    .gap-2 {
        gap: 0.5rem;
    }
    .modern-header h1 {
        font-size: 2rem;
        font-weight: 600;
        margin-bottom: 5px;
    }
    .modern-card {
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border: none;
        margin-bottom: 20px;
    }
    .modern-card .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px 15px 0 0;
        padding: 15px 20px;
        font-weight: 600;
    }
    .modern-card .card-body {
        padding: 20px;
    }
.modern-header p {
    opacity: 0.9;
    margin-bottom: 0;
}
.header-icon {
    font-size: 2.5rem;
    margin-right: 20px;
}
.modern-card {
    border-radius: 15px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border: none;
    margin-bottom: 20px;
}
.modern-card .card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px 15px 0 0;
    padding: 15px 20px;
    font-weight: 600;
}
.modern-card .card-body {
    padding: 20px;
}
</style>
@endsection

@section('menu')
@include('admin.menu')
@endsection

@section('content')

    {{-- Header moderne --}}

    <div class="modern-header">
        <div class="d-flex align-items-center">
            <i class="bi bi-person-gear header-icon"></i>
            <div>
                <h1>{{ $title }}</h1>
                <p>Gestion complète du compte utilisateur</p>
            </div>
        </div>
    </div>


{{-- Inclusion des messages --}}
@include('admin.messages')
    
{{-- Inclusion du menu utilisateur --}}

<form class="form-horizontal" id="user" method="POST" autocomplete="off">

        <div class="row">
            <div class="col-md-6">
                {{-- Card moderne pour les détails du compte --}}
                <div class="card modern-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-person-badge-fill me-2"></i>Détails du compte</h5>
                    </div>
                    <div class="card-body">
                @include('admin.form.select', [
                    'label' => 'Shopid (n° de compte dans la boutique)',
                    'id' => 'shopId',
                    'name' => 'shopId',
                    'options' => $shopsList ?? [],
                    'optionValue' => 'comId',
                    'optionLabel' => 'com_name',
                    'selected' => $user->shopId ?? '',
                    'defaultOption' => "Compte {$user->shopId} non lié à la boutique",
                    'attributes' => 'readonly data-live-search="true"',
                    'validationClass' => '',
                    'errorHTML' => ''
                ])
                

                @include('admin.form.select', \Classes\clean_select([
                    'label' => 'Chargé de compte',
                    'id' => 'vendor_id',
                    'name' => 'vendor_id',
                    'options' => array_map(function($crmUser) {
                        return ['value' => $crmUser->crmUserId, 'name' => "{$crmUser->crmUserId} - {$crmUser->crm_user_firstname} {$crmUser->crm_user_lastname}"];
                    }, $crmUserList),
                    'optionValue' => 'value',
                    'optionLabel' => 'name',
                    'selected' => $user->vendor_id ?? '',
                    'defaultOption' => 'Sélectionnez un chargé de compte',
                    'validationClass' => $validationMessageService->getFieldClass('vendor_id'),
                    'attributes' => 'data-live-search="true"',
                    'errorHTML' => $validationMessageService->getErrorHTML('vendor_id')
                ]))

                @include('admin.form.text-input', \Classes\clean_input_text([
                    'label' => 'Nom de la société',
                    'id' => 'company',
                    'name' => 'company',
                    'placeholder' => 'Raison sociale',                    
                    'required' => $validationService->isFieldRequired('company'),
                    'value' => $user->company ?? '',
                    'validationClass' => $validationMessageService->getFieldClass('company'),
                    'errorHTML' => $validationMessageService->getErrorHTML('company')
                ]))


                @include('admin.form.date-input', [
                    'readonly'=> true,
                    'label' => 'Date du compte',
                    'id' => '_timestamp',
                    'name' => 'timestamp',
                    'value' => isset($user->timestamp) ? date('d-m-Y', $user->timestamp) : ''
                ])

                @include('admin.form.date-input', [
                    'readonly' => true,
                    'additionalClass' => 'showdate',
                    'label' => 'Date des leads visibles en boutique',
                    'id' => 'pro_start_date',
                    'name' => 'pro_start_date',
                    'value' => isset($user->pro_start_date) ? date('d-m-Y H:i:s', $user->pro_start_date) : ''
                ])


                @include('admin.form.date-input', [
                    'readonly' => true,
                    'additionalClass' => 'showdate',
                    'label' => 'Date de bascule de la facturation',
                    'id' => 'billing_start_date',
                    'name' => 'billing_start_date',
                    'value' => isset($user->billing_start_date) ? date('d-m-Y H:i:s', $user->billing_start_date) : ''
                ])

                @include('admin.form.date-input', [
                    'readonly' => true,
                    'additionalClass' => '',
                    'label' => 'Date de dernière connexion',
                    'id' => 'timestamp_connexion',
                    'name' => 'timestamp_connexion',
                    'value' => isset($user->timestamp_connexion) ? date('d-m-Y H:i:s', $user->timestamp_connexion) : ''
                ])

                @include('admin.form.text-input', \Classes\clean_input_text([
                    'readonly' => true,
                    'label' => 'Dernière mise à jour',
                    'id' => 'last_update_timestamp',
                    'name' => 'last_update_timestamp',
                    'placeholder' => '',
                    'value' => isset($user->last_update_timestamp) ? date('d-m-Y H:i:s', $user->last_update_timestamp) : '',
                    'validationClass' => '',
                    'errorHTML' => ''
                ]))

                @include('admin.form.text-input', \Classes\clean_input_text([
                    'label' => 'Numéro de TVA',
                    'id' => 'vat_number',
                    'name' => 'vat_number',
                    'placeholder' => 'Numéro de TVA',
                    'value' => $user->vat_number ?? '',
                    'required' => $validationService->isFieldRequired('vat_number') ? 'required' : '',
                    'validationClass' => $validationMessageService->getFieldClass('vat_number'),
                    'errorHTML' => $validationMessageService->getErrorHTML('vat_number')
                ]))


                <input type="hidden" name="userid" value="{{ $user->userId ?? '' }}">

                @include('admin.form.select', \Classes\clean_select([
                    'label' => 'Type de compte',
                    'id' => 'type',
                    'name' => 'type',
                    'options' => [
                        ['value' => 'client', 'name' => 'Client'],
                        ['value' => 'provider', 'name' => 'Fournisseur'],
                        ['value' => 'admin', 'name' => 'Admin']
                    ],
                    'optionValue' => 'value',
                    'optionLabel' => 'name',
                    'selected' => $user->type ?? '',
                    'defaultOption' => false,
                    'validationClass' => $validationMessageService->getFieldClass('type'),
                    'attributes' => !empty($user->type) ? 'disabled' : '',
                    'errorHTML' => $validationMessageService->getErrorHTML('type')
                ]))

                @include('admin.form.text-input', \Classes\clean_input_text([

                    'label' => 'SIREN',
                    'id' => 'registration_number',
                    'name' => 'registration_number',
                    'placeholder' => 'SIREN',
                    'value' => $user->registration_number ?? '',
                    'required' => $validationService->isFieldRequired('registration_number'),
                    'validationClass' => $validationMessageService->getFieldClass('registration_number'),
                    'errorHTML' => $validationMessageService->getErrorHTML('registration_number')
                ]))

                @include('admin.form.text-input', \Classes\clean_input_text([
                    'label' => 'Adresse',
                    'id' => 'address',
                    'name' => 'address',
                    'placeholder' => 'Adresse',
                    'value' => $user->address ?? '',
                    'required' => $validationService->isFieldRequired('address'),
                    'validationClass' => $validationMessageService->getFieldClass('address'),
                    'errorHTML' => $validationMessageService->getErrorHTML('address')
                ]))

                @include('admin.form.text-input', \Classes\clean_input_text([
                    'label' => 'Ville',
                    'id' => 'city',
                    'name' => 'city',
                    'placeholder' => 'Ville',
                    'value' => $user->city ?? '',
                    'required' => $validationService->isFieldRequired('city'),
                    'validationClass' => $validationMessageService->getFieldClass('city'),
                    'errorHTML' => $validationMessageService->getErrorHTML('city')
                ]))

                @include('admin.form.select', \Classes\clean_select([
                    'label' => 'Pays',
                    'id' => 'country',
                    'name' => 'country',
                    'options' => [
                        ['value' => 'FR', 'name' => 'France'],
                        ['value' => 'US', 'name' => 'USA'],
                        ['value' => 'BE', 'name' => 'Belgique'],
                        ['value' => 'IT', 'name' => 'Italie'],
                        ['value' => 'ES', 'name' => 'Espagne'],
                        ['value' => 'UK', 'name' => 'UK']
                    ],
                    
                    'optionValue' => 'value',
                    'optionLabel' => 'name',
                    'attributes' => 'data-live-search="true"',
                    'selected' => $user->country ?? '',
                    'defaultOption' => false,
                    'required' => $validationService->isFieldRequired('country'),
                    'validationClass' => $validationMessageService->getFieldClass('country'),
                    'errorHTML' => $validationMessageService->getErrorHTML('country')
                ]))

                @include('admin.form.text-input', \Classes\clean_input_text([
                    'label' => 'Code Postal',
                    'id' => 'cp',
                    'name' => 'cp',
                    'placeholder' => 'Code Postal',
                    'value' => $user->cp ?? '',
                    'required' => $validationService->isFieldRequired('cp'),
                    'validationClass' => $validationMessageService->getFieldClass('cp'),
                    'errorHTML' => $validationMessageService->getErrorHTML('cp')
                ]))

                @include('admin.form.text-input', \Classes\clean_input_text([
                    'label' => 'État/Région',
                    'id' => 'state',
                    'name' => 'state',
                    'placeholder' => 'État',
                    'value' => $user->state ?? '',
                    'required' => $validationService->isFieldRequired('state'),
                    'validationClass' => $validationMessageService->getFieldClass('state'),
                    'errorHTML' => $validationMessageService->getErrorHTML('state')
                ]))

                <!-- Informations personnelles -->
                @include('admin.form.select',  \Classes\clean_select(
                    [
                    'label' => 'Titre',
                    'id' => 'civ',
                    'name' => 'civ',
                    'options' => [
                        ['value' => 'M', 'name' => 'M'],
                        ['value' => 'Mme', 'name' => 'Mme'],
                        ['value' => 'Melle', 'name' => 'Melle']
                    ],
                    'optionValue' => 'value',
                    'optionLabel' => 'name',
                    'selected' => $user->civ ?? '',
                    'attributes' => 'data-live-search="true"',
                    'defaultOption' => 'Sélectionnez un titre',
                    'required' => $validationService->isFieldRequired('civ'),
                    'validationClass' => $validationMessageService->getFieldClass('civ'),
                    'errorHTML' => $validationMessageService->getErrorHTML('civ')
                ]
                ))

                @include('admin.form.text-input', \Classes\clean_input_text([
                    'label' => 'Prénom',
                    'id' => 'first_name',
                    'name' => 'first_name',
                    'placeholder' => 'Prénom',
                    'value' => $user->first_name ?? '',
                    'required' => $validationService->isFieldRequired('first_name'),
                    'validationClass' => $validationMessageService->getFieldClass('first_name'),
                    'errorHTML' => $validationMessageService->getErrorHTML('first_name')
                ]))

                @include('admin.form.text-input', \Classes\clean_input_text([
                    'label' => 'Nom',
                    'id' => 'last_name',
                    'name' => 'last_name',
                    'placeholder' => 'Nom',
                    'value' => $user->last_name ?? '',
                    'required' => $validationService->isFieldRequired('last_name'),
                    'validationClass' => $validationMessageService->getFieldClass('last_name'),
                    'errorHTML' => $validationMessageService->getErrorHTML('last_name')
                ]))

                @include('admin.form.text-input', \Classes\clean_input_text([
                    'label' => 'Téléphone',
                    'id' => 'phone',
                    'name' => 'phone',
                    'placeholder' => 'Téléphone',
                    'value' => $user->phone ?? '',
                    'required' => $validationService->isFieldRequired('phone'),
                    'validationClass' => $validationMessageService->getFieldClass('phone'),
                    'errorHTML' => $validationMessageService->getErrorHTML('phone')
                ]))


                @include('admin.form.text-input', \Classes\clean_input_text([
                    'label' => 'Mobile',
                    'id' => 'mobile',
                    'name' => 'mobile',
                    'placeholder' => 'Mobile',
                    'value' => $user->mobile ?? '',
                    'required' => $validationService->isFieldRequired('mobile'),
                    'validationClass' => $validationMessageService->getFieldClass('mobile'),
                    'errorHTML' => $validationMessageService->getErrorHTML('mobile')
                ]))

                @include('admin.form.text-input', \Classes\clean_input_text([
                    'label' => 'Email principal',
                    'id' => 'email',
                    'name' => 'email',
                    'placeholder' => 'Email',
                    'value' => $user->email ?? '',
                    'required' => $validationService->isFieldRequired('email'),
                    'validationClass' => $validationMessageService->getFieldClass('email'),
                    'errorHTML' => $validationMessageService->getErrorHTML('email')
                ]))

                @include('admin.form.text-input', \Classes\clean_input_text([
                    'label' => 'Email secondaire',
                    'id' => 'email2',
                    'name' => 'email2',
                    'placeholder' => 'Email secondaire',
                    'value' => $user->email2 ?? '',
                    'required' => $validationService->isFieldRequired('email2'),
                    'validationClass' => $validationMessageService->getFieldClass('email2'),
                    'errorHTML' => ''
                ]))

                @include('admin.form.select', \Classes\clean_select([
                    'label' => 'Notification de vente sur Email principal',
                    'id' => 'sale_notification_email',
                    'name' => 'sale_notification_email',
                    'options' => [
                        ['value' => '1', 'name' => 'Oui'],
                        ['value' => '0', 'name' => 'Non']
                    ],
                    'optionValue' => 'value',
                    'optionLabel' => 'name',
                    'attributes' => '',
                    'required' => $validationService->isFieldRequired('sale_notification_email'),
                    'selected' => $user->sale_notification_email ?? '0',
                    'defaultOption' => false
                ]))

                @include('admin.form.select', \Classes\clean_select([
                    'label' => 'Notifier chaque vente pour Email secondaire et les méthodes de transfert email push dans @livraison mail?',
                    'id' => 'sale_notification_email2',
                    'name' => 'sale_notification_email2',
                    'options' => [
                        ['value' => '1', 'name' => 'Oui'],
                        ['value' => '0', 'name' => 'Non']
                    ],
                    'optionValue' => 'value',
                    'optionLabel' => 'name',
                    'attributes' => '',
                    'required' => $validationService->isFieldRequired('sale_notification_email2'),
                    'selected' => $user->sale_notification_email2 ?? '0',
                    'defaultOption' => false
                ]))

                <div class="form-group row">
                    <label class="col-sm-4 col-form-label" for="details">Détails</label>
                    <div class="col-sm-8">
                        <textarea class="form-control" name="details" id="details" placeholder="Détails">{{ $user->details ?? '' }}</textarea>
                    </div>
                </div>


                @include('admin.form.text-input', \Classes\clean_input_text([
                    'label' => 'Encours maximum (€)',
                    'id' => 'encours_max',
                    'name' => 'encours_max',
                    'placeholder' => 'Encours en euros',
                    'value' => $user->encours_max ?? '',
                    'required' => $validationService->isFieldRequired('encours_max'),
                    'validationClass' => $validationMessageService->getFieldClass('encours_max'),
                    'errorHTML' => $validationMessageService->getErrorHTML('encours_max')
                ]))

                @include('admin.form.text-input', \Classes\clean_input_text([
                    'label' => 'Capping mensuel global',
                    'id' => 'global_month_capping',
                    'name' => 'global_month_capping',
                    'placeholder' => 'Nombre de leads',
                    'value' => $user->global_month_capping ?? '',
                    'required' => $validationService->isFieldRequired('global_month_capping'),
                    'validationClass' => $validationMessageService->getFieldClass('global_month_capping'),
                    'errorHTML' => $validationMessageService->getErrorHTML('global_month_capping')
                ]))

                @include('admin.form.text-input', \Classes\clean_input_text([
                    'label' => 'Capping journalier global (nb lead)',
                    'id' => 'global_day_capping',
                    'name' => 'global_day_capping',
                    'placeholder' => 'Nombre de leads',
                    'value' => $user->global_day_capping ?? '',
                    'required' => $validationService->isFieldRequired('global_day_capping'),
                    'validationClass' => $validationMessageService->getFieldClass('global_day_capping'),
                    'errorHTML' => $validationMessageService->getErrorHTML('global_day_capping')
                ]))
                
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label" for="user_exclusion">
                        Exclusion de vente multiple
                        <em>Indiquer chaque compte à exclure des ventes pour le même @</em>
                    </label>
                    <div class="col-sm-8">
                        <!-- Champ caché pour s'assurer qu'une valeur vide est envoyée même si rien n'est sélectionné -->
                        <input type="hidden" name="user_exclusion[]" value="">
                        
                        <select multiple="multiple" 
                                size="10" 
                                class="selectpicker form-control {{ $validationMessageService->getFieldClass('user_exclusion') }}" 
                                id="user_exclusion" data-live-search="true"
                                name="user_exclusion[]"  >
                            @foreach($clients as $client)
                                @if($client->userId != ($user->userId ?? null))
                                    @php
                                        $currentExclusions = is_array($user->user_exclusion) 
                                            ? $user->user_exclusion 
                                            : (empty($user->user_exclusion) ? [] : json_decode($user->user_exclusion, true));
                                    @endphp
                                    <option value="{{ $client->userId }}" 
                                            {{ in_array($client->userId, $currentExclusions) ? 'selected' : '' }}>
                                        {{ $client->userId }} - {{ $client->company }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        {!! $validationMessageService->getErrorHTML('user_exclusion') !!}
                    </div>
                </div>                

                @include('admin.form.select', \Classes\clean_select([
                    'label' => 'Marque Blanche',
                    'id' => 'marque_blanche',
                    'name' => 'marque_blanche',
                    'options' => [
                        ['value' => 'no', 'name' => 'Non'],
                        ['value' => 'yes', 'name' => 'Oui']
                    ],
                    'optionValue' => 'value',
                    'optionLabel' => 'name',
                    'attributes' => '',
                    'required' => $validationService->isFieldRequired('marque_blanche'),
                    'selected' => $user->marque_blanche ?? 'no',
                    'defaultOption' => false
                ]))

                @include('admin.form.select', \Classes\clean_select([
                    'label' => 'Deversoir',
                    'id' => 'deversoir',
                    'name' => 'deversoir',
                    'options' => [
                        ['value' => 'no', 'name' => 'Non'],
                        ['value' => 'yes', 'name' => 'Oui']
                    ],
                    'optionValue' => 'value',
                    'optionLabel' => 'name',
                    'attributes' => '',
                    'required' => $validationService->isFieldRequired('deversoir'),
                    'selected' => $user->deversoir ?? 'no',  
                    'defaultOption' => false                  
                ]))
                
                @include('admin.form.select', \Classes\clean_select([
                    'label' => 'SMS Légal',
                    'id' => 'legal_sms',
                    'name' => 'legal_sms',
                    'options' => [
                        ['value' => 'on', 'name' => 'Oui'],
                        ['value' => 'off', 'name' => 'Non']
                    ],
                    'optionValue' => 'value',
                    'optionLabel' => 'name',
                    'attributes' => '',
                    'required' => $validationService->isFieldRequired('legal_sms'),
                    'selected' => $user->legal_sms ?? 'on',
                    'defaultOption' => false
                ]))

                <div class="form-group row">
                    <label class="col-sm-4 col-form-label" for="welcome_sms">SMS de bienvenue</label>
                    <div class="col-sm-8">
                        <textarea class="form-control" rows="3" maxlength="160" name="welcome_sms" id="welcome_sms">{{ $user->welcome_sms ?? '' }}</textarea>
                    </div>
                </div>
                @include('admin.form.select', \Classes\clean_select([
                    'label' => 'Statut',
                    'id' => 'statut',
                    'name' => 'statut',
                    'options' => [
                        ['value' => 'on', 'name' => 'On'],
                        ['value' => 'off', 'name' => 'Off']
                    ],
                    'optionValue' => 'value',
                    'optionLabel' => 'name',
                    'attributes' => '',
                    'required' => $validationService->isFieldRequired('statut'),
                    'selected' => $user->statut ?? 'on',
                    'defaultOption' => false
                ]))
                    </div>
                    <!-- Boutons d'action dans le card -->
                    <div class="card-footer bg-light border-top">
                        <div class="d-flex flex-wrap gap-2 justify-content-end">
                            <button type="submit" name="submit" value="valid" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Valider
                            </button>
                            <button type="submit" name="submit" value="synchro" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-repeat"></i> Synchroniser
                            </button>
                            <button type="submit" name="submit" value="delete" onclick="return confirm('Êtes-vous sûr ?');" class="btn btn-danger">
                                <i class="bi bi-trash"></i> Supprimer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @php
            if(isset($user->type) && $user->type == 'client') {
            @endphp            
                @include('admin.userdetails');
            @php
            }   
            @endphp
            
        </div>

</form>
@endsection

@section('custom-scripts')
@parent {{-- Pour garder les scripts existants --}}
<script>
$(document).ready(function() {
    $('.selectpicker').selectpicker();
    $('#country').change(function() {
        let country = $(this).val();
        $('#registration_number').attr('data-var', country);
    });
});
</script>
@endsection

