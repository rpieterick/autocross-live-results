function sortValues(vals, valid) {
    return vals.sort().filter(function(item, pos, ary) {
        return (!pos || item != ary[pos - 1]) && valid.indexOf(item) > -1;
    })
}

function defaultColumns(valid, grouping)
{
    var defcols = ["Runs", "Diff", "-1st"];

    if (grouping !== 'Class') {
        defcols.splice(defcols.indexOf('Runs'), 1);
        defcols.push('Car');
        defcols.push('Index');
        defcols.push('Pax');
        defcols.push('PaxP');
    }

    return sortValues(defcols, valid);
}

function defaultOptions(valid, grouping)
{
    var defopts = ["GrpRow"];
    if (grouping === 'Category') {
        defopts.push('Ladies');
    }

    return sortValues(defopts, valid);
}

function getColumns(qs, valid, grouping)
{
    var key = 'cols';
    var cookie = grouping + '-' + key;

    if (key in qs) {
        cols = qs[key].split(' ');
    } else if (typeof Cookies.get(cookie) !== "undefined") {
        cols = Cookies.getJSON(cookie);
    } else {
        cols = sortValues(defaultColumns(valid, grouping), valid);
        Cookies.set(cookie, cols, { expires: 2000 });
        return cols;
    }

    return sortValues(cols, valid);
}

function getGrouping(qs, defval, valid)
{
    return getValid(qs, defval, 'grouping', valid);
}

function getOptions(qs, valid, grouping)
{
    var key = 'options';
    var cookie = grouping + '-' + key;

    if (key in qs) {
        opts = qs[key].split(' ');
    } else if (typeof Cookies.get(cookie) !== "undefined") {
        opts = Cookies.getJSON(cookie);
    } else {
        opts = sortValues(defaultOptions(valid, grouping), valid);
        Cookies.set(cookie, opts, { expires: 2000 });
        return opts;
    }

    return sortValues(opts, valid);
}

function getSort(qs, defval, valid, grouping)
{
    return getValid(qs, defval, 'sort', valid, grouping);
}

function getSource(qs, defval, valid)
{
    return getValid(qs, defval, 'source', valid);
}

function getValid(qs, defval, key, valid, grouping)
{
    grouping = grouping || '';
    if (grouping.length > 0) {
        grouping += '-';
    }

    var val = defval;
    var newarr = valid.map(function(x){ return x.toLowerCase() });

    if (key in qs && newarr.indexOf(qs[key].toLowerCase()) > -1) {
        var pos = newarr.indexOf(qs[key].toLowerCase());
        val = valid[pos];
    } else if (typeof Cookies.get(grouping + key) !== "undefined"
        && newarr.indexOf(Cookies.get(grouping + key).toLowerCase() > -1)
    ) {
        var pos = newarr.indexOf(Cookies.get(grouping + key).toLowerCase());
        val = valid[pos];
    }

    return val;
}

function fromQueryString()
{
    var qsobj = {};
    if (document.location.search.length > 1) {
        $.each(document.location.search.substr(1).split('&'),function(c,q)
        {
            var i = q.split('=');
            var k = i[0].toString();
            var v = '';
            if (i.length > 1) {
                v = i[1].toString();
            }

            qsobj[k] = decodeURIComponent(v.replace(/\+/g, ' '));
        });
    }

    return qsobj;
}

function toQueryString(Columns, Grouping, Options, Sort, Source)
{
    qs = "grouping=" + Grouping;
    qs += "&source=" + encodeURIComponent(Source).replace(/%20/g, '+');

    if (Array.isArray(Columns)) {
        qs += "&cols=" + Columns.join('+')
    }

    if (Array.isArray(Options)) {
        qs += "&options=" + Options.join('+')
    }

    if (Grouping !== 'Class') {
        qs += '&sort=' + Sort;
    }

    return qs;
}

function pushState(newloc)
{
    if (decodeURI(newloc) !== decodeURI(window.location.toString())) {
        window.history.pushState(null, '', newloc);
    }
}

function replaceState(newloc)
{
    if (decodeURI(newloc) !== decodeURI(window.location.toString())) {
        window.history.replaceState(null, '', newloc);
    }
}
