@if(!$request->ajax())
<!doctype html>
<html lang="{{ config('app.locale') }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{$item['name']}}</title>
        <link href="{{ asset('/css/datatables.min.css') }}" rel="stylesheet">
        <link href="{{ asset('/css/timing.css') }}" rel="stylesheet">
        <script src="{{ asset('/js/datatables.min.js') }}"></script>
        <script src="{{ asset('/js/timingHelpers.js') }}"></script>
    </head>
    <body>
        <div class="container" style="margin-top: 20px;">
        <div class="row">
        <div class="col-xs-9">
            <b>
            {{$summary['event']}}<br>
            {{$item['name'].' - '.$item['number'].$item['class']}}
            </b>
        </div>
        <div class="col-xs-3">
            <div class="dt-buttons btn-group">
            <a id="refreshBtn" class="btn btn-default btn-info btn-xs nohref" tabindex="0" aria-controls="timing" onclick="location.reload(false);">
            <span><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span> Refresh</span></a>
            </div>
        </div>
        </div>
@endif

        <table id="driver" class="dataTable table table-condensed table-striped table-bordered" cellspacing="0" width="100%">
            <thead>
                <tr>
                    @if ($grouping != 'Class')
                    <th>R</th>
                    @endif
                    <th>P</th>
                    <th class="text-center">Car</th>
                    <th class="text-center">Color</th>
                    @if ($idx_id != '' or $grouping != 'Class')
                    <th>Raw</th>
                    <th>Index</th>
                    @endif
                    @if ($grouping == 'Class')
                    <th>Total</th>
                    @else
                    <th>Pax</th>
                    @endif
                    <th>Diff</th>
                    <th>-1st</th>
                    @if ($grouping != 'Class')
                    <th>Pax%</th>
                    @endif
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
        
        <script>
        $(document).ready(function()
        {
            @if($request->ajax())
            $('#divModalHeader').html("<b>{{$summary['event']}}<br>{{$item['name'].' - '.$item['number'].$item['class']}}</b>");
            $('#divModalFooter').text("Generated: {{$summary['generated']}}");
            @endif
            var Grouping = '{{$grouping}}';
            var Name = '{!!urlencode($item['name'])!!}';
            var requrl = '{{$requrl}}';
            var Sort = '{{$sort}}';
            var Source = decodeURIComponent('{!!rawurlencode($src)!!}');
            var newurl = requrl + '?';
            if (Name.length > 0) {
                newurl += 'name=' + Name + '&';
            }
            @if(!$request->ajax())
            replaceState(newurl + toQueryString(Grouping, null, Sort, Source));
            @endif
        });
        </script>

@if(!$request->ajax())
        <div class="row">
        <div class="col-xs-12 small">
        Generated: {{$summary['generated']}}
        </div>
        </div>
    </div>
    </body>
</html>
@endif
