@extends('admin.blanck')

@section('title', $title)

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Menu latéral -->
        <div class="col-md-3 col-lg-2">
            <div class="position-sticky" style="top: 20px;">
                <div class="list-group shadow-sm">
                    <div class="list-group-item list-group-item-primary">Navigation</div>
                    <a href="#general" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        Informations Générales
                        <i class="fas fa-info-circle"></i>
                    </a>
                    <a href="#parametres" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        Paramètres
                        <i class="fas fa-cogs"></i>
                    </a>
                    <a href="#exemples" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        Exemples
                        <i class="fas fa-code"></i>
                    </a>
                    <div class="list-group-item list-group-item-primary">Modèles</div>
                    @foreach($models as $modelName => $modelInfo)
                        <a href="#model-{{ strtolower($modelName) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            {{ $modelName }}
                            <span class="badge bg-primary rounded-pill">{{ is_array($modelInfo['schema']) ? count($modelInfo['schema']) : 0 }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Contenu principal -->
        <div class="col-md-9 col-lg-10">
            <div class="pb-2 mb-4 border-bottom">
                <h1>Documentation API v{{ $version }}</h1>
                <p class="text-muted">Framework utilisé : <a href="https://github.com/COMPLEOAGENCY/Framework" target="_blank">COMPLEOAGENCY Framework <i class="fas fa-external-link-alt"></i></a></p>
            </div>
            
            <div id="general" class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0">Informations Générales</h2>
                </div>
                <div class="card-body">
                    <h3>URL de Base</h3>
                    <pre class="bg-light p-3 rounded"><code>{{ $baseUrl }}</code></pre>

                    <h3>Authentification</h3>
                    <p>{{ $authentication['description'] }}</p>
                    <pre class="bg-light p-3 rounded"><code>Authorization: Bearer {votre-token}</code></pre>

                    <h3>Codes d'Erreur</h3>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Code</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($errors as $code => $description)
                                <tr>
                                    <td><code>{{ $code }}</code></td>
                                    <td>{{ $description }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="parametres" class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0">Paramètres de l'API</h2>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h3>Paramètres de pagination</h3>
                            <ul class="list-group mb-4">
                                <li class="list-group-item"><code>page</code> : Numéro de la page (défaut: 1)</li>
                                <li class="list-group-item"><code>limit</code> : Nombre d'éléments par page (défaut: 1000)</li>
                            </ul>

                            <h3>Paramètres de tri</h3>
                            <ul class="list-group mb-4">
                                <li class="list-group-item"><code>sort</code> : Champ sur lequel trier</li>
                                <li class="list-group-item"><code>order</code> : Direction du tri (asc/desc)</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h3>Paramètres de filtrage</h3>
                            <div class="alert alert-info">
                                Le paramètre <code>filter</code> accepte un objet JSON avec les options suivantes :
                            </div>
                            <ul class="list-group mb-4">
                                <li class="list-group-item">Filtre simple : <code>{"champ": "valeur"}</code></li>
                                <li class="list-group-item">Filtre avec opérateur : <code>{"champ": {"operator": "OPERATEUR", "value": "VALEUR"}}</code></li>
                            </ul>
                            
                            <h4>Opérateurs supportés</h4>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <tbody>
                                        <tr><td><code>=</code></td><td>Égal à (défaut)</td></tr>
                                        <tr><td><code>></code></td><td>Supérieur à</td></tr>
                                        <tr><td><code><</code></td><td>Inférieur à</td></tr>
                                        <tr><td><code>>=</code></td><td>Supérieur ou égal à</td></tr>
                                        <tr><td><code><=</code></td><td>Inférieur ou égal à</td></tr>
                                        <tr><td><code>!=</code></td><td>Différent de</td></tr>
                                        <tr><td><code>LIKE</code></td><td>Recherche avec wildcards (%)</td></tr>
                                        <tr><td><code>IN</code></td><td>Dans une liste de valeurs</td></tr>
                                        <tr><td><code>IS</code></td><td>Pour les valeurs NULL</td></tr>
                                        <tr><td><code>IS NOT</code></td><td>Pour les valeurs non NULL</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="exemples" class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0">Exemples d'utilisation</h2>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>1. Liste simple</h4>
                            <pre class="bg-light p-3 rounded"><code>GET /apiv2/administration</code></pre>

                            <h4>2. Pagination</h4>
                            <pre class="bg-light p-3 rounded"><code>GET /apiv2/administration?page=1&limit=10</code></pre>

                            <h4>3. Tri</h4>
                            <pre class="bg-light p-3 rounded"><code>GET /apiv2/administration?sort=name&order=desc
GET /apiv2/administration?sort=label&order=asc</code></pre>
                        </div>
                        <div class="col-md-6">
                            <h4>4. Filtres simples</h4>
                            <pre class="bg-light p-3 rounded"><code>GET /apiv2/administration?filter={"name":"test"}
GET /apiv2/administration?filter={"label":"config"}</code></pre>

                            <h4>5. Création (CREATE)</h4>
                            <p>Pour créer une nouvelle ressource, utilisez une requête POST.</p>
                            <pre class="bg-light p-3 rounded"><code>POST /apiv2/{ressource}
Content-Type: application/json

{
    "field1": "valeur",
    "field2": "valeur"
}</code></pre>
                            <p>En cas de succès, la réponse contiendra la ressource créée avec son ID.</p>

                            <h4>6. Mise à jour (UPDATE)</h4>
                            <p>Pour mettre à jour une ressource, utilisez une requête POST avec l'ID de la ressource.</p>
                            <pre class="bg-light p-3 rounded"><code>POST /apiv2/{ressource}/{id}
Content-Type: application/json

{
    "field1": "nouvelle valeur",
    "field2": "nouvelle valeur"
}</code></pre>

                            <h4>7. Suppression (DELETE)</h4>
                            <p>Pour supprimer une ressource, utilisez une requête DELETE avec l'ID de la ressource.</p>
                            <pre class="bg-light p-3 rounded"><code>DELETE /apiv2/{ressource}/{id}</code></pre>
                            <p>La suppression retourne un code 204 en cas de succès.</p>

                            <h4>8. Filtres avec opérateurs</h4>
                            <pre class="bg-light p-3 rounded"><code>GET /apiv2/administration?filter={"value":{"operator":"LIKE","value":"%test%"}}
GET /apiv2/administration?filter={"administrationid":{"operator":">","value":"5"}}</code></pre>

                            <h4>9. Filtres NULL</h4>
                            <pre class="bg-light p-3 rounded"><code>GET /apiv2/administration?filter={"value":{"operator":"IS","value":null}}
GET /apiv2/administration?filter={"value":{"operator":"IS NOT","value":null}}</code></pre>

                            <h4>10. Combinaison de paramètres</h4>
                            <pre class="bg-light p-3 rounded"><code>GET /apiv2/administration?page=1&limit=10&sort=name&order=desc&filter={"value":{"operator":"LIKE","value":"%test%"}}</code></pre>
                        </div>
                    </div>
                </div>
            </div>

            <h2 class="border-bottom pb-2 mb-4">Modèles et Points d'Accès</h2>
            @foreach($models as $modelName => $modelInfo)
            <div id="model-{{ strtolower($modelName) }}" class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">{{ $modelName }}</h3>
                    <span class="badge bg-light text-primary">{{ is_array($modelInfo['schema']) ? count($modelInfo['schema']) : 0 }} champs</span>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p><strong>Table :</strong> <code>{{ $modelInfo['table'] }}</code></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Identifiant :</strong> <code>{{ $modelInfo['index'] }}</code></p>
                        </div>
                    </div>
                    
                    <h4>Schéma</h4>
                    <pre class="bg-light p-3 rounded"><code>{{ json_encode($modelInfo['schema'], JSON_PRETTY_PRINT) }}</code></pre>

                    <h4>Points d'accès</h4>
                    <div class="list-group">
                        <div class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-center">
                                <code>GET /apiv2/{{ strtolower($modelName) }}</code>
                                <span class="badge bg-success">GET</span>
                            </div>
                            <small class="text-muted">Liste des éléments</small>
                        </div>
                        <div class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-center">
                                <code>GET /apiv2/{{ strtolower($modelName) }}/{id}</code>
                                <span class="badge bg-info">GET</span>
                            </div>
                            <small class="text-muted">Détail d'un élément</small>
                        </div>
                        <div class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-center">
                                <code>POST /apiv2/{{ strtolower($modelName) }}</code>
                                <span class="badge bg-primary">POST</span>
                            </div>
                            <small class="text-muted">Création d'un élément</small>
                        </div>
                        <div class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-center">
                                <code>POST /apiv2/{{ strtolower($modelName) }}/{id}</code>
                                <span class="badge bg-primary">POST</span>
                            </div>
                            <small class="text-muted">Mise à jour d'un élément</small>
                        </div>
                        <div class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-center">
                                <code>DELETE /apiv2/{{ strtolower($modelName) }}/{id}</code>
                                <span class="badge bg-danger">DELETE</span>
                            </div>
                            <small class="text-muted">Suppression d'un élément</small>
                        </div>
                    </div>

                    @if($modelName === 'LeadManager' && isset($modelInfo['examples']))
                        <h3>Exemples d'utilisation</h3>
                        
                        <!-- Exemples de filtrage -->
                        <div class="mb-4">
                            <h4>Filtrage</h4>
                            <div id="filterExamples" class="accordion">
                                @foreach($modelInfo['examples']['filtrage'] as $index => $example)
                                    <div class="card">
                                        <div class="card-header" id="filter-heading-{{ $index }}">
                                            <h5 class="mb-0">
                                                <button class="btn btn-link" data-toggle="collapse" data-target="#filter-{{ $index }}" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="filter-{{ $index }}">
                                                    {{ $example['description'] }}
                                                </button>
                                            </h5>
                                        </div>
                                        <div id="filter-{{ $index }}" class="collapse {{ $index === 0 ? 'show' : '' }}" aria-labelledby="filter-heading-{{ $index }}" data-parent="#filterExamples">
                                            <div class="card-body">
                                                <p>{{ $example['explanation'] }}</p>
                                                <pre class="bg-light p-3 rounded"><code>{{ $example['request'] }}</code></pre>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Exemples de tri -->
                        <div class="mb-4">
                            <h4>Tri</h4>
                            <div id="sortExamples" class="accordion">
                                @foreach($modelInfo['examples']['tri'] as $index => $example)
                                    <div class="card">
                                        <div class="card-header" id="sort-heading-{{ $index }}">
                                            <h5 class="mb-0">
                                                <button class="btn btn-link" data-toggle="collapse" data-target="#sort-{{ $index }}" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="sort-{{ $index }}">
                                                    {{ $example['description'] }}
                                                </button>
                                            </h5>
                                        </div>
                                        <div id="sort-{{ $index }}" class="collapse {{ $index === 0 ? 'show' : '' }}" aria-labelledby="sort-heading-{{ $index }}" data-parent="#sortExamples">
                                            <div class="card-body">
                                                <p>{{ $example['explanation'] }}</p>
                                                <pre class="bg-light p-3 rounded"><code>{{ $example['request'] }}</code></pre>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Exemples de pagination -->
                        <div class="mb-4">
                            <h4>Pagination</h4>
                            <div id="paginationExamples" class="accordion">
                                @foreach($modelInfo['examples']['pagination'] as $index => $example)
                                    <div class="card">
                                        <div class="card-header" id="pagination-heading-{{ $index }}">
                                            <h5 class="mb-0">
                                                <button class="btn btn-link" data-toggle="collapse" data-target="#pagination-{{ $index }}" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="pagination-{{ $index }}">
                                                    {{ $example['description'] }}
                                                </button>
                                            </h5>
                                        </div>
                                        <div id="pagination-{{ $index }}" class="collapse {{ $index === 0 ? 'show' : '' }}" aria-labelledby="pagination-heading-{{ $index }}" data-parent="#paginationExamples">
                                            <div class="card-body">
                                                <p>{{ $example['explanation'] }}</p>
                                                <pre class="bg-light p-3 rounded"><code>{{ $example['request'] }}</code></pre>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Notes -->
                        @if(isset($modelInfo['notes']))
                            <h3>Notes importantes</h3>
                            <div class="list-group">
                                @foreach($modelInfo['notes'] as $title => $note)
                                    <div class="list-group-item">
                                        <strong>{{ $title }}</strong>: {{ $note }}
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<style>
/* Style pour le menu latéral */
.position-sticky {
    position: -webkit-sticky;
    position: sticky;
    top: 20px;
    max-height: calc(100vh - 40px);
    overflow-y: auto;
}

.list-group-item {
    padding: 0.75rem 1rem;
    border-radius: 0;
}

.list-group-item-primary {
    font-weight: bold;
    background-color: var(--bs-primary);
    color: white;
}

/* Style pour les ancres */
[id] {
    scroll-margin-top: 20px;
}

/* Style pour le code */
pre {
    margin: 0;
    white-space: pre-wrap;
}

code {
    color: var(--bs-primary);
}

/* Responsive */
@media (max-width: 768px) {
    .position-sticky {
        position: relative;
        max-height: none;
        margin-bottom: 1rem;
    }
}
</style>
@endsection