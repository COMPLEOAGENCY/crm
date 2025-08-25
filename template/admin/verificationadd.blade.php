@extends('admin.blanck')

@section('title', $title)

@section('custom-css')
@parent
<style>
    .modern-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        padding: 1.5rem;
        border-radius: 10px;
        margin-bottom: 1.5rem;
    }
</style>
@endsection

@section('menu')
@include('admin.menu')
@endsection

@section('content')
<div class="container-fluid">
    <div class="modern-header">
        <h1 class="h4 mb-0">@yield('title')</h1>
        <small class="opacity-75">Formulaire d'ajout/modification d'une vérification</small>
    </div>

    {{-- Messages globaux --}}
    @include('admin.messages')

    <form method="POST">
        @if(!empty($validation->validationId))
            <input type="hidden" name="validationid" value="{{ $validation->validationId }}">
        @endif

        <div class="card modern-card mb-3">
            <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="mb-0">Vérification</h5>
            </div>
            <div class="card-body">
                @php
                    $statutOptions = [
                        ['value' => 'none', 'name' => 'none'],
                        ['value' => 'reject', 'name' => 'reject'],
                        ['value' => 'valid', 'name' => 'valid'],
                        ['value' => 'deversoir', 'name' => 'deversoir'],
                        ['value' => 'pending', 'name' => 'pending'],
                    ];
                    $statutOptions2 = [
                        
                        ['value' => 'pending', 'name' => 'pending'],
                        ['value' => 'raw', 'name' => 'raw'],
                        ['value' => 'reject', 'name' => 'reject'],
                        ['value' => 'valid', 'name' => 'valid'],
                        ['value' => 'deversoir', 'name' => 'deversoir'],

                    ];                    
                    $orderOptions = [];
                    for ($i = 1; $i <= 9; $i++) {
                        $orderOptions[] = ['value' => (string)$i, 'name' => (string)$i];
                    }
                @endphp
                @include('admin.form.text-input', \Classes\clean_input_text([
                    'label' => "Nom de la validation",
                    'id' => 'name',
                    'name' => 'name',
                    'placeholder' => 'Nom',
                    'value' => $validation->name ?? '',
                    'required' => $validationService->isFieldRequired('name'),
                    'validationClass' => $validationMessageService->getFieldClass('name'),
                    'errorHTML' => $validationMessageService->getErrorHTML('name')
                ]))

                @include('admin.form.select', \Classes\clean_select([
                    'label' => 'Type de validation',
                    'id' => 'type',
                    'name' => 'type',
                    'options' => [
                        ['value' => 'none', 'name' => 'Aucune'],
                        ['value' => 'tel', 'name' => 'Eligibilité téléphonique'],
                        ['value' => 'audio', 'name' => 'Audio'],
                        ['value' => 'starleads', 'name' => 'Starleads'],
                        ['value' => 'sms', 'name' => 'SMS'],
                        ['value' => 'hlr', 'name' => 'HLR HLRLOOKUP.COM'],
                        ['value' => 'hlr2', 'name' => 'HLR NEUTRINO'],
                        ['value' => 'hlr_all', 'name' => 'HLR DOUBLE CHECK'],
                        ['value' => 'ip', 'name' => 'Vérification IP BLOCKLIST'],
                        ['value' => 'n8n', 'name' => 'Vérification n8n'],
                    ],
                    'optionValue' => 'value',
                    'optionLabel' => 'name',
                    'selected' => $validation->type ?? 'none',
                    'defaultOption' => false,
                    'attributes' => 'data-live-search="true"',
                    'required' => $validationService->isFieldRequired('type'),
                    'validationClass' => $validationMessageService->getFieldClass('type'),
                    'errorHTML' => $validationMessageService->getErrorHTML('type')
                ]))

                @include('admin.form.select', \Classes\clean_select([
                    'label' => 'Campagne de lead',
                    'id' => 'campaignid',
                    'name' => 'campaignid',
                    'options' => [ ['value' => 'all', 'name' => 'Toutes les campagnes'] ],
                    'optionValue' => 'value',
                    'optionLabel' => 'name',
                    'selected' => $validation->campaignid ?? 'all',
                    'defaultOption' => false,
                    'attributes' => 'data-live-search="true"',
                    'required' => $validationService->isFieldRequired('campaignid'),
                    'validationClass' => $validationMessageService->getFieldClass('campaignid'),
                    'errorHTML' => $validationMessageService->getErrorHTML('campaignid')
                ]))

                @include('admin.form.select', \Classes\clean_select([
                    'label' => 'Sélection des sources',
                    'id' => 'sourceid',
                    'name' => 'sourceid',
                    'options' => [ ['value' => 'all', 'name' => 'Toutes les sources'] ],
                    'optionValue' => 'value',
                    'optionLabel' => 'name',
                    'selected' => $validation->sourceid ?? 'all',
                    'defaultOption' => false,
                    'attributes' => 'data-live-search="true"',
                    'required' => $validationService->isFieldRequired('sourceid'),
                    'validationClass' => $validationMessageService->getFieldClass('sourceid'),
                    'errorHTML' => $validationMessageService->getErrorHTML('sourceid')
                ]))

                @include('admin.form.select', \Classes\clean_select([
                    'label' => 'Sélection du téléphone',
                    'id' => 'type_phone',
                    'name' => 'type_phone',
                    'options' => [
                        ['value' => 'all', 'name' => 'Tous'],
                        ['value' => 'fixe', 'name' => 'Fixes'],
                        ['value' => 'mobile', 'name' => 'Mobiles'],
                    ],
                    'optionValue' => 'value',
                    'optionLabel' => 'name',
                    'selected' => $validation->type_phone ?? 'all',
                    'defaultOption' => false,
                    'attributes' => 'data-live-search="true"',
                    'required' => $validationService->isFieldRequired('type_phone'),
                    'validationClass' => $validationMessageService->getFieldClass('type_phone'),
                    'errorHTML' => $validationMessageService->getErrorHTML('type_phone')
                ]))

                @include('admin.form.select', \Classes\clean_select([
                    'label' => 'Statut initial du lead',
                    'id' => 'start_lead_statut',
                    'name' => 'start_lead_statut',
                    'options' => $statutOptions2,
                    'optionValue' => 'value',
                    'optionLabel' => 'name',
                    'selected' => $validation->start_lead_statut ?? 'pending',
                    'defaultOption' => false,
                    'attributes' => 'data-live-search="true"',
                    'required' => $validationService->isFieldRequired('start_lead_statut'),
                    'validationClass' => $validationMessageService->getFieldClass('start_lead_statut'),
                    'errorHTML' => $validationMessageService->getErrorHTML('start_lead_statut')
                ]))

                @include('admin.form.text-input', \Classes\clean_input_text([
                    'label' => 'Message audio',
                    'id' => 'audio_file',
                    'name' => 'audio_file',
                    'placeholder' => 'audio_file',
                    'value' => $validation->audio_file ?? '',
                    'required' => $validationService->isFieldRequired('audio_file'),
                    'validationClass' => $validationMessageService->getFieldClass('audio_file'),
                    'errorHTML' => $validationMessageService->getErrorHTML('audio_file')
                ]))

                @include('admin.form.text-input', \Classes\clean_input_text([
                    'label' => 'Message audio de validation',
                    'id' => 'audio_file_valid',
                    'name' => 'audio_file_valid',
                    'placeholder' => 'audio_file_valid',
                    'value' => $validation->audio_file_valid ?? '',
                    'required' => $validationService->isFieldRequired('audio_file_valid'),
                    'validationClass' => $validationMessageService->getFieldClass('audio_file_valid'),
                    'errorHTML' => $validationMessageService->getErrorHTML('audio_file_valid')
                ]))

                @include('admin.form.text-input', \Classes\clean_input_text([
                    'label' => 'Message audio de refus',
                    'id' => 'audio_file_unvalid',
                    'name' => 'audio_file_unvalid',
                    'placeholder' => 'audio_file_unvalid',
                    'value' => $validation->audio_file_unvalid ?? '',
                    'required' => $validationService->isFieldRequired('audio_file_unvalid'),
                    'validationClass' => $validationMessageService->getFieldClass('audio_file_unvalid'),
                    'errorHTML' => $validationMessageService->getErrorHTML('audio_file_unvalid')
                ]))

                @include('admin.form.text-input', \Classes\clean_input_text([
                    'label' => "Message audio d'erreur",
                    'id' => 'audio_file_error',
                    'name' => 'audio_file_error',
                    'placeholder' => 'audio_file_error',
                    'value' => $validation->audio_file_error ?? '',
                    'required' => $validationService->isFieldRequired('audio_file_error'),
                    'validationClass' => $validationMessageService->getFieldClass('audio_file_error'),
                    'errorHTML' => $validationMessageService->getErrorHTML('audio_file_error')
                ]))

                @include('admin.form.number-input', [
                    'label' => 'Heure de départ des validations (24H)',
                    'id' => 'start_hour',
                    'name' => 'start_hour',
                    'placeholder' => '0',
                    'value' => $validation->start_hour ?? '',
                    'validationClass' => $validationMessageService->getFieldClass('start_hour'),
                    'errorHTML' => $validationMessageService->getErrorHTML('start_hour')
                ])

                @include('admin.form.number-input', [
                    'label' => 'Heure de fin des validations (24H)',
                    'id' => 'end_hour',
                    'name' => 'end_hour',
                    'placeholder' => '24',
                    'value' => $validation->end_hour ?? '',
                    'validationClass' => $validationMessageService->getFieldClass('end_hour'),
                    'errorHTML' => $validationMessageService->getErrorHTML('end_hour')
                ])

                @include('admin.form.number-input', [
                    'label' => 'Nombre maximum de validation',
                    'id' => 'max_nb',
                    'name' => 'max_nb',
                    'placeholder' => '1',
                    'value' => $validation->max_nb ?? '',
                    'validationClass' => $validationMessageService->getFieldClass('max_nb'),
                    'errorHTML' => $validationMessageService->getErrorHTML('max_nb')
                ])

                @include('admin.form.number-input', [
                    'label' => 'Délais entre chaque validation (mn)',
                    'id' => 'min_delay',
                    'name' => 'min_delay',
                    'placeholder' => '0',
                    'value' => $validation->min_delay ?? '',
                    'validationClass' => $validationMessageService->getFieldClass('min_delay'),
                    'errorHTML' => $validationMessageService->getErrorHTML('min_delay')
                ])

                @include('admin.form.number-input', [
                    'label' => 'Délais de première validation',
                    'id' => 'first_delay',
                    'name' => 'first_delay',
                    'placeholder' => '0',
                    'value' => $validation->first_delay ?? '',
                    'validationClass' => $validationMessageService->getFieldClass('first_delay'),
                    'errorHTML' => $validationMessageService->getErrorHTML('first_delay')
                ])

                @include('admin.form.number-input', [
                    'label' => 'Scoring de la validation [0,+1.0]',
                    'id' => 'valid_scoring_action',
                    'name' => 'valid_scoring_action',
                    'placeholder' => '+0.0',
                    'value' => $validation->valid_scoring_action ?? '',
                    'validationClass' => $validationMessageService->getFieldClass('valid_scoring_action'),
                    'errorHTML' => $validationMessageService->getErrorHTML('valid_scoring_action')
                ])

                @include('admin.form.select', \Classes\clean_select([
                    'label' => "Action sur le statut du lead de la validation",
                    'id' => 'valid_scoring_statut',
                    'name' => 'valid_scoring_statut',
                    'options' => $statutOptions,
                    'optionValue' => 'value',
                    'optionLabel' => 'name',
                    'selected' => $validation->valid_scoring_statut ?? 'none',
                    'defaultOption' => false,
                    'attributes' => 'data-live-search="true"',
                    'required' => $validationService->isFieldRequired('valid_scoring_statut'),
                    'validationClass' => $validationMessageService->getFieldClass('valid_scoring_statut'),
                    'errorHTML' => $validationMessageService->getErrorHTML('valid_scoring_statut')
                ]))

                @include('admin.form.number-input', [
                    'label' => 'Scoring refus de mise en relation [0,+1.0]',
                    'id' => 'unvalid_scoring_action',
                    'name' => 'unvalid_scoring_action',
                    'placeholder' => '+0.0',
                    'value' => $validation->unvalid_scoring_action ?? '',
                    'validationClass' => $validationMessageService->getFieldClass('unvalid_scoring_action'),
                    'errorHTML' => $validationMessageService->getErrorHTML('unvalid_scoring_action')
                ])

                @include('admin.form.select', \Classes\clean_select([
                    'label' => 'Action sur le statut du refus de mise en relation',
                    'id' => 'unvalid_scoring_statut',
                    'name' => 'unvalid_scoring_statut',
                    'options' => $statutOptions,
                    'optionValue' => 'value',
                    'optionLabel' => 'name',
                    'selected' => $validation->unvalid_scoring_statut ?? 'none',
                    'defaultOption' => false,
                    'attributes' => 'data-live-search="true"',
                    'required' => $validationService->isFieldRequired('unvalid_scoring_statut'),
                    'validationClass' => $validationMessageService->getFieldClass('unvalid_scoring_statut'),
                    'errorHTML' => $validationMessageService->getErrorHTML('unvalid_scoring_statut')
                ]))

                @include('admin.form.number-input', [
                    'label' => "Scoring de l'échec de la validation [0,+1.0]",
                    'id' => 'failed_scoring_action',
                    'name' => 'failed_scoring_action',
                    'placeholder' => '+0.0',
                    'value' => $validation->failed_scoring_action ?? '',
                    'validationClass' => $validationMessageService->getFieldClass('failed_scoring_action'),
                    'errorHTML' => $validationMessageService->getErrorHTML('failed_scoring_action')
                ])

                @include('admin.form.select', \Classes\clean_select([
                    'label' => "Action sur le statut de l'échec de la validation",
                    'id' => 'failed_scoring_statut',
                    'name' => 'failed_scoring_statut',
                    'options' => $statutOptions,
                    'optionValue' => 'value',
                    'optionLabel' => 'name',
                    'selected' => $validation->failed_scoring_statut ?? 'none',
                    'defaultOption' => false,
                    'attributes' => 'data-live-search="true"',
                    'required' => $validationService->isFieldRequired('failed_scoring_statut'),
                    'validationClass' => $validationMessageService->getFieldClass('failed_scoring_statut'),
                    'errorHTML' => $validationMessageService->getErrorHTML('failed_scoring_statut')
                ]))

                @include('admin.form.number-input', [
                    'label' => 'Scoring du rejet [0,+1.0]',
                    'id' => 'reject_scoring_action',
                    'name' => 'reject_scoring_action',
                    'placeholder' => '0.0',
                    'value' => $validation->reject_scoring_action ?? '',
                    'validationClass' => $validationMessageService->getFieldClass('reject_scoring_action'),
                    'errorHTML' => $validationMessageService->getErrorHTML('reject_scoring_action')
                ])

                @include('admin.form.select', \Classes\clean_select([
                    'label' => 'Action sur le statut du rejet',
                    'id' => 'reject_scoring_statut',
                    'name' => 'reject_scoring_statut',
                    'options' => $statutOptions,
                    'optionValue' => 'value',
                    'optionLabel' => 'name',
                    'selected' => $validation->reject_scoring_statut ?? 'none',
                    'defaultOption' => false,
                    'attributes' => 'data-live-search="true"',
                    'required' => $validationService->isFieldRequired('reject_scoring_statut'),
                    'validationClass' => $validationMessageService->getFieldClass('reject_scoring_statut'),
                    'errorHTML' => $validationMessageService->getErrorHTML('reject_scoring_statut')
                ]))

                @include('admin.form.select', \Classes\clean_select([
                    'label' => 'Priorité [1-9]',
                    'id' => 'order',
                    'name' => 'order',
                    'options' => $orderOptions,
                    'optionValue' => 'value',
                    'optionLabel' => 'name',
                    'selected' => (string)($validation->order ?? ''),
                    'defaultOption' => false,
                    'attributes' => 'data-live-search="true"',
                    'required' => $validationService->isFieldRequired('order'),
                    'validationClass' => $validationMessageService->getFieldClass('order'),
                    'errorHTML' => $validationMessageService->getErrorHTML('order')
                ]))

                @include('admin.form.select', \Classes\clean_select([
                    'label' => 'Statut',
                    'id' => 'statut',
                    'name' => 'statut',
                    'options' => [
                        ['value' => 'on', 'name' => 'on'],
                        ['value' => 'off', 'name' => 'off'],
                    ],
                    'optionValue' => 'value',
                    'optionLabel' => 'name',
                    'selected' => $validation->statut ?? 'on',
                    'defaultOption' => false,
                    'attributes' => 'data-live-search="true"',
                    'required' => $validationService->isFieldRequired('statut'),
                    'validationClass' => $validationMessageService->getFieldClass('statut'),
                    'errorHTML' => $validationMessageService->getErrorHTML('statut')
                ]))

                @include('admin.form.text-input', \Classes\clean_input_text([
                    'label' => 'URL du webhook n8n',
                    'id' => 'api_url',
                    'name' => 'api_url',
                    'placeholder' => 'https://votre-instance-n8n.com/webhook/...',
                    'value' => $validation->api_url ?? '',
                    'required' => $validationService->isFieldRequired('api_url'),
                    'validationClass' => $validationMessageService->getFieldClass('api_url'),
                    'errorHTML' => $validationMessageService->getErrorHTML('api_url')
                ]))
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label"></label>
                    <div class="col-sm-8">
                        <small class="form-text text-muted">Uniquement nécessaire pour les validations de type n8n</small>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" name="submit" value="valid" class="btn btn-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 10px 30px; border-radius: 25px;">
                    Valider
                </button>
                <button type="button" class="btn btn-secondary" style="padding: 10px 30px; border-radius: 25px;" onclick="location.reload()">Rafraîchir</button>
            </div>
        </div>
    </form>
</div>
@endsection

@section('custom-scripts')
@parent
<script>
$(document).ready(function() {
    $('.selectpicker').selectpicker();
});
</script>
@endsection
