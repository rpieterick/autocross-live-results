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
                    <th></th>
                    <th class="text-center">Car</th>
                    <th class="text-center">Color</th>
                    @if ($idx_id != '' or $grouping != 'Class')
                    @if ($sort != 'Pax' and $grouping != 'Class')
                    <th id="thDriverRaw" class="sorting_asc">Raw</th>
                    @elseif ($grouping != 'Class')
                    <th id="thDriverRaw" class="sorting">Raw</th>
                    @else
                    <th id="thDriverRaw">Raw</th>
                    @endif
                    <th>Index</th>
                    @endif
                    @if ($grouping == 'Class')
                    <th>Total</th>
                    @else
                    @if ($sort == 'Pax')
                    <th id="thDriverPax" class="sorting_asc">Pax</th>
                    @else
                    <th id="thDriverPax" class="sorting">Pax</th>
                    @endif
                    @endif
                    <th>Diff</th>
                    <th>-1st</th>
                    @if ($grouping != 'Class')
                    <th>Pax%</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                <tr>
                    <?php
                    $colkeys = ['position', 'group', 'car', 'car_color'];
                    if ($grouping == 'Class') {
                        if ($idx_id != '') {
                            $colkeys = array_merge(
                                $colkeys,
                                ['best_raw', 'pax', 'total', 'difference', 'from_first']
                            );
                        } else {
                            $colkeys = array_merge(
                                $colkeys,
                                ['total', 'difference', 'from_first']
                            );
                        }
                    } else {
                        $colkeys = array_merge(
                            ['rank'],
                            $colkeys,
                            ['best_raw', 'pax', 'best_pax', 'difference', 'from_first', 'paxp']
                        );
                    }
                    ?>
                    @foreach($colkeys as $key)
                    @if ($key == 'total'
                        or ($key == 'best_pax' and $grouping != 'Class' and $sort == 'Pax')
                        or ($key == 'best_raw' and $grouping != 'Class' and $sort != 'Pax')
                    )
                    <td><b>{{$item[$key]}}</b></td>
                    @else
                    <td>{{$item[$key]}}</td>
                    @endif
                    @endforeach
                </tr>
            </tbody>
        </table>

        @if($summary['runs'] > 0)
        <table id="runs" class="dataTable table table-condensed table-striped table-bordered" cellspacing="0" width="100%">
            <thead>
                <tr>
                    @for($i=1; $i <= $summary['runs']; $i++)
                    <th>Run{{$i}}</th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                <tr>
                    @for($i=1; $i <= $summary['runs']; $i++)
                    @if (starts_with($item['run'.$i], '<b>'))
                    <td class="bestt">
                    @else
                    <td>
                    @endif
                    {{$time = str_replace('<b>', '', str_replace('</b>', '', $item['run'.$i]))}}
                    </td>
                    @endfor
                </tr>
            </tbody>
        </table>

        @if($idx_id != '' or $grouping != 'Class')
        <table id="paxes" class="table table-condensed table-striped table-bordered" cellspacing="0" width="100%">
            <thead>
                <tr>
                    @for($i=1; $i <= $summary['runs']; $i++)
                    @if($idx_id != '' and $grouping == 'Class')
                    <th>Tot{{$i}}</th>
                    @else
                    <th>Pax{{$i}}</th>
                    @endif
                    @endfor
                </tr>
            </thead>
            <tbody>
                <tr>
                    @for($i=1; $i <= $summary['runs']; $i++)
                    @if (starts_with($item['runpax'.$i], '<b>'))
                    <td class="bestt">
                    @else
                    <td>
                    @endif
                    {{$time = str_replace('<b>', '', str_replace('</b>', '', $item['runpax'.$i]))}}
                    </td>
                    @endfor
                </tr>
            </tbody>
        </table>
        @endif
        @endif
        <script>
        $(document).ready(function()
        {
            @if($request->ajax())
            $('#divModalHeader').html(
                "<b>{{$summary['event']}}<br>{{$item['name'].' - '.$item['number'].$item['class']}}</b>"
            );
            $('#divModalFooter').text("Generated: {{$summary['generated']}}");
            @endif
            var Grouping = '{{$grouping}}';
            var Name = '{!!urlencode($item['name'])!!}';
            var requrl = '{{$requrl}}';
            var Sort = '{{$sort}}';
            var Source = decodeURIComponent('{!!rawurlencode($src)!!}');

            if (Grouping === 'Class') {
                var grprow = 1;
            } else {
                var grprow = 2;
                var notorderable = [0, 1, 3, 4, 6, 8, 9, 10];
                if (Sort === 'Pax') {
                    var order = [ [grprow + 5, 'asc'] ];
                } else {
                    var order = [ [grprow + 3, 'asc'] ];
                }
            }

            var newurl = requrl + '?';
            if (Name.length > 0) {
                newurl += 'name=' + Name + '&';
            }

            @if(!$request->ajax())
            replaceState(newurl + toQueryString(null, Grouping, null, Sort, Source));
            @endif

            $('#thDriverRaw').on( 'click', function (e)
            {
                if ($(this).hasClass('sorting')) {
                    Sort = 'Raw';
                    newurl += toQueryString(null, Grouping, null, Sort, Source);
                    @if($request->ajax())
                    $('#myModal').find(".modal-body").load(newurl);
                    @else
                    pushState(newurl);
                    location.reload(false);
                    @endif
                }

                return false;
            });

            $('#thDriverPax').on( 'click', function (e)
            {
                if ($(this).hasClass('sorting')) {
                    Sort = 'Pax';
                    newurl += toQueryString(null, Grouping, null, Sort, Source);
                    @if($request->ajax())
                    $('#myModal').find(".modal-body").load(newurl);
                    @else
                    pushState(newurl);
                    location.reload(false);
                    @endif
                    
                }

                return false;
            });

            $('#driver').DataTable(
            {
                info: false,
                ordering: false,
                paging: false,
                searching: false,
                scrollX: true,
                columnDefs: [
                    @if ($grouping != 'Class')
                    { orderSequence: [ "asc" ], "targets": [ grprow + 3 ] },
                    { orderSequence: [ "asc" ], "targets": [ grprow + 5 ] },
                    { orderable: false, targets: notorderable },
                    @endif
                    { visible: false, targets: grprow }
                ],
                @if ($grouping != 'Class')
                order: order,
                @endif
                rowGroup: {
                    dataSrc: grprow,
                    enable: {{(in_array('GrpRow', $options)) ? 'true' : 'false'}}
                }
            });
            @if($summary['runs'] > 0)

            $('#runs').DataTable(
            {
                info: false,
                ordering: false,
                paging: false,
                searching: false,
                scrollX: true,
            });
            @if($idx_id != '' or $grouping != 'Class')

            $('#paxes').DataTable(
            {
                info: false,
                ordering: false,
                paging: false,
                searching: false,
                scrollX: true,
            });
            @endif
            @endif

            window.onpopstate = function (event) {
                location.reload(false);
            }
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
