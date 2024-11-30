<footer class="px-4 bg-neutral-100 border-t">
    <div class="md:grid-cols-3 grid py-12 max-w-7xl mx-auto sm:px-3 gap-2">
        

       










    </div>

    <div class="flex gap-1 justify-center py-2">
        <div><img src="{{ Storage::disk('public')->url('image/logo/amex.svg') }}"></div>
        <div><img src="{{ Storage::disk('public')->url('image/logo/diners.svg') }}"></div>
        <div><img src="{{ Storage::disk('public')->url('image/logo/discover.svg') }}"></div>
        <div><img src="{{ Storage::disk('public')->url('image/logo/jcb.svg') }}"></div>
        <div><img src="{{ Storage::disk('public')->url('image/logo/mastercard.svg') }}"></div>
        <div><img src="{{ Storage::disk('public')->url('image/logo/paypal.svg') }}"></div>
        <div><img src="{{ Storage::disk('public')->url('image/logo/unionpay.svg') }}"></div>
        <div><img src="{{ Storage::disk('public')->url('image/logo/visa.svg') }}"></div>
    </div>

    <div class="copyright text-center text-xs pt-4 pb-10">&copy <?php echo date("Y"); ?> Copyright {{ env('APP_NAME') }}. All Rights Reserved.</div>

</footer>