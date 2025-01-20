<!-- Date Input Component -->
<div class="form-group row">
    <label class="col-sm-4 col-form-label" for="{{ $id }}">{{ $label }}</label>
    <div class="col-sm-8">
        <input readonly class="form-control showdate" type="text" name="{{ $name }}" id="{{ $id }}" value="{{ $value }}">
    </div>
</div>