@extends('admin.blanck')

@section('title', $title)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1>Documentation API v{{ $version }}</h1>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h2>Informations Générales</h2>
                </div>
                <div class="card-body">
                    <h3>URL de Base</h3>
                    <pre><code>{{ $baseUrl }}</code></pre>

                    <h3>Authentification</h3>
                    <p>{{ $authentication['description'] }}</p>
                    <pre><code>Authorization: Bearer {votre-token}</code></pre>

                    <h3>Codes d'Erreur</h3>
                    <table class="table">
                        <thead>
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

            <h2>Modèles et Points d'Accès</h2>
            @foreach($models as $modelName => $modelInfo)
            <div class="card mb-4">
                <div class="card-header">
                    <h3>{{ $modelName }}</h3>
                    <small>Table: {{ $modelInfo['tableName'] }}, Clé primaire: {{ $modelInfo['primaryKey'] }}</small>
                </div>
                <div class="card-body">
                    <h4>Structure des Données</h4>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Champ</th>
                                <th>Type</th>
                                <th>Requis</th>
                                <th>Valeur par défaut</th>
                                <th>Contraintes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($modelInfo['fields'] as $field => $info)
                            <tr>
                                <td><code>{{ $field }}</code></td>
                                <td>{{ $info['type'] }}</td>
                                <td>{{ $info['required'] ? 'Oui' : 'Non' }}</td>
                                <td><code>{{ $info['default'] ?? 'NULL' }}</code></td>
                                <td>
                                    @if(isset($info['enum']))
                                        Valeurs possibles: {{ implode(', ', $info['enum']) }}
                                    @endif
                                    @if(isset($info['min']))
                                        Min: {{ $info['min'] }}
                                    @endif
                                    @if(isset($info['max']))
                                        Max: {{ $info['max'] }}
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <h4>Points d'Accès</h4>
                    
                    <h5>Lister les éléments</h5>
                    <pre><code>GET {{ $modelInfo['endpoints']['list'] }}</code></pre>
                    <p>Paramètres de requête :</p>
                    <ul>
                        <li><code>page</code> : Numéro de page (optionnel)</li>
                        <li><code>limit</code> : Nombre d'éléments par page (optionnel)</li>
                        <li><code>sort</code> : Champ de tri (optionnel)</li>
                        <li><code>order</code> : Direction du tri (asc/desc) (optionnel)</li>
                    </ul>

                    <h5>Obtenir un élément</h5>
                    <pre><code>GET {{ $modelInfo['endpoints']['get'] }}</code></pre>

                    <h5>Créer un élément</h5>
                    <pre><code>POST {{ $modelInfo['endpoints']['create'] }}
Content-Type: application/json

@json($modelInfo['example'], JSON_PRETTY_PRINT)</code></pre>

                    <h5>Mettre à jour un élément</h5>
                    <pre><code>PUT {{ $modelInfo['endpoints']['update'] }}
Content-Type: application/json

@json($modelInfo['example'], JSON_PRETTY_PRINT)</code></pre>

                    <h5>Supprimer un élément</h5>
                    <pre><code>DELETE {{ $modelInfo['endpoints']['delete'] }}</code></pre>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@section('custom-css')
<style>
pre {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 4px;
    margin: 10px 0;
}
code {
    color: #e83e8c;
    background-color: #f8f9fa;
    padding: 2px 4px;
    border-radius: 3px;
}
.table td, .table th {
    vertical-align: middle;
}
h5 {
    margin-top: 20px;
}
</style>
@endsection
