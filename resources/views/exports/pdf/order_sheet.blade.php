<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Order Sheet</title>
    </head>
    <style>
        .table-items {
            font-size: 10px;
            width: 100%;
            margin-top: 10px;
            font-family: Arial, Helvetica, sans-serif;
        }
        .table-items td {
            padding: 2px;
            background-color: #ffffff;
            border-right: 1px solid #000000;
            border-bottom: 0px;
        }
        .table-items th {
            border-right: 1px solid #000000;
            border-top: 1px solid #000000;
            border-bottom: 1px solid #000000;
        }
        .unit-sheet {
            border: 1px solid #000000;
        }
        .header-section {
            margin: 10px 10px 5px 10px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
        }
        .header-section .title {
            font-weight: bold;
            font-size: 14px;
        }
        .email-and-issued {
            font-size: 10px;
            font-family: Arial, Helvetica, sans-serif;
            margin: 10px 10px 0px 10px;
        }
        .title-table-right {
            font-weight: bold;
            font-size: 14px;
        }
        .box-check {
            border: 1px solid #000000;
            width: 20px;
            height: 10px;
            color: #ffffff;
        }
        .total-price {
            font-size: 12px;
            padding: 4px;
            text-align: center;
        }
        .doc-req {
            padding: 4px 10px 4px 10px;
            width: 80%;
        }
        footer {
            position: fixed;
            bottom: -60px;
            left: 0px;
            right: 0px;
            height: 50px;
            color: #000000;
            font-size: 12px;
        }
        .pagenum:before {
            content: counter(page);
        }
    </style>

    <body>
        <div class="unit-sheet">
            <div class="header-section">
                <table style="width:100%;">
                    <tr>
                        <td valign="top">
                            <label class="title">ORDER SHEET {{$po->po_num}} Rev.{{$po->po_change}}</label>
                            <p>{{$po->vend_num}} - {{$po->vendor->vend_name ?? '-'}}</p>
                            <p>Attn : <br> {{$po->vendor->vend_name ?? '-'}}<br> {{$po->vendor->vend_addr ?? '-'}}</p>
                        </td>
                        <td valign="top" align="right">
                            <img src="{{ public_path('/img/logokubotaforearth.png')}}" width="250px"><br>
                            <div style="text-align: left; width:280px; float:right">
                                <span class="title-table-right">PT. KUBOTA INDONESIA</span> <br>
                                Taman Industri Bukit Semarang Baru (BSB) Blok D.1 Kav.8 <br> Kel.Jatibarang - Kec. Mijen – Kota Semarang
                                <table>
                                    <tr>
                                        <td>Telp</td>
                                        <td>: 024-747289, 7473257</td>
                                    </tr>
                                    <tr>
                                        <td>Fax</td>
                                        <td>: 024-7474266, 7472865</td>
                                    </tr>
                                    <tr>
                                        <td>Email</td>
                                        <td>: ptki_g.layanan@kubota.com </td>
                                    </tr>
                                    <tr>
                                        <td>Website</td>
                                        <td>: www.ptkubota.co.id</td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            <div style="clear: both;"></div>
            <table class="email-and-issued" style="width:100%">
                <tbody>
                    <tr>
                        <td style="width:80%;text-align:left">Email : {{implode(', ', preg_split("/[;,]+/", preg_replace('/\s+/', '', $po->vendor->vend_email ?? '-')))}}</td>
                        <td style="width:20%;text-align:right;padding-right:20px;padding-left:20px"><b>Issued Date : {{date("Y-m-d", strtotime($po->po_date))}}</b></td>
                    </tr>
                </tbody>
            </table>
            <div style="clear: both;"></div>
            <div>
                <table class="table-items" border="0" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>ORDER NO</th>
                            <th>REV</th>
                            <th>ITEM NO</th>
                            <th>DESCRIPTION</th>
                            <th>DUE DATE</th>
                            <th>QTY ORDER</th>
                            <th>UNIT PRICE ({{$po->vendor->currency ?? '-'}})</th>
                            <th>TOTAL AMOUNT ({{$po->vendor->currency ?? '-'}})</th>
                            <th>PROD DATE (Ref)</th>
                        </tr>
                    </thead>
                    <tbody>
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
                            <td align="center">{{$num++}}</td>
                            <td align="center" class="text-nowrap">{{$po_line->po_num}}-{{$po_line->po_line}}</td>
                            <td align="center">{{$po_line->po_change}}</td>
                            <td>{{$po_line->item}}</td>
                            <td>
                                @if($po_line->po_change == $po->po_change)
                                {!! $po_line->change_description_bold !!}
                                @else
                                {{$po_line->description}}
                                @endif
                            </td>
                            <td align="center">
                                @if($po_line->po_change == $po->po_change)
                                {!! $po_line->change_due_date_bold !!}
                                @else
                                {{$due_date}}
                                @endif
                            </td>
                            <td align="right">
                                @if($po_line->po_change == $po->po_change)
                                {!! $po_line->change_order_qty_bold !!}
                                @else
                                {{$po_line->order_qty}}
                                @endif
                            </td>
                            <td align="right" class="text-nowrap">
                                @if(App\Helpers\Constant::checkPermission('Show Price In PO Menu'))
                                    @if($po_line->po_change == $po->po_change)
                                    {!! $po_line->change_unit_price_bold !!}
                                    @else
                                    {{$unit_price}}
                                    @endif
                                @endif
                            </td>
                            <td align="right" class="text-nowrap">
                                @if(App\Helpers\Constant::checkPermission('Show Price In PO Menu'))
                                    {{number_format($po_line->order_qty*$po_line->unit_price,0,',','.')}}
                                @endif
                            </td>
                            <td align="center">{!! date("Y-m-d", strtotime($po_line->production_date)) !!}</td>
                        </tr>
                        @endforeach
                        <tr>
                            <td colspan="5" rowspan="2" valign="bottom" style="border-top: 1px solid #000000; padding:4px;">
                                <b>*) Computer direct to PDF Format, No Signature is required</b>
                            </td>
                            <td colspan="3" style="border-top: 1px solid #000000;">
                                <div class="total-price">TOTAL </div>

                            </td>
                            <td colspan="2" style="border-top: 1px solid #000000;">
                                @if(App\Helpers\Constant::checkPermission('Show Price In PO Menu'))
                                <div class="total-price"><b>{{$po->vendor->currency ?? '-'}} {{number_format($total,0,',','.')}}</b></div>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td colspan="5" style="border-top: 1px solid #000000;">
                                <div class="doc-req">
                                    <p>NOTE : Document's Requirements :</p>
                                    <div>
                                        <div class="box-check" style="float: left;"></div>
                                        <div style="float: left; margin-left:6px;">Material Mill Sheet</div>
                                    </div>
                                    <div style="clear:both"></div>
                                    <div>
                                        <div class="box-check" style="float: left;"></div>
                                        <div style="float: left; margin-left:6px;">Material Safety Data Sheet</div>
                                    </div>
                                    <div style="clear:both"></div>
                                    <div>
                                        <div class="box-check" style="float: left;"></div>
                                        <div style="float: left; margin-left:6px;">Result Of Inspection (Certificate)</div>
                                    </div>
                                    <div style="clear:both"></div>
                                    <div>
                                        <div class="box-check" style="float: left;"></div>
                                        <div style="float: left; margin-left:6px;">Product Safety Information Sheet</div>
                                    </div>
                                    <div style="clear:both"></div>
                                    <div>
                                        <div class="box-check" style="float: left;"></div>
                                        <div style="float: left; margin-left:6px;">Instruction Operator Manual</div>
                                    </div>
                                    <div style="clear:both"></div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <footer>
            Page <span class="pagenum"></span>
        </footer>
    </body>

</html>