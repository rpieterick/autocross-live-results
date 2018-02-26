$(document).ready(function()
{
    const r = {'R': 0, 'P': 1, 'G': 2, 'Cls': 3, 'Nbr': 4, 'Name': 5, 'Car': 6, 'Color':7};
    const o = {'Total': 8, 'Raw': 9, 'Index': 10, 'Pax': 11, 'Diff': 12, '-1st': 13, 'PaxP': 14};
    var adjust = false;
    var heightAdj = 137;
    var Columns = [];
    var COLUMNS = JSON.parse(decodeURIComponent($('#divCOLUMNS').text()));
    var Grouping = 'Class';
    var GROUPINGS = JSON.parse(decodeURIComponent($('#divGROUPINGS').text()));
    var Options = [];
    var OPTIONS = JSON.parse(decodeURIComponent($('#divOPTIONS').text()));
    var SORTS = JSON.parse(decodeURIComponent($('#divSORTS').text()));
    var Source = decodeURIComponent($('#divSource').text());
    var SOURCES = JSON.parse(decodeURIComponent($('#divSOURCES').text()));
    var order = [];
    var redraw = false;
    var rowGroup = true;
    var Sort = 'Raw';
    var Search = false;
    var summary;
    var requrl = $('#divReqUrl').text();
    var runs = parseInt($('#divRuns').text());
    var notorderable = [
        r['R'], r['P'], r['Cls'], r['Nbr'], r['Name'], r['Car'], r['Color'],
        runs + o['Total'],
        runs + o['Index'],
        runs + o['Diff'],
        runs + o['-1st'],
        runs + o['PaxP']
    ];
    var notsearchable = [ r['R'], r['P'] ];
    var notvisible = [
        r['R'], r['G'], r['Car'], r['Color'],
        runs + o['Raw'],
        runs + o['Index'],
        runs + o['Pax'],
        runs + o['Diff'],
        runs + o['-1st'],
        runs + o['PaxP']
    ];

    for (c = 8; c < (runs + o['Total']); c++) {
        notorderable.push(c);
        notvisible.push(c);
    }

    for (c = 8; c < (runs + o['PaxP'] + 1); c++) {
        notsearchable.push(c);
    }

    $('a.Grouping').each( function ()
    {
        inp = $(this).find('input');
        if (inp.height() > 13) {
            inp.css('margin-top', (20 - 1 - inp.height()) / 2);
        }
    });

    $('a.Source').each( function ()
    {
        inp = $(this).find('input');
        if (inp.height() > 13) {
            inp.css('margin-top', (20 - 1 - inp.height()) / 2);
        }
    });

    qs = fromQueryString();

    Grouping = getGrouping(qs, Grouping, GROUPINGS);
    Sort = getSort(qs, Sort, SORTS, Grouping);
    if (Grouping !== 'Class') {
        if (Sort === "Pax") {
            order = [ [runs + o['Pax'], 'asc'] ];
        } else {
            order = [ [runs + o['Raw'], 'asc'] ];
        }
    }

    $('a.Grouping:not(:contains("' + Grouping + '"))').each( function ()
    {
        $(this).find('input').prop("checked", false);
    });
    $('a.Grouping:contains("' + Grouping + '")').each( function ()
    {
        $(this).find('input').prop("checked", true);
    });

    if (Grouping !== 'Class') {
        if (order === [ [runs + o['Pax'], 'asc'] ]) {
            notvisible.splice(notvisible.indexOf(runs + o['Pax']), 1);
            $('#thPax').addClass('sorting_asc');
            $('#thRaw').addClass('sorting');
        } else {
            notvisible.splice(notvisible.indexOf(runs + o['Raw']), 1);
            $('#thRaw').addClass('sorting_asc');
            $('#thPax').addClass('sorting');
        }

        notvisible.push(runs + o['Total']);
    }

    Columns = getColumns(qs, COLUMNS, Grouping);
    initColumns();
    Options = getOptions(qs, OPTIONS, Grouping);
    initOptions();
    Source = getSource(qs, Source, SOURCES);

    $('a.Source:not(:contains("' + Source.replace(/\"/g, '\\\"') + '"))').each( function ()
    {
        $(this).find('input').prop("checked", false);
    });

    $('a.Source:contains("' + Source.replace(/\"/g, '\\\"') + '")').each( function ()
    {
        $(this).find('input').prop("checked", true);
    });

    replaceState(requrl + '?' + toQueryString(Columns, Grouping, Options, Sort, Source));

    $('#thRaw').on( 'click', function (e)
    {
        if ($(this).hasClass('sorting')) {
            Cookies.set(Grouping + '-sort', 'Raw', { expires: 2000 });
            Sort = 'Raw';
            pushState(requrl + '?' + toQueryString(Columns, Grouping, Options, Sort, Source));
            $('#thRaw').removeClass('sorting').addClass('sorting_asc');
            $('#thPax').removeClass('sorting_asc').addClass('sorting');
            for (i = 1; i <= runs; i++) {
                $('#thRun' + i).text('Run' + i);
            }
        }
    });

    $('#thPax').on( 'click', function (e)
    {
        if ($(this).hasClass('sorting')) {
            Cookies.set(Grouping + '-sort', 'Pax', { expires: 2000 });
            Sort = 'Pax';
            pushState(requrl + '?' + toQueryString(Columns, Grouping, Options, Sort, Source));
            $('#thRaw').removeClass('sorting_asc').addClass('sorting');
            $('#thPax').removeClass('sorting').addClass('sorting_asc');
            if (Options.indexOf('PaxRuns') > -1) {
                for (i = 1; i <= runs; i++) {
                    $('#thRun' + i).text('Pax' + i);
                }
            }
        }
    });

    var table = $('#timing').DataTable(
    {
        "ajax": {
            "url": requrl,
            "data": function ( d ) {
                return $.extend( {}, d, fromQueryString() );
            },
            "dataSrc": function ( json )
            {
                if ('summary' in json) {
                    summary = json.summary;
                    if (summary.runs !== runs) {
                        window.location.reload(true);
                        return [];
                    }
                }

                if ('data' in json) {
                    if (json.data.length === 0
                        && (requrl.indexOf('/class/') > -1 || requrl.indexOf('/group/') > -1)
                    ) {
                        $('a.navbar-brand').click();
                    }

                    return json.data;
                } else {
                    return [];
                }
            }
        },
        preDrawCallback: function( settings )
        {
            if (!Search) {
                $('#timing_filter').hide();
            }

            if (summary) {
                $('#divEvent').html(summary.event + '<br>' + summary.disclaimer);
                $('#divGenerated').text('Generated: ' + summary.generated);
            }
        },
        drawCallback: function( settings )
        {
            if (Grouping === 'Class') {
                table.columns([ r['R'], runs + o['Raw'], runs + o['Index'], runs + o['Pax'] ]).visible(false);
                table.column(runs + o['Total']).visible(true);
            } else {
                table.column(runs + o['Total']).visible(false);
                var cols = [r['R'], runs + o['Raw']];
                if (Columns.indexOf('Index') > -1) {
                    cols.push(runs + o['Index']);
                }

                if (Columns.indexOf('Pax') > -1) {
                    cols.push(runs + o['Pax']);
                }

                table.columns(cols).visible(true);
                rawidx = $('#thRaw').index() + 1;
                if (rawidx > 0) {
                    $('#thRaw').removeClass('sorting_asc_disabled');
                    if ($('#thRaw').hasClass('sorting_asc')) {
                        $('#thRaw').removeClass('sorting');
                        $('#timing tr td:nth-child(' + rawidx + ')').each(function ()
                        {
                              $(this).addClass('bestt')
                        })
                        for (i = 1; i <= runs; i++) {
                            $('#thRun' + i).text('Run' + i);
                        }
                    } else {
                        $('#thRaw').addClass('sorting');
                        $('#timing tr td:nth-child(' + rawidx + ')').each(function ()
                        {
                              $(this).removeClass('bestt')
                        })
                    }
                }

                paxidx = $('#thPax').index() + 1;
                if (paxidx > 0) {
                    $('#thPax').removeClass('sorting_asc_disabled');
                    if ($('#thPax').hasClass('sorting_asc')) {
                        $('#thPax').removeClass('sorting');
                        $('#timing tr td:nth-child(' + paxidx + ')').each(function ()
                        {
                              $(this).addClass('bestt')
                        })
                        if (Options.indexOf('PaxRuns') > -1) {
                            for (i = 1; i <= runs; i++) {
                                $('#thRun' + i).text('Pax' + i);
                            }
                        }
                    } else {
                        $('#thPax').addClass('sorting');
                        $('#timing tr td:nth-child(' + paxidx + ')').each(function ()
                        {
                              $(this).removeClass('bestt')
                        })
                    }
                }//end if
            }//end if
        },
        columnDefs: [
            { className: "bestt", "targets": [ runs + o['Total'] ] },
            { orderSequence: [ "asc" ], "targets": [ runs + o['Raw'] ] },
            { orderSequence: [ "asc" ], "targets": [ runs + o['Pax'] ] },
            { orderable: false, targets: notorderable },
            { searchable: false, targets: notsearchable },
            { orderData: [ r['G'], runs + o['Raw'] ], targets: runs + o['Raw'] },
            { orderData: [ r['G'], runs + o['Pax'] ], targets: runs + o['Pax'] },
            { visible: false, targets: notvisible }
        ],
        rowGroup: {
            dataSrc: r['G'],
            enable: rowGroup,
            startRender: function ( rows, group )
            {
                data = rows.data();
                if (((Grouping === 'Class' && requrl.indexOf('/class/') < 0)
                    || (Grouping !== 'Class' && requrl.indexOf('/group/') < 0))
                    && (Grouping !== 'Overall' || $.inArray('Ladies', Options) > -1) && data
                ) {
                    if (Grouping === 'Class') {
                        a = '/class/' + encodeURIComponent(group.substr(0, group.indexOf(' - ')).toLowerCase());
                    } else {
                        a = '/group/' + encodeURIComponent(group);
                    }

                    return '<a class="rowgrp" href="' + a + '">' + group + '</a>';
                } else {
                    return group;
                }
            },
        },
        dom: "<'row'<'col-sm-12'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>",
        fixedHeader: {
            header: false,
        },
        order: order,
        paging: false,
        lengthMenu: [ 50, 100, 200 ],
        processing: true,
        scrollX: true,
        scrollY: $(window).height() - heightAdj + "px",
        scrollCollapse: true,
        serverSide: true,
    } );

    $(window).bind('resize', function ()
    {
        var NewHeight = $(window).height() - heightAdj;
        $('#timing').closest('div.dataTables_scrollBody').css('max-height', NewHeight + "px");
        table.columns.adjust();
    });

    $('#refreshBtn').on( 'click', function ()
    {
        var scrollPos = $('#timing').closest("div.dataTables_scrollBody").scrollTop();
        if (Grouping === 'Class') {
            table.order.neutral();
        }

        table.ajax.reload(function()
        {
            $('#timing').closest("div.dataTables_scrollBody").scrollTop(scrollPos);
        }, false);
    });

    $("#timing tbody").on("click", "a.rowgrp", function(e)
    {
        var href = $(this).attr('href');
        if (Grouping === 'Class' && href.substring(0, 7) === "/class/") {
            Cookies.set(Grouping + '-group', href.substring(7));
        } else if (['Category', 'Overall'].indexOf(Grouping) > -1 && href.substring(0, 7) === "/group/") {
            Cookies.set(Grouping + '-group', href.substring(7));
        }

        window.location.assign(href + '?' + toQueryString(Columns, Grouping, Options, Sort, Source));
        return false;
    });

    $("#timing tbody").on("click", "a.driver", function(e)
    {
        var td = $(this).parent();
        var data = table.row( td ).data();
        var index = table.cell( td ).index();
        if (data && index && index.column === r['Name'] && data[r['Name']]) {
            a1 = $($.parseHTML(data[r['Name']]));
            a2 = $($.parseHTML(data[r['Cls']]));
            $('#divModalHeader').html(
                '<b>' + summary.event + '<br>' + a1.text() + ' - ' + data[r['Nbr']] + a2.text() + '</b>'
            );
            $('#divModalFooter').text('');
            $('#myModal').find(".modal-body").text('');
            $('#myModal').find(".modal-body").load(a1.attr('href'));
            $('#myModal').modal('show');
        }
    });

    $('a.navbar-brand').on( 'click', function (e)
    {
        var href = $(this).attr('href');
        if (href === "/") {
            Cookies.remove(Grouping + '-group');
        }

        window.location.assign(href + '?' + toQueryString(Columns, Grouping, Options, Sort, Source));
        return false;
    });

    $('a.Grouping').on( 'click', function (e)
    {
        if ($(this).text().trim() !== Grouping) {
            Grouping = $(this).text().trim();
            Cookies.set('grouping', Grouping, { expires: 2000 });

            $('a.Grouping:not(:contains("' + Grouping + '"))').each( function ()
            {
                $(this).find('input').prop("checked", false);
            });

            $('a.Grouping:contains("' + Grouping + '")').each( function ()
            {
                $(this).find('input').prop("checked", true);
            });

            if (typeof Cookies.get(Grouping + '-cols') === "undefined") {
                Columns = defaultColumns(COLUMNS, Grouping);
                Cookies.set(Grouping + '-cols', Columns, { expires: 2000 });
            } else {
                Columns = sortValues(Cookies.getJSON(Grouping + '-cols'), COLUMNS);
            }

            if (typeof Cookies.get(Grouping + '-options') === "undefined") {
                Options = defaultOptions(OPTIONS, Grouping);
                Cookies.set(Grouping + '-options', Options, { expires: 2000 });
            } else {
                Options = sortValues(Cookies.getJSON(Grouping + '-options'), OPTIONS);
            }

            if (typeof Cookies.get(Grouping + '-sort') === "undefined") {
                Sort = 'Raw';
                if (Grouping !== 'Class') {
                    Cookies.set(Grouping + '-sort', Sort, { expires: 2000 });
                }
            } else {
                Sort = Cookies.get(Grouping + '-sort');
            }

            var href = "/";
            if (typeof Cookies.get(Grouping + '-group') !== "undefined") {
                if (Grouping === 'Class') {
                    href = "/class/" + Cookies.get(Grouping + '-group');
                } else {
                    href = "/group/" + Cookies.get(Grouping + '-group');
                }
            }

            window.location.assign(href + '?' + toQueryString(Columns, Grouping, Options, Sort, Source));
            return;
        }//end if

        $(this).closest('li.dropdown').removeClass('open');
    });

    $('a.Source').on( 'click', function (e)
    {
        if ($(this).text().trim() !== Source) {
            Source = $(this).text().trim();
            pushState(requrl + '?' + toQueryString(Columns, Grouping, Options, Sort, Source));
            Cookies.set('source', Source, { expires: 2000 });

            $('a.Source:not(:contains("' + Source.replace(/\"/g, '\\\"') + '"))').each( function ()
            {
                $(this).find('input').prop("checked", false);
            });

            $('a.Source:contains("' + Source.replace(/\"/g, '\\\"') + '")').each( function ()
            {
                $(this).find('input').prop("checked", true);
            });

            if (Grouping === 'Class') {
                table.order.neutral().draw();
            } else {
                table.draw();
            }
        }

        $(this).closest('li.dropdown').removeClass('open');
    });

    $( 'a.Columns' ).on( 'click', function( event )
    {
        var $target = $( event.currentTarget );
        var val = $target.attr( 'data-value' ).trim();
        var $inp = $target.find( 'input' );
        var idx;

        if (( idx = Columns.indexOf( val ) ) > -1) {
            Columns.splice( idx, 1 );
            visibility(val, false);
            setTimeout( function() { $inp.prop( 'checked', false ) }, 0);
        } else {
            Columns.push( val );
            visibility(val, true);
            setTimeout( function() { $inp.prop( 'checked', true ) }, 0);
        }

        $( event.target ).blur();
        $target.blur();

        Columns = sortValues(Columns, COLUMNS);
        Cookies.set(Grouping + '-cols', Columns, { expires: 2000 });
        return false;
    });

    $( 'a.Options' ).on( 'click', function( event )
    {
        var $target = $( event.currentTarget ),
            val = $target.attr( 'data-value' ).trim(),
            $inp = $target.find( 'input' ),
            idx;

        if (( idx = Options.indexOf( val ) ) > -1) {
            Options.splice( idx, 1 );
            visibility(val, false);
            setTimeout( function() { $inp.prop( 'checked', false ) }, 0);
        } else {
            Options.push( val );
            visibility(val, true);
            setTimeout( function() { $inp.prop( 'checked', true ) }, 0);
        }

        $( event.target ).blur();
        $target.blur();

        Options = sortValues(Options, OPTIONS);
        Cookies.set(Grouping + '-options', Options, { expires: 2000 });
        return false;
    });

    $('#ddColumns').on('hide.bs.dropdown', function ()
    {
        pushState(requrl + '?' + toQueryString(Columns, Grouping, Options, Sort, Source));
        if (adjust) {
            table.columns.adjust().draw();
        }

        adjust = false;
    })

    $('#ddOptions').on('hide.bs.dropdown', function ()
    {
        pushState(requrl + '?' + toQueryString(Columns, Grouping, Options, Sort, Source));
        if (redraw) {
            table.draw();
        }

        redraw = false;
    })

    function columnsToJson(arr)
    {
        var arrcopy = arr.slice(0);
        if ((idx = arrcopy.indexOf('Ladies')) > -1) {
            arrcopy.splice(idx, 1);
        }

        if ((idx = arrcopy.indexOf('PaxRuns')) > -1) {
            arrcopy.splice(idx, 1);
        }

        if ((idx = arrcopy.indexOf('GrpRow')) > -1) {
            arrcopy.splice(idx, 1);
        }

        return JSON.stringify(arrcopy);
    }

    function initColumns()
    {
        if (Columns.indexOf('Car') > -1) {
            notvisible.splice(notvisible.indexOf(r['Car']), 1);
        }

        if (Columns.indexOf('Color') > -1) {
            notvisible.splice(notvisible.indexOf(r['Color']), 1);
        }

        if (Columns.indexOf('Runs') > -1) {
            for (c = 8; c < (runs + o['Total']); c++) {
                notvisible.splice(notvisible.indexOf(c), 1);
            }
        }

        if (Grouping !== 'Class' && Columns.indexOf('Index') > -1) {
            notvisible.splice(notvisible.indexOf(runs + o['Index']), 1);
        }

        if (Grouping !== 'Class' && Columns.indexOf('Pax') > -1) {
            notvisible.splice(notvisible.indexOf(runs + o['Pax']), 1);
        }

        if (Columns.indexOf('Diff') > -1) {
            notvisible.splice(notvisible.indexOf(runs + o['Diff']), 1);
        }

        if (Columns.indexOf('-1st') > -1) {
            notvisible.splice(notvisible.indexOf(runs + o['-1st']), 1);
        }

        if (Grouping !== 'Class' && Columns.indexOf('PaxP') > -1) {
            notvisible.splice(notvisible.indexOf(runs + o['PaxP']), 1);
        }

        if (Grouping === 'Class') {
            $('#aIndex').addClass("hidden").removeClass("hidden-xs");
            $('#aIndex-xs').addClass("hidden").removeClass("visible-xs");
            $('#aPax').addClass("hidden").removeClass("hidden-xs");
            $('#aPax-xs').addClass("hidden").removeClass("visible-xs");
            $('#aPaxP').addClass("hidden").removeClass("hidden-xs");
            $('#aPaxP-xs').addClass("hidden").removeClass("visible-xs");
        } else {
            $('#aIndex').addClass("hidden-xs").removeClass("hidden");
            $('#aIndex-xs').addClass("visible-xs").removeClass("hidden");
            $('#aPax').addClass("hidden-xs").removeClass("hidden");
            $('#aPax-xs').addClass("visible-xs").removeClass("hidden");
            $('#aPaxP').addClass("hidden-xs").removeClass("hidden");
            $('#aPaxP-xs').addClass("visible-xs").removeClass("hidden");
        }

        var show = false;
        $('a.Columns').each( function ()
        {
            inp = $(this).find('input');
            if (inp.height() > 13) {
                inp.css('margin-top', (20 - 1 - inp.height()) / 2);
            }

            show = Boolean(Columns.indexOf($(this).attr('data-value').trim()) > -1);
            inp.prop('checked', show);
        });
    }

    function initOptions()
    {
        rowGroup = Boolean(Options.indexOf('GrpRow') > -1);
        Search = Boolean(Options.indexOf('Search') > -1);
        if (Search) {
            heightAdj += 27;
        }

        if (Grouping === 'Class') {
            $('#aLadies').addClass("hidden").removeClass("hidden-xs");
            $('#aLadies-xs').addClass("hidden").removeClass("visible-xs");
            $('#aPaxRuns').addClass("hidden").removeClass("hidden-xs");
            $('#aPaxRuns-xs').addClass("hidden").removeClass("visible-xs");
        } else {
            $('#aLadies').addClass("hidden-xs").removeClass("hidden");
            $('#aLadies-xs').addClass("visible-xs").removeClass("hidden");
            $('#aPaxRuns').addClass("hidden-xs").removeClass("hidden");
            $('#aPaxRuns-xs').addClass("visible-xs").removeClass("hidden");
        }

        if (Options.indexOf('Ladies') > -1) {
            $('#aLadies').find('input').prop("checked", true);
            $('#aLadies-xs').find('input').prop("checked", true);
        } else {
            $('#aLadies').find('input').prop("checked", false);
            $('#aLadies-xs').find('input').prop("checked", false);
        }

        var show = false;
        $('a.Options').each( function ()
        {
            inp = $(this).find('input');
            if (inp.height() > 13) {
                inp.css('margin-top', (20 - 1 - inp.height()) / 2);
            }

            show = Boolean(Options.indexOf($(this).attr('data-value').trim()) > -1);
            inp.prop('checked', show);
        });
    }

    function visibility(key, show)
    {
        if (key === 'GrpRow') {
            redraw = true;
            if (show) {
                table.rowGroup().enable();
            } else {
                table.rowGroup().disable();
            }
        }

        if (key === 'Ladies') {
            redraw = true;
        }

        if (key === 'PaxRuns') {
            redraw = true;
            if (show && Options.indexOf('PaxRuns') > -1) {
                for (i = 1; i <= runs; i++) {
                    $('#thRun' + i).text('Pax' + i);
                }
            } else if (!show) {
                for (i = 1; i <= runs; i++) {
                    $('#thRun' + i).text('Run' + i);
                }
            }
        }

        if (key === 'Search') {
            if (show) {
                Search = true;
                heightAdj += 27;
                $('#timing_filter').show();
            } else {
                Search = false;
                heightAdj -= 27;
                var inp = $('#timing_filter').find('input');
                if (inp.val().trim() !== '') {
                    redraw = true;
                    table.search( '' ).columns().search( '' );
                } else {
                    inp.val('');
                }

                $('#timing_filter').hide();
            }

            var NewHeight = $(window).height() - heightAdj;
            $('#timing').closest('div.dataTables_scrollBody').css('max-height', NewHeight + "px");
        }//end if

        if (key === 'Car' || key === 'Color') {
            if (show) {
                adjust = true;
            }

            table.column(r[key]).visible(show);
        }

        if (key === 'Runs') {
            if (show) {
                adjust = true;
            }

            var cols = [];
            for (c = 8; c < (runs + o['Total']); c++) {
                cols.push(c);
            }

            table.columns(cols).visible(show);
            if ($('#thRaw').hasClass('sorting_asc')) {
                for (i = 1; i <= runs; i++) {
                    $('#thRun' + i).text('Run' + i);
                }
            } else if ($('#thPax').hasClass('sorting_asc')) {
                if (Options.indexOf('PaxRuns') > -1) {
                    for (i = 1; i <= runs; i++) {
                        $('#thRun' + i).text('Pax' + i);
                    }
                }
            }
        }//end if

        if (Grouping !== 'Class' && (key === 'Index' || key === 'Pax' || key === 'PaxP')) {
            if (show) {
                adjust = true;
            }

            table.column(runs + o[key]).visible(show);
        }

        if (key === 'Diff' || key === '-1st') {
            if (show) {
                adjust = true;
            }

            table.column(runs + o[key]).visible(show);
        }
    }

    window.onpopstate = function (event) {
        location.reload(false);
    }
} );

$.fn.dataTable.Api.register( 'order.neutral()', function ()
{
    return this.iterator( 'table', function ( s )
    {
        s.aaSorting.length = 0;
        s.aiDisplay.sort( function (a,b)
        {
            return a - b;
        } );
        s.aiDisplayMaster.sort( function (a,b)
        {
            return a - b;
        } );
    } );
} );
