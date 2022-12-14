<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF</title>
</head>
<style>
    table{
        font-size: 12px;
    }
</style>
<body>
    <span>Order Sheet PT KUBOTA INDONESIA</span>
    <div>
        <table class="table">
            <tr>
                <td>PO Number</td>
                <td>: {{$po->po_num}}</td>
            </tr>
            <tr>
                <td>Vendor</td>
                <td>: {{$po->vend_num}}</td>
            </tr>
            <tr>
                <td>PO Date</td>
                <td>: {{date('Y-m-d', strtotime($po->po_date))}}</td>
            </tr>
            <tr>
                <td>Email Sent</td>
                <td>: {{($po->email_flag) ? "✓":"-"}}</td>
            </tr>
        </table>
    </div>

    <div>
        <h5>List PO Line</h5>
        <table class="table table-striped mb-0 table-responsive" >
            <thead  style="border-top: 1px solid #000000 ; border-bottom: 1px solid #000000 ;">
                <tr>
                    <th>PO Number</th>
                    <th>Status</th>
                    <th>Item</th>
                    <th>Vendor Name</th>
                    <th>Description</th>
                    <th>Qty</th>
                    <th>UM</th>
                    <th>Due Date</th>
                    <th>Tax (%)</th>
                    <th>Unit Price</th>
                    <th>Total Price</th>
                    <th>Status Accept</th>
                    <th>Read By</th>
                    <th>Read At</th>
                    @if(strpos(strtoupper(App\Helpers\Constant::getRole()), 'PTKI'))
                        <th>Created At</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($po_lines as $key => $po_line)
                <tr>
                    <td class="text-nowrap">{{$po_line->po_num}}-{{$po_line->po_line}}</td>
                    <td>
                        <span class="{{$arr_po_line_status[$po_line->status]['color']}}">
                            {{$arr_po_line_status[$po_line->status]['text']}}
                        </span>
                    </td>
                    <td>{{$po_line->item}}</td>
                    <td>{{$po_line->vendor_name}}</td>
                    <td>{{$po_line->description}}</td>
                    <td>{!! $po_line->order_qty !!}</td>
                    <td>{{$po_line->u_m}}</td>
                    <td>{!! $po_line->due_date !!}</td>
                    <td>{{$po_line->tax}}</td>
                    <td class="text-nowrap">{!! $po_line->unit_price !!}</td>
                    <td class="text-nowrap">{!! $po_line->total_price !!}</td>
                    <td>{!! $po_line->flag_accept !!}</td>
                    <td>{{$po_line->read_by_user}}</td>
                    <td>{{$po_line->read_at}}</td>
                    @if(strpos(strtoupper(App\Helpers\Constant::getRole()), 'PTKI'))
                        <td>{{$po_line->created_at}}</td>
                    @endif
                   
                </tr>
                @endforeach

            </tbody>
        </table>

    </div>

    
</body>

</html>