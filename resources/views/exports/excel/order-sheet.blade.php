<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF</title>
</head>

<body>
    <table>
        <tr>
            <td colspan="4">ORDER SHEET {{$po->po_num}} Rev 00</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td colspan="4">{{$po->vend_num}} - {{$po->vendor->vend_name}}</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td colspan="4">Attn : <br> {{$po->vendor->vend_name}}<br> {{$po->vendor->vend_addr}}</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><b>PT. KUBOTA INDONESIA</b></td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td colspan="2">Taman Industri Bukit Semarang Baru (BSB) Blok D.1 Kav.8 <br> Kel.Jatibarang - Kec. Mijen – Kota Semarang</td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>Telp</td>
            <td>: 024-747289, 7473257</td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>Fax</td>
            <td>: 024-7474266, 7472865</td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>Email</td>
            <td>: ptki_g.layanan@kubota.com </td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>Website</td>
            <td>: www.ptkubota.co.id</td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td colspan="4">Email : {{$po->vendor->vend_email}}</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><b>Issued Date : {{date("Y-m-d", strtotime($po->po_date))}}</b></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td align="center" style="border: 1px solid #000000;"><b>No</b></td>
            <td align="center" style="border: 1px solid #000000;"><b>ORDER NO</b></td>
            <td align="center" style="border: 1px solid #000000;"><b>REV</b></td>
            <td align="center" style="border: 1px solid #000000;"><b>ITEM NO</b></td>
            <td align="center" style="border: 1px solid #000000;"><b>DESCRIPTION</b></td>
            <td align="center" style="border: 1px solid #000000;"><b>DUE DATE</b></td>
            <td align="center" style="border: 1px solid #000000;"><b>QTY ORDER</b></td>
            <td align="center" style="border: 1px solid #000000;"><b>UNIT PRICE ({{$po->vendor->currency}})</b></td>
            <td align="center" style="border: 1px solid #000000;"><b>TOTAL AMOUNT ({{$po->vendor->currency}})</b></td>
            <td align="center" style="border: 1px solid #000000;"><b>PROD DATE (Ref)</b></td>
        </tr>
        @php
            $total = 0;
            $num = 1;
        @endphp
        @foreach ($po_lines as $key => $po_line)
        @php
            $total += $po_line->order_qty*$po_line->unit_price;
        @endphp
        <tr>
            <td align="center" style="border-left: 1px solid #000000; border-right:1px solid #000000;">{{$num++}}</td>
            <td align="center" style="border-right:1px solid #000000;" class="text-nowrap">{{$po_line->po_num}}-{{$po_line->po_line}}</td>
            <td align="center" style="border-right:1px solid #000000;">00</td>
            <td style="border-right:1px solid #000000;">{{$po_line->item}}</td>
            <td style="border-right:1px solid #000000;">{{$po_line->description}}</td>
            <td align="center" style="border-right:1px solid #000000;">{!! date("Y-m-d", strtotime($po_line->due_date)) !!}</td>
            <td align="right" style="border-right:1px solid #000000;">{{$po_line->order_qty}}</td>
            <td align="right" style="border-right:1px solid #000000;" class="text-nowrap"> {{number_format($po_line->unit_price,0,',','.')}}</td>
            <td align="right" style="border-right:1px solid #000000;" class="text-nowrap">{{number_format($po_line->order_qty*$po_line->unit_price,0,',','.')}}</td>
            <td align="center" style="border-right:1px solid #000000;">{!! date("Y-m-d", strtotime($po_line->production_date)) !!}</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="5" rowspan="7" valign="bottom" style="border: 1px solid #000000; padding:4px;">
                <b>*) Computer direct to PDF Format, No Signature is required</b>
            </td>
            <td colspan="3" style="border-top: 1px solid #000000; border-right: 1px solid #000000;">
                <div class="total-price">TOTAL </div>

            </td>
            <td colspan="2" style="border-top: 1px solid #000000; border-right: 1px solid #000000;">
                <div class="total-price"><b>{{$po->vendor->currency}} {{number_format($total,0,',','.')}}</b></div>
            </td>
        </tr>
        <tr>
            <td style="border-top: 1px solid #000000; border-right: 1px solid #000000;"></td>
            <td colspan="4" valign="bottom" style="border-right: 1px solid #000000; border-top: 1px solid #000000; padding:4px;">
                <b>NOTE : Document's Requirements :</b>
            </td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td colspan="3" valign="bottom" style="border-right: 1px solid #000000;">
                ▢ Material Mill Sheet
            </td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td colspan="3" valign="bottom" style="border-right: 1px solid #000000;">
                ▢ Material Safety Data Sheet
            </td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td colspan="3" valign="bottom" style="border-right: 1px solid #000000;">
                ▢ Result Of Inspection (Certificate)  
            </td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td colspan="3" valign="bottom" style="border-right: 1px solid #000000;">
                ▢ Product Safety Information Sheet
            </td>
        </tr>
        <tr>
            <td style="border-bottom: 1px solid #000000;"></td>
            <td style="border-bottom: 1px solid #000000;"></td>
            <td colspan="3" valign="bottom" style="border-right: 1px solid #000000; border-bottom: 1px solid #000000; ">
                ▢ Instruction Operator Manual
            </td>
        </tr>
    </table>
</body>

</html>