<!doctype html>
<html lang="{{ config('app.locale') }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf_token" content="{{csrf_token()}}">

        <title>{{ config('timing.title', 'Live Results') }}</title>
        <link href="{{ asset('/css/datatables.min.css') }}" rel="stylesheet">
        <link href="{{ asset('/css/timing.css') }}" rel="stylesheet">
        <script src="{{ asset('/js/datatables.min.js') }}"></script>
        <script src="{{ asset('/js/timingHelpers.js') }}"></script>
        <script src="{{ asset('/js/js.cookie.js') }}"></script>
    </head>
    <body>
        <nav class="navbar navbar-inverse navbar-fixed-top">
          <div class="container">
            <div class="navbar-header">
              <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
              </button>
              <a class="navbar-brand" href="/" style="color: #fff;">{{ config('timing.title', 'Live Results') }}</a>
            </div>
            <div id="navbar" class="navbar-collapse collapse">
              <ul class="nav navbar-nav">
                <li class="dropdown" id="ddColumns">
                  <a class="dropdown-toggle nohref" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Columns <span class="caret"></span></a>
                  <ul class="dropdown-menu">
                    <li>
                        <a class="Columns hidden-xs nohref" data-value="Car" tabIndex="-1">
                            <div class="checkbox">
                                <input type="checkbox"/>Car
                            </div>
                        </a>
                    </li>
                    <li>
                        <a class="Columns visible-xs nohref" data-value="Car" tabIndex="-1" data-toggle="collapse" data-target=".navbar-collapse">
                            <div class="checkbox">
                                <input type="checkbox"/>Car
                            </div>
                        </a>
                    </li>
                    <li>
                        <a class="Columns hidden-xs nohref" data-value="Color" tabIndex="-1">
                            <div class="checkbox">
                                <input type="checkbox"/>Color
                            </div>
                        </a>
                    </li>
                    <li>
                        <a class="Columns visible-xs nohref" data-value="Color" tabIndex="-1" data-toggle="collapse" data-target=".navbar-collapse">
                            <div class="checkbox">
                                <input type="checkbox"/>Color
                            </div>
                        </a>
                    </li>
                    <li>
                        <a class="Columns hidden-xs nohref" data-value="Runs" tabIndex="-1">
                            <div class="checkbox">
                                <input type="checkbox" checked/>Runs
                            </div>
                        </a>
                    </li>
                    <li>
                        <a class="Columns visible-xs nohref" data-value="Runs" tabIndex="-1" data-toggle="collapse" data-target=".navbar-collapse">
                            <div class="checkbox">
                                <input type="checkbox" checked/>Runs
                            </div>
                        </a>
                    </li>
                    <li>
                        <a class="Columns hidden nohref" data-value="Index" id="aIndex" tabIndex="-1">
                            <div class="checkbox">
                                <input type="checkbox"/>Index
                            </div>
                        </a>
                    </li>
                    <li>
                        <a class="Columns hidden nohref" data-value="Index" id="aIndex-xs" tabIndex="-1" data-toggle="collapse" data-target=".navbar-collapse">
                            <div class="checkbox">
                                <input type="checkbox"/>Index
                            </div>
                        </a>
                    </li>
                    <li>
                        <a class="Columns hidden nohref" data-value="Pax" id="aPax" tabIndex="-1">
                            <div class="checkbox">
                                <input type="checkbox" checked/>Pax
                            </div>
                        </a>
                    </li>
                    <li>
                        <a class="Columns hidden nohref" data-value="Pax" id="aPax-xs" tabIndex="-1" data-toggle="collapse" data-target=".navbar-collapse">
                            <div class="checkbox">
                                <input type="checkbox" checked/>Pax
                            </div>
                        </a>
                    </li>
                    <li>
                        <a class="Columns hidden-xs nohref" data-value="Diff" tabIndex="-1">
                            <div class="checkbox">
                                <input type="checkbox" checked/>Diff
                            </div>
                        </a>
                    </li>
                    <li>
                        <a class="Columns visible-xs nohref" data-value="Diff" tabIndex="-1" data-toggle="collapse" data-target=".navbar-collapse">
                            <div class="checkbox">
                                <input type="checkbox" checked/>Diff
                            </div>
                        </a>
                    </li>
                    <li>
                        <a class="Columns hidden-xs nohref" data-value="-1st" tabIndex="-1">
                            <div class="checkbox">
                                <input type="checkbox" checked/>-1st
                            </div>
                        </a>
                    </li>
                    <li>
                        <a class="Columns visible-xs nohref" data-value="-1st" tabIndex="-1" data-toggle="collapse" data-target=".navbar-collapse">
                            <div class="checkbox">
                                <input type="checkbox" checked/>-1st
                            </div>
                        </a>
                    </li>
                    <li>
                        <a class="Columns hidden nohref" data-value="PaxP" id="aPaxP" tabIndex="-1">
                            <div class="checkbox">
                                <input type="checkbox" checked/>Pax%
                            </div>
                        </a>
                    </li>
                    <li>
                        <a class="Columns hidden nohref" data-value="PaxP" id="aPaxP-xs" tabIndex="-1" data-toggle="collapse" data-target=".navbar-collapse">
                            <div class="checkbox">
                                <input type="checkbox" checked/>Pax%
                            </div>
                        </a>
                    </li>
                  </ul>
                </li>
                <li class="dropdown">
                  <a class="dropdown-toggle nohref" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Grouping <span class="caret"></span></a>
                  <ul class="dropdown-menu">
                    @foreach($GROUPINGS as $grp)
                    <li>
                        <a class="Grouping nohref" data-value="{{$grp}}" tabIndex="-1">
                            <div class="radio">
                                <input type="radio"/>{{$grp}}
                            </div>
                        </a>
                    </li>
                    @endforeach
                  </ul>
                </li>
                <li class="dropdown" id="ddOptions">
                  <a class="dropdown-toggle nohref" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Options <span class="caret"></span></a>
                  <ul class="dropdown-menu">
                    <li>
                        <a class="Options hidden-xs nohref" data-value="GrpRow" tabIndex="-1">
                            <div class="checkbox">
                                <input type="checkbox" checked/>Grouping Row
                            </div>
                        </a>
                    </li>
                    <li>
                        <a class="Options visible-xs nohref" data-value="GrpRow" tabIndex="-1" data-toggle="collapse" data-target=".navbar-collapse">
                            <div class="checkbox">
                                <input type="checkbox" checked/>Grouping Row
                            </div>
                        </a>
                    </li>
                    <li>
                        <a class="Options hidden nohref" data-value="Ladies" id="aLadies" tabIndex="-1">
                            <div class="checkbox">
                                <input type="checkbox" checked/>Ladies Categories
                            </div>
                        </a>
                    </li>
                    <li>
                        <a class="Options hidden nohref" data-value="Ladies" id="aLadies-xs" tabIndex="-1" data-toggle="collapse" data-target=".navbar-collapse">
                            <div class="checkbox">
                                <input type="checkbox" checked/>Ladies Categories
                            </div>
                        </a>
                    </li>
                    <li>
                        <a class="Options hidden nohref" data-value="PaxRuns" id="aPaxRuns" tabIndex="-1">
                            <div class="checkbox">
                                <input type="checkbox"/>PAX Runs with PAX Order
                            </div>
                        </a>
                    </li>
                    <li>
                        <a class="Options hidden nohref" data-value="PaxRuns" id="aPaxRuns-xs" tabIndex="-1" data-toggle="collapse" data-target=".navbar-collapse">
                            <div class="checkbox">
                                <input type="checkbox"/>PAX Runs with PAX Order
                            </div>
                        </a>
                    </li>
                    <li>
                        <a class="Options hidden-xs nohref" data-value="Search" tabIndex="-1">
                            <div class="checkbox">
                                <input type="checkbox"/>Search
                            </div>
                        </a>
                    </li>
                    <li>
                        <a class="Options visible-xs nohref" data-value="Search" tabIndex="-1" data-toggle="collapse" data-target=".navbar-collapse">
                            <div class="checkbox">
                                <input type="checkbox"/>Search
                            </div>
                        </a>
                    </li>
                  </ul>
                </li>
                <li class="dropdown">
                  <a class="dropdown-toggle nohref" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Source <span class="caret"></span></a>
                  <ul class="dropdown-menu">
                    @foreach(array_keys(config("timing.source")) as $src)
                    <li>
                        <a class="Source nohref" data-value="{{$src}}" tabIndex="-1">
                            <div class="radio">
                                <input type="radio"{{$src == array_keys(config("timing.source"))[0] ? ' checked' : ''}}/>{{$src}}
                            </div>
                        </a>
                    </li>
                    @endforeach
                  </ul>
                </li>
              </ul>
              <div class="row">
              <div class="text-center" id="divEvent" style="color: #fff; margin-top: 5px; padding-right: 15px;">
              {{$summary['event']}}<br>{{$summary['disclaimer']}}
              </div>
              </div>
            </div><!--/.nav-collapse -->
          </div>
        </nav>
        <div class="container" style="margin-top: 55px;">
        <div class="row">
            <div class="modal" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <div id="divModalHeader"></div>
                  </div>
                  <div class="modal-body">
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-info pull-right" data-dismiss="modal">Close</button>
                    <div class="small pull-left" id="divModalFooter"></div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-xs-9 small" id="divGenerated">
                Generated: {{$summary['generated']}}
            </div>
            <div class="col-xs-3">
                <div class="dt-buttons btn-group">
                <a id="refreshBtn" class="btn btn-default btn-info btn-xs nohref" tabindex="0" aria-controls="timing"><span><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span> Refresh</span></a>
                </div>
            </div>
        </div>
        
        <div class="row">
        <div class="col-xs-12">
        <table id="timing" class="table table-condensed table-striped table-bordered" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>R</th>
                    <th>P</th>
                    <th></th>
                    <th>Cls</th>
                    <th>Nbr</th>
                    <th class="text-center">Name</th>
                    <th class="text-center">Car</th>
                    <th class="text-center">Color</th>
                    @for($i=1; $i <= $summary['runs']; $i++)
                    <th id="thRun{{$i}}">Run{{$i}}</th>
                    @endfor
                    <th>Total</th>
                    <th id="thRaw">Raw</th>
                    <th>Index</th>
                    <th id="thPax">Pax</th>
                    <th>Diff</th>
                    <th>-1st</th>
                    <th>Pax%</th>
                </tr>
            </thead>
        </table>
        </div>
        </div>
        </div>
        <div id="divReqUrl" style="display: none;">{{$requrl}}</div>
        <div id="divRuns" style="display: none;">{{$summary['runs']}}</div>
        <div id="divCOLUMNS" style="display: none;">{!!rawurlencode(json_encode($COLUMNS))!!}</div>
        <div id="divGROUPINGS" style="display: none;">{!!rawurlencode(json_encode($GROUPINGS))!!}</div>
        <div id="divOPTIONS" style="display: none;">{!!rawurlencode(json_encode($OPTIONS))!!}</div>
        <div id="divSORTS" style="display: none;">{!!rawurlencode(json_encode($SORTS))!!}</div>
        <div id="divSource" style="display: none;">{!!rawurlencode(array_keys(config("timing.source", ["localhost" => ""]))[0])!!}</div>
        <div id="divSOURCES" style="display: none;">{!!rawurlencode(json_encode(array_keys(config("timing.source", ["localhost" => ""]))))!!}</div>
        <script src="{{ asset('/js/timingIndex.js') }}"></script>
    </body>
</html>
