@extends('admin.blanck')

@section('title', $title)

@section('menu')
@include('admin.menu')
@endsection

@section('content')

    {{-- En-tête --}}
    <div class="card mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 15px;">
        <div class="card-body py-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-shield-check" style="font-size: 2.0rem; margin-right: 16px;"></i>
                <div>
                    <h1 class="mb-0" style="font-size: 1.6rem; font-weight: 600;">Liste des vérifications</h1>
                    <p class="mb-0" style="opacity: 0.9;"></p>
                </div>
            </div>
        </div>
    </div>

    {{-- Messages --}}
    @include('admin.messages')

    {{-- Formulaire et tableau --}}
    <form id="verification" method="POST">
        <div class="card modern-card mb-4">
            <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Vérifications</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Select</th>
                                <th>Id</th>
                                <th>Date</th>
                                <th>Nom</th>
                                <th>Type</th>
                                <th>Campagne</th>
                                <th>Source</th>
                                <th>Téléphone</th>
                                <th>Heures</th>
                                <th>Priorité</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($validationList ?? []) as $v)
                                <tr>
                                    <td><input type="checkbox" name="id_array[{{ $v->validationId }}]"></td>
                                    <td><a href="/admin/verification/{{ $v->validationId }}" target="_blank">{{ $v->validationId }}</a></td>
                                    <td>{{ isset($v->timestamp) ? date('d/m/Y', (int) $v->timestamp) : '' }}</td>
                                    <td>{{ $v->name ?? '' }}</td>
                                    <td>{{ $v->type ?? '' }}</td>
                                    <td>{{ $v->campaignid ?? '' }}</td>
                                    <td>{{ $v->sourceid ?? '' }}</td>
                                    <td>{{ $v->type_phone ?? '' }}</td>
                                    <td>
                                        @if(!empty($v->start_hour) || !empty($v->end_hour))
                                            {{ ($v->start_hour ?? '') }} - {{ ($v->end_hour ?? '') }}
                                        @endif
                                    </td>
                                    <td>{{ $v->order ?? '' }}</td>
                                    <td>{{ $v->statut ?? '' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center">Aucune donnée à afficher.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Panneau d'actions (placeholder) --}}
        <div class="card modern-card">
            <div class="card-body">
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label" for="action"><strong>Action</strong></label>
                    <div class="col-sm-10">
                        <select class="form-control" id="action" name="action">
                            <option value="">Choisir une action</option>
                            <option value="copy">Copier</option>
                            <option value="delete">Supprimer</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" name="submit" value="valid" class="btn btn-primary">Valider</button>
                <button type="submit" name="submit" value="refresh" class="btn btn-secondary">Rafraîchir</button>
            </div>
        </div>
    </form>

@endsection

@section('custom-css')
@parent
@endsection

@section('custom-scripts')
@parent
@endsection
