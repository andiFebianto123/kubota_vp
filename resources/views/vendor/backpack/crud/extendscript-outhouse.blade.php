<script>
     function outhouseTableManager(currentQty){
        var anyError = false
        var arrQtyPer = []
        var arrLotQty = {}
        $('.info-qty').html('')
        $.each($('.form-issued'), function( k, v ) {
            var lotqty = parseFloat($('.form-issued:eq('+k+')').data('lotqty'))
            var qtyper = parseFloat($('.form-issued:eq('+k+')').data('qtyper'))
            var totalQtyPer = parseFloat($('.form-issued:eq('+k+')').data('totalqtyper'))
            var issuedQty =  currentQty*qtyper
            var fixedIssuedQty = (lotqty > issuedQty) ? issuedQty : lotqty
                fixedIssuedQty = parseFloat(fixedIssuedQty).toFixed(2);
            $('.form-issued:eq('+k+')').val(fixedIssuedQty)
            $('.qty-requirement:eq('+k+')').text(fixedIssuedQty)
            arrLotQty[qtyper] = lotqty
            arrQtyPer.push(qtyper)
            $('.outhouse-table tbody tr:eq('+k+')').css('color', '#000000')
            if (issuedQty > lotqty) {
                $('.outhouse-table tbody tr:eq('+k+')').css('color', '#df4759')
                anyError = true
            }
        }) 
        
        var maxQtyPer = Math.max(...arrQtyPer)
        var maxQtyAllowed = arrLotQty[maxQtyPer]/maxQtyPer
        if (anyError) {
            $('.info-qty').html('<small>Jumlah Qty melebihi batas maksimal ('+maxQtyAllowed+')</small>')
        }       
    }
</script>