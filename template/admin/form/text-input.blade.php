<!-- Text Input Component -->
<div class="form-group row">
    <label class="col-sm-4 col-form-label" for="{{ $id }}" >{{ $label }}
    @if($required)
            <span class="text-danger">*</span>
    @endif
    </label>

    <div class="col-sm-8">
        <input {{ $readonly ?? '' }} {{ $required ? 'required' : '' }} class="form-control {{ $validationClass }}" type="text" name="{{ $name }}" id="{{ $id }}" placeholder="{{ $placeholder }}" value="{{ $value }}">
        {!! $errorHTML !!}
    </div>
</div>