@props(['canSpin' =>true])
<div id="spin-wheel-app" {{ $attributes->merge(['class' => '']) }}></div>
<script>
    window.spinWheelData = {
        items: @json($items),
        config: @json($config),
        canSpin: @json((bool) $canSpin),
        csrfToken: "{{csrf_token()}}",
        // csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
    };
</script>
<script src="{{ mix('/js/manifest.js','/spin-wheel-tool') }}"></script>
<script src="{{ mix('/js/vendor.js','/spin-wheel-tool') }}"></script>
<script src="{{ mix('js/spin-wheel.js','/spin-wheel-tool') }}"></script>
