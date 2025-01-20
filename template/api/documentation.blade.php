@extends('admin.blanck')

@section('title', $title)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1>Documentation API v2</h1>
            
            <h2>Base URL</h2>
            <pre><code>{{ $baseUrl }}</code></pre>

            <h2>Modèles disponibles</h2>
            @foreach($models as $modelName => $modelInfo)
            <div class="card mb-4">
                <div class="card-header">
                    <h3>{{ $modelName }}</h3>
                    <small>Table: {{ $modelInfo['tableName'] }}, Clé primaire: {{ $modelInfo['primaryKey'] }}</small>
                </div>
                <div class="card-body">
                    <h4>Schéma</h4>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Champ</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th>Défaut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($modelInfo['schema'] as $field => $info)
                            <tr>
                                <td><code>{{ $field }}</code></td>
                                <td>{{ $info['type'] }}</td>
                                <td>{{ $info['fieldType'] }}</td>
                                <td>{{ $info['default'] ?? 'NULL' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <h4>Exemples d'utilisation</h4>
                    <h5>Liste</h5>
                    <pre><code>GET {{ $baseUrl }}/{{ strtolower($modelName) }}?method=list</code></pre>

                    <h5>Obtenir par ID</h5>
                    <pre><code>GET {{ $baseUrl }}/{{ strtolower($modelName) }}/123?method=get</code></pre>

                    <h5>Créer</h5>
                    <pre><code>POST {{ $baseUrl }}/{{ strtolower($modelName) }}
method=set&[champs]</code></pre>

                    <h5>Mettre à jour</h5>
                    <pre><code>POST {{ $baseUrl }}/{{ strtolower($modelName) }}/123
method=set&[champs]</code></pre>

                    <h5>Supprimer</h5>
                    <pre><code>POST {{ $baseUrl }}/{{ strtolower($modelName) }}/123
method=delete</code></pre>
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
}
code {
    color: #e83e8c;
}
</style>
@endsection
