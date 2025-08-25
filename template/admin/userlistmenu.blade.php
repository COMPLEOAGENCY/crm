<?php // path: src/template/admin/userlistmenu.blade.php ?>
<form class="w-100 mb-4" id="balance" method="POST">
<div class="card modern-card">
    <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
        <h5 class="mb-0"><i class="bi bi-funnel-fill me-2"></i>Filtres de recherche</h5>
    </div>
    <div class="card-body">

        <div class="form-group row">
            <label class="col-sm-2 col-form-label">Compte : </label>
            <div class="col-sm-10">
                <select data-required="false" data-live-search="true" class="selectpicker form-control" id="userid" name="userid">
                    <option value="">Tous</option>
                    @foreach ($userList as $id)
                        <option value="{{ $id->userId }}" {{ isset($params['userid']) && $params['userid'] == $id->userId ? 'selected' : '' }}>
                            {{ $id->company }} - {{ $id->userId }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-2 col-form-label">Type : </label>
            <div class="col-sm-10">
                <select data-required="false" data-live-search="true" class="selectpicker form-control" id="type" name="type">
                    <option value="">Tous</option>
                    <option value="client" {{ isset($params['type']) && $params['type'] == 'client' ? 'selected' : '' }}>Client</option>
                    <option value="provider" {{ isset($params['type']) && $params['type'] == 'provider' ? 'selected' : '' }}>Fournisseur</option>
                    <option value="admin" {{ isset($params['type']) && $params['type'] == 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-2 col-form-label">Gestionnaire : </label>
            <div class="col-sm-10">
                <select data-required="false" data-live-search="true" class="selectpicker form-control" id="crm_userid" name="crm_userid">
                    <option value="">Tous</option>
                    @foreach ($crmUserList as $id)
                        <option value="{{ $id->crmUserId }}" {{ isset($params['crm_userid']) && $params['crm_userid'] == $id->crm_userId ? 'selected' : '' }}>
                        {{ $id->crmUserId }} - {{ $id->crm_user_firstname }} {{ $id->crm_user_lastname }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-2 col-form-label">Statut : </label>
            <div class="col-sm-10">
                <select data-required="false" data-live-search="true" class="selectpicker form-control" id="statut" name="statut">
                    <option value="on" {{ isset($params['statut']) && $params['statut'] == 'on' ? 'selected' : '' }}>On</option>
                    <option value="off" {{ isset($params['statut']) && $params['statut'] == 'off' ? 'selected' : '' }}>Off</option>
                    <option value="all" {{ isset($params['statut']) && $params['statut'] == 'all' ? 'selected' : '' }}>Tous</option>
                    <option value="credit_over" {{ isset($params['statut']) && $params['statut'] == 'credit_over' ? 'selected' : '' }}>Encours crédit dépassé</option>
                </select>
            </div>
        </div>
    </div>
    <div class="card-footer">
        <button type="submit" name="submit" value="search" class="btn btn-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 10px 30px; border-radius: 25px;"><i class="bi bi-search me-1"></i> Rechercher</button>
        <button type="submit" name="submit" value="refresh" class="btn btn-secondary" style="padding: 10px 30px; border-radius: 25px;"><i class="bi bi-arrow-clockwise me-1"></i> Rafraîchir</button>
    </div>
</div>
</form>
