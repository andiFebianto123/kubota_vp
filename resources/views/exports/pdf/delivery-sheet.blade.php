<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excel</title>
</head>

<body>
    <h1>Delivery Sheet</h1>
    <span>PT KUBOTA INDONESIA</span>
    <div>
        <div style="float:left; position:relative; width: 80%;">
            <table border="1pt" width="100%">
                <tbody>
                    <tr>
                        <td width="50%" colspan="2">Delivery Sheet No.<br><strong>{{$delivery_show->ds_num}}</strong></td>
                        <td width="50%" colspan="2"></td>
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
                    @if($with_price  == 'yes')
                    <tr>
                        <td width="25%">Order No.<br><strong>{{$delivery_show->po_number}}-{{$delivery_show->po_line}}</strong></td>
                        <td width="25%">Order QTY<br><strong style="text-align: right;">{{$delivery_show->order_qty}}</strong></td>
                        <td width="25%">Dlv.QTY<br><strong style="text-align: right;">{{$delivery_show->shipped_qty}}</strong></td>
                        <td width="25%">Unit Price<br><strong class="right">{{"IDR " . number_format($delivery_show->unit_price,0,',','.')}}</strong></td>
                    </tr>
                    @else
                    <tr>
                        <td width="50%" colspan="2">Order No.<br><strong>{{$delivery_show->po_number}}-{{$delivery_show->po_line}}</strong></td>
                        <td width="25%">Order No.<br><strong>{{$delivery_show->po_number}}-{{$delivery_show->po_line}}</strong></td>
                        <td width="25%">Order QTY<br><strong style="text-align: right;">{{$delivery_show->order_qty}}</strong></td>
                        <td width="25%">Dlv.QTY<br><strong style="text-align: right;">{{$delivery_show->shipped_qty}}</strong></td>
                    </tr>
                    @endif

                    <tr>
                        <td width="25%">Part No.<br><strong>-</strong></td>
                        <td width="25%">Currency<br><strong>{{$delivery_show->currency}}</strong></td>
                        <td width="25%">Tax Status<br><strong class="right">{{$delivery_show->tax_status}}</strong></td>
                        <td width="25%">Amount<br><strong class="right">-</strong></td>
                    </tr>
                    <tr>
                        <td width="50%" colspan="2">Part Name<br><strong>{{$delivery_show->description}}</strong></td>
                        <td width="25%">WH<br><strong>{{$delivery_show->wh}}</strong></td>
                        <td width="25%">Location<br><strong>{{$delivery_show->location}}</strong></td>
                    </tr>
                </tbody>
            </table>
            <table border="1px" width="98%" style="margin-top: 10px;" class="pdf-table">
                <tbody>
                    <tr>
                        <td width="15%" align="center"><small>VENDOR</small></td>
                        <td rowspan="2" valign="top">
                            <small>QC</small> : <strong>NO</strong><br>
                            <small>NOTES</small> :
                        </td>
                    </tr>
                    <tr>
                        <td height="80px"></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div style="float:right; position:relative; width:20%;">
            <div>
                <img src="data:image/png;base64, {{ base64_encode(QrCode::format('png')->size(100)->generate('Make me into an QrCode!')) }} ">
            </div>
            <div style="border:1px solid #000; margin-top: 10px; width: 100%; max-width:220px; padding: 5px 10px 0 10px;">
                <strong>Document Requirements</strong>
                <ul>
                    <li>Material Mill Sheet</li>
                    <li>Material Safety Data Sheet</li>
                    <li>Result of Inspection (Certificate)</li>
                    <li>Product Safaty Information Sheet</li>
                    <li>Instruction Operator Manual</li>
                </ul>
            </div>
        </div>
    </div>
</body>

</html>