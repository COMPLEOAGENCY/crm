<!-- Button Group Component -->
<div class="form-group row">
    <div class="col-12">
        @foreach ($buttons as $button)
            <button type="submit" name="submit" value="{{ $button['value'] }}" class="btn {{ $button['class'] }}">{{ $button['label'] }}</button>
        @endforeach
    </div>
</div>