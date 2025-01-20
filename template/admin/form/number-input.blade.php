<!-- Number Input Component -->
<div class="form-group row">
    <label class="col-sm-4 col-form-label" for="{{ $id }}">{{ $label }}</label>
    <div class="col-sm-8">
        <input class="form-control {{ $validationClass }}" type="number" name="{{ $name }}" id="{{ $id }}" placeholder="{{ $placeholder }}" value="{{ $value }}">
        {!! $errorHTML !!}
    </div>
</div>