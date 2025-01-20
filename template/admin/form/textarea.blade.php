<!-- Textarea Component -->
<div class="form-group row">
    <label class="col-sm-4 col-form-label" for="{{ $id }}">{{ $label }}</label>
    <div class="col-sm-8">
        <textarea class="form-control" name="{{ $name }}" id="{{ $id }}" placeholder="{{ $placeholder }}">{{ $value }}</textarea>
    </div>
</div>