<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF</title>
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
        margin: 10px 10px 15px 10px;
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
        /** Extra personal styles **/
        /* background-color: #03a9f4;
        color: white;
        text-align: center;
        line-height: 35px; */
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
                        <p>{{$po->vend_num}} - {{$po->vendor->vend_name}}</p>
                        <p>Attn : <br> {{$po->vendor->vend_name}}<br> {{$po->vendor->vend_addr}}</p>
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
        <div class="email-and-issued">
            <div style="float: left;">
                Email : {{$po->vendor->vend_email}}
            </div>
            <div style="float: right;">
                <b>Issued Date : {{date("Y-m-d", strtotime($po->po_date))}}</b>
            </div>
        </div>
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
                        <th>UNIT PRICE ({{$po->vendor->currency}})</th>
                        <th>TOTAL AMOUNT ({{$po->vendor->currency}})</th>
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
                        $arr_change_1[$po_line->po_change][] = $po_line->description;
                        $arr_change_2[$po_line->po_change][] = $po_line->due_date;
                        $arr_change_3[$po_line->po_change][] = (string) $po_line->order_qty;
                        $arr_change_4[$po_line->po_change][] = (string) $po_line->unit_price;
                    @endphp
                    @endforeach

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
                            @if( sizeof($arr_change_1[$po_line->po_change]) > 1 && array_count_values($arr_change_1[$po_line->po_change])[$po_line->description] !=  sizeof($arr_change_1[$po_line->po_change]))
                            <b><i><u>{{$po_line->description}}</u></i></b>
                            @else
                            {{$po_line->description}}
                            @endif
                        </td>
                        <td align="center">
                            @if( sizeof($arr_change_2[$po_line->po_change]) > 1 && array_count_values($arr_change_2[$po_line->po_change])[$po_line->due_date] !=  sizeof($arr_change_2[$po_line->po_change]))
                            <b><i><u>{{$due_date}}</u></i></b>
                            @else
                            {{$due_date}}
                            @endif
                        </td>
                        <td align="right">
                            @if( sizeof($arr_change_3[$po_line->po_change]) > 1 && array_count_values($arr_change_3[$po_line->po_change])[(string)$po_line->order_qty] !=  sizeof($arr_change_3[$po_line->po_change]))
                            <b><i><u>{{$po_line->order_qty}}</u></i></b>
                            @else
                            {{$po_line->order_qty}}
                            @endif
                        </td>
                        <td align="right" class="text-nowrap">
                            @if( sizeof($arr_change_4[$po_line->po_change]) > 1 && array_count_values($arr_change_4[$po_line->po_change])[(string)$po_line->unit_price] !=  sizeof($arr_change_4[$po_line->po_change]))
                            <b><i><u>{{$unit_price}}</u></i></b>
                            @else
                            {{$unit_price}}
                            @endif
                        </td>
                        <td align="right" class="text-nowrap"> {{$unit_price}}</td>
                        <td align="right" class="text-nowrap">{{number_format($po_line->order_qty*$po_line->unit_price,0,',','.')}}</td>
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
                            <div class="total-price"><b>{{$po->vendor->currency}} {{number_format($total,0,',','.')}}</b></div>
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