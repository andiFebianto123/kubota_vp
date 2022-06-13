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
                <td colspan="4">ORDER SHEET {{$po->po_num}} Rev.{{$po->po_change}}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td colspan="4">{{$po->vend_num}} - {{$po->vendor->vend_name ?? '-'}}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td colspan="4">Attn :</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td><b>PT. KUBOTA INDONESIA</b></td>
                <td></td>
            </tr>
            <tr>
                <td colspan="4">{{$po->vendor->vend_name ?? '-'}}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td colspan="2">Taman Industri Bukit Semarang Baru (BSB) Blok D.1 Kav.8 <br> Kel.Jatibarang - Kec. Mijen – Kota Semarang</td>
            </tr>
            <tr>
                <td colspan="4">{{$po->vendor->vend_addr ?? '-'}}</td>
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
                <td colspan="4">Email : {{$po->vendor->vend_email ?? '-'}}</td>
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
                <td align="center" style="border: 1px solid #000000;"><b>UNIT PRICE ({{$po->vendor->currency ?? '-'}})</b></td>
                <td align="center" style="border: 1px solid #000000;"><b>TOTAL AMOUNT ({{$po->vendor->currency ?? '-'}})</b></td>
                <td align="center" style="border: 1px solid #000000;"><b>PROD DATE (Ref)</b></td>
            </tr>
            @php
            $total = 0;
            $num = 1;
            @endphp
            @foreach ($po_lines as $key => $po_line)
            @php
                $total += $po_line->order_qty*$po_line->unit_price;
                $due_date = date("Y-m-d", strtotime($po_line->due_date));
                $unit_price = number_format($po_line->unit_price,0,',','.');
            @endphp
            <tr>
                <td align="center" style="border-left: 1px solid #000000; border-right:1px solid #000000;">
                    {{$num++}}
                </td>
                <td align="center" style="border-right:1px solid #000000;" class="text-nowrap">
                    {{$po_line->po_num}}-{{$po_line->po_line}}
                </td>
                <td align="center" style="border-right:1px solid #000000;">
                    {{$po_line->po_change}}
                </td>
                <td style="border-right:1px solid #000000;">
                    {{$po_line->item}}
                </td>
                <td style="border-right:1px solid #000000;">
                    @if($po_line->po_change == $po->po_change)
                    {!! $po_line->change_description_bold !!}
                    @else
                    {{$po_line->description}}
                    @endif
                </td>
                <td align="center" style="border-right:1px solid #000000;">
                    @if($po_line->po_change == $po->po_change)
                    {!! date('Y', strtotime($po_line->change_due_date_bold)) > 2000 ? $po_line->change_due_date_bold : "-" !!}
                    @else
                    {{ date('Y', strtotime($due_date)) > 2000 ? $due_date : "-" }}
                    @endif
                </td>
                <td align="right" style="border-right:1px solid #000000;">
                    @if($po_line->po_change == $po->po_change)
                    {!! $po_line->change_order_qty_bold !!}
                    @else
                    {{$po_line->order_qty}}
                    @endif
                </td>
                <td align="right" style="border-right:1px solid #000000;" class="text-nowrap">
                    @if(App\Helpers\Constant::checkPermission('Show Price In PO Menu'))
                        @if($po_line->po_change == $po->po_change)
                        {!! $po_line->change_unit_price_bold !!}
                        @else
                        {{$unit_price}}
                        @endif
                    @endif
                </td>
                <td align="right" style="border-right:1px solid #000000;" class="text-nowrap">
                    @if(App\Helpers\Constant::checkPermission('Show Price In PO Menu'))
                        {{number_format($po_line->order_qty*$po_line->unit_price,0,',','.')}}
                    @endif
                </td>
                <td align="center" style="border-right:1px solid #000000;">
                    {!!  date('Y', strtotime($po_line->production_date)) > 2000 ? date("Y-m-d", strtotime($po_line->production_date)) : "-" !!}
                </td>

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
                    @if(App\Helpers\Constant::checkPermission('Show Price In PO Menu'))
                    <div class="total-price"><b>{{$po->vendor->currency ?? '-'}} {{number_format($total,0,',','.')}}</b></div>
                    @endif
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