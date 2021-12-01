<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excel</title>
</head>
<style>
    table td{
        border:1px solid #ffffff;
        background-color: #ffffff;
        font-size: 12px;
        font-family: Arial, Helvetica, sans-serif;
        padding: 4px;
        color: #000000;
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
    .page_break { page-break-after: always; }
</style>
<body>
    <div>
        @foreach($deliveries as $key => $delivery)
            @php
                $delivery_show = $delivery['delivery_show'];
                $qr_code = $delivery['qr_code'];
                $with_price = $delivery['with_price'];
            @endphp
        <div @if($key % 2 != 0) class="page_break" @endif style="margin-bottom: 40px;"> 
            <div style="float:left; position:relative; width: 540px;">
                <table width="98%" class="pdf-table">
                    <tbody>
                        <tr>
                            <td width="100%" colspan="4">Delivery Sheet No.<br><strong>{{$delivery_show->ds_num}}</strong></td>
                        </tr>
                        <tr>
                            <td width="50%" colspan="2">Dlv.Date<br><strong>{{date("Y-m-d", strtotime($delivery_show->shipped_date))}}</strong></td>
                            <td width="50%" colspan="2">P/O Due Date<br><strong>{{date("Y-m-d", strtotime($delivery_show->due_date))}}</strong></td>
                        </tr>
                        <tr>
                            <td width="50%" colspan="2">Vend. No<br><strong>{{$delivery_show->vendor_number}}</strong></td>
                            <td width="25%">Vend. Name<br><strong>{{$delivery_show->vendor_name}}</strong></td>
                            <td width="25%">Vendor Dlv. No<br><strong>{{$delivery_show->no_surat_jalan_vendor}}</strong></td>
                        </tr>
                        <tr>
                            <td width="25%">Order No.<br><strong>{{$delivery_show->po_number}}-{{$delivery_show->po_line}}</strong></td>
                            <td width="25%">Order QTY<br><strong style="text-align: right;">{{$delivery_show->order_qty}}</strong></td>
                            <td width="25%">Dlv.QTY<br><strong style="text-align: right;">{{$delivery_show->shipped_qty}}</strong></td>
                            <td width="25%">Unit Price<br><strong class="right">{{$delivery_show->currency." " . number_format($delivery_show->unit_price,0,',','.')}}</strong></td>
                        </tr>

                        <tr>
                            <td width="25%">Part No.<br><strong>-</strong></td>
                            <td width="25%">Currency<br><strong>{{$delivery_show->currency}}</strong></td>
                            <td width="25%">Tax Status<br><strong class="right">{{$delivery_show->tax_status}}</strong></td>
                            <td width="25%">Amount<br><strong class="right">{{$delivery_show->currency." " . number_format($delivery_show->order_qty*$delivery_show->unit_price,0,',','.')}}</strong></td>
                        </tr>
                        <tr>
                            <td width="50%" colspan="2">Part Name<br><strong>{{$delivery_show->description}}</strong></td>
                            <td width="25%">WH<br><strong>{{$delivery_show->wh}}</strong></td>
                            <td width="25%">Location<br><strong>{{($delivery_show->location) ? $delivery_show->location:'-'}}</strong></td>
                        </tr>
                    </tbody>
                </table>
                <table width="98%" style="margin-top: 10px;" class="pdf-table">
                    <tbody>
                        <tr>
                            <td width="15%" align="center"><small>VENDOR</small></td>
                            <td rowspan="2" valign="top">
                                <small>QC</small> : <strong>NO</strong><br>
                                <small>NOTES</small> :
                            </td>
                        </tr>
                        <tr>
                            <td height="63px"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div style="float:right; position:relative; width:168px;">
                <div>
                    <img src="data:image/png;base64, {{ base64_encode(QrCode::format('png')->size(168)->generate('Make me into an QrCode!')) }} ">
                </div>
                <div class="doc-requirement">
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
            <div style="clear: both; float:none;"></div>
        </div>
        @endforeach
    </div>
</body>

</html>