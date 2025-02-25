<?php // path: src/template/admin/menu.blade.php ?>
<nav class="navbar navbar-expand-lg navbar-light">
<a class="navbar-brand" href="/admin/"><img src="{{ asset('assets/img/applicationlogo.gif') }}" /></a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item">
                <a class="nav-link" href="/admin/">Accueil</a>
            </li>
            <li class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">Leads</a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="/admin/lead.php">Liste des leads</a>
                    <a class="dropdown-item" href="/admin/lead-add.php">Ajouter un lead</a>
                    <a class="dropdown-item" href="/admin/quality.php">Qualité des leads</a>
                </div>
            </li>

            <li class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">Ventes</a>
                <div class="dropdown-menu">
                    <div class="dropdown-header">Campagnes clients</div>
                    <a class="dropdown-item" href="/admin/clientcampaign.php">Liste des campagnes clients</a>
                    <a class="dropdown-item" href="/admin/clientcampaign-add.php">Ajouter une campagne client</a>
                    <div class="dropdown-divider"></div>
                    <div class="dropdown-header">Transferts</div>
                    <a class="dropdown-item" href="/admin/deversoir.php">Déversoir</a>
                    <div class="dropdown-divider"></div>
                    <div class="dropdown-header">Ventes</div>
                    <a class="dropdown-item" href="/admin/ventes.php">Total des ventes</a>
                    <a class="dropdown-item" href="/admin/sale.php">Liste des ventes</a>
                    <a class="dropdown-item" href="/admin/sale-add.php">Ajouter une vente</a>
                </div>
            </li>

            <li class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">Collecte</a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="/admin/performance.php">Performances</a>
                    <div class="dropdown-header">Sources</div>
                    <a class="dropdown-item" href="/admin/source.php">Liste des sources</a>
                    <a class="dropdown-item" href="/admin/source-add.php">Ajouter une source</a>
                    <div class="dropdown-divider"></div>
                    <div class="dropdown-header">Ciblage</div>
                    <a class="dropdown-item" href="/admin/ciblage.php">Liste des cibles</a>
                    <a class="dropdown-item" href="/admin/ciblage-add.php">Ajouter un ciblage</a>
                    <div class="dropdown-divider"></div>
                    <div class="dropdown-header">Campagnes</div>
                    <a class="dropdown-item" href="/admin/campaign.php">Liste des campagnes</a>
                    <a class="dropdown-item" href="/admin/campaign-add.php">Ajouter une campagne</a>
                    <div class="dropdown-divider"></div>
                    <div class="dropdown-header">Questions</div>
                    <a class="dropdown-item" href="/admin/question.php">Liste des questions</a>
                    <a class="dropdown-item" href="/admin/question-add.php">Ajouter une question</a>
                    <div class="dropdown-divider"></div>
                    <div class="dropdown-header">Mapping</div>
                    <a class="dropdown-item" href="/admin/mapping.php">Liste des mapping</a>
                    <a class="dropdown-item" href="/admin/mapping-add.php">Ajouter un mapping</a>
                    <div class="dropdown-divider"></div>
                    <div class="dropdown-header">Vérifications</div>
                    <a class="dropdown-item" href="/admin/verification.php">Liste des vérifications</a>
                    <a class="dropdown-item" href="/admin/verification-add.php">Ajouter une vérification</a>
                </div>
            </li>

            <li class="nav-item dropdown">
                <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">Gestion</a>
                <div class="dropdown-menu">
                    <div class="dropdown-header">Comptes</div>
                    <a class="dropdown-item" href="/admin/userlist/">Liste des comptes</a>
                    <a class="dropdown-item" href="/admin/user-add.php">Ajouter un compte</a>
                    <div class="dropdown-divider"></div>
                    <div class="dropdown-header">Intelligence Artificielle</div>
                    <a class="dropdown-item" href="/admin/ai/chat">Assistant AI</a>
                    <div class="dropdown-divider"></div>
                    <div class="dropdown-header">Redis</div>
                    <a class="dropdown-item" href="/admin/redis/info">État du serveur Redis</a>
                    <a class="dropdown-item" href="/admin/redis/explore">Explorateur Redis</a>
                    <div class="dropdown-divider"></div>
                    <div class="dropdown-header">Utilisateurs</div>
                    <a class="dropdown-item" href="/admin/user-sub.php">Liste des utilisateurs</a>
                    <a class="dropdown-item" href="/admin/user-sub-add.php">Ajouter un utilisateur</a>
                    <div class="dropdown-divider"></div>
                    <div class="dropdown-header">Groupes d'utilisateurs</div>
                    <a class="dropdown-item" href="/admin/rol.php">Liste des rôles</a>
                    <a class="dropdown-item" href="/admin/rol-add.php">Ajouter un rôle</a>
                    <div class="dropdown-divider"></div>
                    <div class="dropdown-header">Livraison deversoir par @</div>
                    <a class="dropdown-item" href="/admin/transfert-method.php">Liste des livraison @</a>
                    <a class="dropdown-item" href="/admin/transfert-method-add.php">Ajouter un livraison @</a>
                    <div class="dropdown-divider"></div>
                    <div class="dropdown-header">Factures</div>
                    <a class="dropdown-item" href="/admin/invoice.php">Liste des factures</a>
                    <a class="dropdown-item" href="/admin/invoice-add.php">Ajouter une facture</a>
                    <a class="dropdown-item" href="/admin/user-balance.php">Solde client</a>
                    <a class="dropdown-item" href="/admin/import-invoices.php">Importation de la facturation</a>
                    <div class="dropdown-divider"></div>
                    <div class="dropdown-header">Domaines</div>
                    <a class="dropdown-item" href="/admin/domain.php">Liste des domaines</a>
                    <a class="dropdown-item" href="/admin/domain-add.php">Ajout de domaines</a>
                    <div class="dropdown-divider"></div>
                    <div class="dropdown-header">Exportation/Importation</div>
                    <a class="dropdown-item" href="/admin/export.php">Module d'exportation</a>
                    <a class="dropdown-item" href="/admin/import.php">Module d'importation</a>
                    <div class="dropdown-divider"></div>
                    <div class="dropdown-header">API</div>
                        <a class="dropdown-item" href="/apiv2/docs">
                            <i class="fas fa-book"></i> Documentation API
                        </a>
                        <div class="dropdown-divider"></div>                    
                    <div class="dropdown-header">Paramétrage</div>
                    <a class="dropdown-item" href="/admin/parameters.php">Informations d'administration</a>
                    <a class="dropdown-item" href="/admin/monitoring.php">Monitoring</a>
                </div>
            </li>
        </ul>
        <div style="padding:7px;line-height:20px;">
            <a href="/admin/logout" class="btn btn-danger">Déconnexion</a>
        </div>
    </div>
</nav>