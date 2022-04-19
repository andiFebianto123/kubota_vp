@inject('constant', 'App\Helpers\Constant')
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Delivery Sheet</title>
    </head>
    <style>
        table td{
            border:1px solid #ffffff;
            background-color: #ffffff;
            font-size: 11px;
            font-family: Arial, Helvetica, sans-serif;
            padding: 4px;
            color: #000000;
        }
        td strong{
            font-size: 12px;
        }
        table{
            background-color: #000000;
        }
        ul{
            padding: 0px 20px;
        }
        strong{
            font-size: 13px;
            color: #000000;
        }
        .doc-requirement{
            border:1px solid #000; 
            margin-top: 10px;
            font-size: 12px;
            padding: 6px;
            font-family: Arial, Helvetica, sans-serif;
        }
        .title{
            font-family: Arial, Helvetica, sans-serif;
            font-weight: bold;
            font-size: 20px;
        }
        .title small{
            font-size: 12px;
            font-weight: normal;
        }
        .title_demo {
            font-family: Arial, Helvetica, sans-serif;
            font-weight: bold;
            font-size: 30px;
            margin-left: 12px;
        }
        .page_break { page-break-after: always; }
    </style>
    <body>
        <div>
            @foreach($deliveries as $key => $delivery)
                @php
                    $delivery_show = $delivery['delivery_show'];
                    $qr_code = $delivery['qr_code'];
                    $issued_mos = $delivery['issued_mos'];
                    $with_price = $delivery['with_price'];
                    $use_page_break = "";
                @endphp
                @if($key < sizeof($deliveries) - 1 && $key % 2 != 0)
                    @php $use_page_break = "page_break"; @endphp

                @endif
            <div @if($key % 2 != 0) class="{{ $use_page_break}}" @endif style="margin-bottom: 20px;"> 
                <div>
                    <div style="float: left;  position:relative;">
                        <span class="title">Delivery Sheet <small> - KUBOTA INDONESIA</small></span>
                    </div>
                    @if(env('APP_ENV') === 'local')
                        <div style="float: left; position:relative;">
                            <span class="title_demo">DEMO</span>
                        </div>
                    @endif
                    <div style="float: right;  position:relative;  padding-top:10px;">
                        <small style="font-size: 12px;">KIS - 404.0006</small>
                    </div>
                </div>
                <div style="clear:both;"></div>
                <hr>
                <div style="float:left; position:relative; width: 540px;">
                    <table width="98%" class="pdf-table">
                        <tbody>
                            <tr>
                                <td width="100%" colspan="4">
                                    Delivery Sheet No.<br>
                                    <strong>{{$delivery_show->ds_num}}-{{$delivery_show->ds_line}}</strong>
                                </td>
                            </tr>
                            <tr>
                                <td width="50%" colspan="2">
                                    Dlv.Date<br>
                                    <strong>{{date("Y-m-d", strtotime($delivery_show->shipped_date))}}</strong>
                                </td>
                                <td width="50%" colspan="2">
                                    P/O Due Date<br>
                                    <strong>{{date("Y-m-d", strtotime($delivery_show->due_date))}}</strong>
                                </td>
                            </tr>
                            <tr>
                                <td width="50%" colspan="2">
                                    Vend. Name<br>
                                    <strong>{{$delivery_show->vendor_name}}</strong>
                                </td>
                                <td width="25%">
                                    Vend. No<br>
                                    <strong>{{$delivery_show->vendor_number}}</strong>
                                </td>
                                <td width="25%">
                                    Vendor Dlv. No<br>
                                    <strong @if(strlen($delivery_show->no_surat_jalan_vendor) > 15) style="font-size:10px;" @endif>
                                        {{$delivery_show->no_surat_jalan_vendor}}
                                    </strong>
                                </td>
                            </tr>
                            <tr>
                                <td width="25%">
                                    Order No.<br>
                                    <strong>{{$delivery_show->po_number}}-{{$delivery_show->po_line}}</strong>
                                </td>
                                <td width="25%">
                                    Order QTY<br>
                                    <strong style="text-align: right;">{{$delivery_show->shipped_qty}}</strong>
                                </td>
                                <td width="25%">
                                    Dlv.QTY<br>
                                    <strong style="text-align: right;">{{$delivery_show->order_qty}}</strong>
                                </td>
                                <td width="25%">
                                    Unit Price<br>
                                    <strong>TBA</strong>
                                </td>
                            </tr>

                            <tr>
                                <td width="25%">
                                    Part No.<br>
                                    <strong>{{$delivery_show->item}}</strong>
                                </td>
                                <td width="25%">
                                    Currency<br>
                                    <strong>{{$delivery_show->currency}}</strong>
                                </td>
                                <td width="25%">
                                    Tax Status<br>
                                    <strong class="right">{{$delivery_show->tax_status}}</strong>
                                </td>
                                <td width="25%">
                                    Amount<br>
                                    <strong>TBA</strong>
                                </td>
                            </tr>
                            <tr>
                                <td width="50%" colspan="2">
                                    Part Name<br>
                                    <strong>{{$delivery_show->description}}</strong>
                                </td>
                                <td width="25%">
                                    WH<br>
                                    <strong>{{$delivery_show->wh}}</strong>
                                </td>
                                <td width="25%">
                                    Location<br>
                                    <strong>{{($delivery_show->location) ? $delivery_show->location:'-'}}</strong>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <table width="98%" style="margin-top: 10px;" class="pdf-table">
                        <tbody>
                            <tr>
                                <td width="15%" align="center" style="padding:0px;" valign="top">
                                    <small>VENDOR</small>
                                    <div style="width: 100%; border-bottom:1px solid #000000; height:1px;"></div>
                                </td>
                                <td valign="top" height="117px">
                                    <small>QC</small> : <strong>@if($delivery_show->inspection_flag == 1) YES @else NO @endif</strong><br>
                                    <small>NOTES</small> :
                                    @foreach($issued_mos as $imo)
                                    <br>
                                    <span style="font-size: 10px;"> - {{$imo->matl_item}} {{$imo->description}}</span>
                                    @endforeach
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div style="float:right; position:relative; width:168px;">
                    <div>
                        <img src="data:image/png;base64, {{ base64_encode(QrCode::format('png')->size(168)->generate($qr_code)) }} ">
                    </div>
                    <div class="doc-requirement" style="height: 205px;">
                        <strong>Document Requirements</strong>
                        <hr>
                        <ul>
                            <li>Material Mill Sheet</li>
                            <li>Material Safety Data Sheet</li>
                            <li>Result of Inspection (Certificate)</li>
                            <li>Product Safaty Information Sheet</li>
                            <li>Instruction Operator Manual</li>
                        </ul>
                    </div>
                </div>
                <div style="clear:both;"></div>
                <div style="text-align: right; font-size:11px;">
                    <p>Print Date/Time {{date('d-M-Y H:i:s')}}</p>
                </div>
            </div>
            @endforeach
        </div>
    </body>
</html>