<!-- Select Component -->
<div class="form-group row">
    <label class="col-sm-4 col-form-label" for="{{ $id }}">{{ $label }}
    @if($required)
            <span class="text-danger">*</span>
    @endif
    </label>
    <div class="col-sm-8">
        <select class="selectpicker form-control {{ $required ? 'required' : '' }} {{ $validationClass }}" id="{{ $id }}" name="{{ $name }}" {{ $attributes }}>
            @if($defaultOption != false)
                <option value="">{{ $defaultOption }}</option>
            @endif
            @foreach ($options as $option)
                @php
                    if(is_array($option)){
                        $option = (object) $option;
                    }
                @endphp
                <option value="{{ $option->$optionValue }}" {{ ($selected == $option->$optionValue) ? 'selected' : '' }}>
                    {{ $option->$optionLabel }}
                </option>
            @endforeach
        </select>
        {!! $errorHTML !!}
    </div>
</div>


