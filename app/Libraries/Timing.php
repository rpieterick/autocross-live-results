<?php

namespace App\Libraries;

class Timing
{
    protected static function curlInit($srcurl, &$headers)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $srcurl
        ));

        $headers = ["Cache-Control: no-cache"];
        curl_setopt($curl, CURLOPT_HEADERFUNCTION, function ($ch, $hdr) use (&$headers) {
            $len = strlen($hdr);
            $hdr = explode(':', $hdr, 2);
            if (curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200 or count($hdr) < 2) { // ignore invalid headers
                return $len;
            }
        
            $name = trim($hdr[0]);
            if ($name == 'ETag') {
                $headers[] = 'If-None-Match: '.trim($hdr[1]);
            } elseif ($name == 'Last-Modified') {
                $headers[] = 'If-Modified-Since: '.trim($hdr[1]);
            }

            return $len;
        });

        return $curl;
    }

    protected static function driverBestPax($driver, $idx_id)
    {
        if ($idx_id != '') {
            $bestpax = $driver['total'];
        } elseif (is_numeric($driver['best_raw'])) {
            $bestpax = number_format($driver['best_raw'] * $driver['pax'], 4, '.', '');
            $bp = explode('.', $bestpax);
            if (isset($bp[1])) {
                $bp[1] = substr($bp[1], 0, 3);
            }
            $bestpax = implode('.', $bp);
            $bestpax = number_format($bestpax, 3, '.', '');
        } else {
            $bestpax = $driver['best_raw'];
        }

        return $bestpax;
    }

    protected static function driverRunPax($driver, $time)
    {
        $t = explode('+', $time);
        if (is_numeric($t[0])) {
            $runpax = number_format($t[0] * $driver['pax'], 4, '.', '');
            $rp = explode('.', $runpax);
            if (isset($rp[1])) {
                $rp[1] = substr($rp[1], 0, 3);
            }
            $runpax = implode('.', $rp);
            $runpax = number_format($runpax, 3, '.', '');
            if (isset($t[1])) {
                $runpax .= '+'.$t[1];
            }
        } else {
            $runpax = $time;
        }

        return $runpax;
    }

    protected static function getClass($cols, $group_id, &$idx_id)
    {
        if (strlen(trim($cols[2]->nodeValue)) > 0) {
            $class = $group_id;
        }

        if (strlen(trim($cols[1]->nodeValue)) > 0 or strlen(trim($cols[2]->nodeValue)) == 0) {
            $class = strtolower(str_replace('-', '', trim($cols[1]->nodeValue)));
            if ($group_id != '') {
                $idx_id = self::idxClassId($group_id);
            } else {
                $idx_id = self::idxClassId($class);
            }

            if ($idx_id != '' and
                strlen(trim($cols[1]->nodeValue)) > 0 and
                !starts_with($class, $idx_id)
            ) {
                $class = $idx_id.$class;
            }
        }

        return $class;
    }

    protected static function getClassPax($class, $idx_id, $idx_mod)
    {
        $pax = number_format(config('timing.pax.'.$class), 3, '.', '');
        if ($idx_id != '' and \Config::has('timing.idx_mult.'.$idx_id)) {
            $mult = config('timing.idx_mult.'.$idx_id);
            $pax = number_format(
                $pax * $mult,
                3 + strlen(substr(strrchr($mult, "."), 1)),
                '.',
                ''
            );
        } elseif ($idx_mod != '') {
            $mult = config('timing.idx_mult.'.$idx_mod);
            $pax = number_format($pax * $mult, 3, '.', '');
        }

        return $pax;
    }

    protected static function getGroupFromClass($cols, $class, &$idx_id)
    {
        $idx_id = self::idxClassId($class);
        if ($idx_id != '') {
            $group = $idx_id." - '".config('timing.idx_class.'.$idx_id)."'";
        } else {
            $tclass = $class;
            if (ends_with($class, 'l')) {
                $tclass = substr($class, 0, -1);
            }

            if (\Config::has('timing.class.'.$tclass)) {
                $group = $class." - '".config('timing.class.'.$tclass);
                if (ends_with($class, 'l')) {
                    $group .= " Ladies";
                }
                $group .= "'";
            } else {
                $group = $class;
            }
        }

        return $group;
    }

    protected static function getSummary($summary, $rows)
    {
        $heading = $rows[1]->getElementsByTagName('th');
        if ($heading[0]) {
            if (strpos($heading[0]->nodeValue, 'Generated:') !== false) {
                $g = explode(':', $heading[0]->nodeValue);
                if (isset($g[1])) {
                    $summary['generated'] = trim(explode('/', implode(':', array_slice($g, 1)))[0]);
                }
            } else {
                $summary['event'] = trim(
                    implode(' - ', array_slice(explode(' - ', $heading[0]->nodeValue), 0, -1))
                );
            }
        }

        $heading = $rows[2]->getElementsByTagName('th');
        if ($heading[0]) {
            if (strpos($heading[0]->nodeValue, 'Generated:') !== false
                and $summary['generated'] == ''
            ) {
                $g = explode(':', $heading[0]->nodeValue);
                if (isset($g[1])) {
                    $summary['generated'] = trim(explode('/', implode(':', array_slice($g, 1)))[0]);
                }
            } elseif ($summary['event'] == '') {
                $summary['event'] = trim(
                    implode(' - ', array_slice(explode(' - ', $heading[0]->nodeValue), 0, -1))
                );
            }
        }

        return $summary;
    }

    public static function idxClassId($str)
    {
        $tclass = $str;
        if (ends_with($str, 'l')) {
            $tclass = substr($str, 0, -1);
        }
        
        if (\Config::has('timing.class.'.$tclass)) {
            return '';
        } elseif (\Config::has('timing.idx_class.'.$str)) {
            return $str;
        }

        for ($i = 1; $i < strlen($str); $i++) {
            $tclass = substr($str, $i);
            $idx_mod = self::idxClassMod($tclass);
            if ($idx_mod != '') {
                $tclass = substr($tclass, 0, -strlen($idx_mod));
            }

            if (ends_with($tclass, 'l')) {
                $tclass = substr($tclass, 0, -1);
            }

            if (\Config::has('timing.idx_class.'.substr($str, 0, $i)) and \Config::has('timing.class.'.$tclass)) {
                return substr($str, 0, $i);
            }
        }

        return '';
    }
    
    public static function idxClassMod($str)
    {
        if (\Config::has('timing.class.'.$str)) {
            return '';
        }

        for ($i = 1; $i < strlen($str); $i++) {
            $tclass = substr($str, 0, -$i);
            if (ends_with($tclass, 'l') and \Config::has('timing.class.'.substr($tclass, 0, -1))) {
                $tclass = substr($tclass, 0, -1);
            }

            if (\Config::has('timing.idx_mult.'.substr($str, -$i)) and \Config::has('timing.class.'.$tclass)) {
                return substr($str, -$i);
            }
        }

        return '';
    }

    protected static function newDriver($group, $class, $cols, &$frcol)
    {
        $newdriver = array(
            'rank' => '',
            'position' => '',
            'group' => $group,
            'class' => $class,
            'number' => trim($cols[2]->nodeValue),
            'name' => '',
            'car' => '',
            'car_color' => '',
            'run1' => 'na',
            'run2' => 'na',
            'run3' => 'na',
            'run4' => 'na',
            'run5' => 'na',
            'run6' => 'na',
            'run7' => 'na',
            'run8' => 'na',
            'run9' => 'na',
            'run10' => 'na',
            'runpax1' => 'na',
            'runpax2' => 'na',
            'runpax3' => 'na',
            'runpax4' => 'na',
            'runpax5' => 'na',
            'runpax6' => 'na',
            'runpax7' => 'na',
            'runpax8' => 'na',
            'runpax9' => 'na',
            'runpax10' => 'na',
            'total' => 'na',
            'best_raw' => 'na',
            'pax' => '1.000',
            'best_pax' => 'na',
            'difference' => '',
            'from_first' => '',
            'paxp' => '',
        );

        if (isset($cols[0])) {
            $newdriver['position'] = trim($cols[0]->nodeValue);
        }

        if (isset($cols[3])) {
            $newdriver['name'] = trim($cols[3]->nodeValue);
        }

        if (isset($cols[4]) and !$cols[4]->hasAttribute('valign')) {
            $newdriver['car'] = trim($cols[4]->nodeValue);
            $frcol += 1;
        }

        if (isset($cols[5]) and !$cols[5]->hasAttribute('valign')) {
            $newdriver['car_color'] = trim($cols[5]->nodeValue);
            $frcol += 1;
        }

        return $newdriver;
    }

    public static function results($src)
    {
        \Log::debug('begin results() '.microtime());
        $srcurl = config('timing.source.'.$src);
        return \Cache::remember('results_'.$src, config('timing.ttl.results', 0.2), function () use ($src, $srcurl) {
            $drivers = [];
            $html = false;
            $summary = [
                'disclaimer' => config('timing.disclaimer', '*** Unofficial ***'),
                'event' => '',
                'generated' => '',
                'runs' => 0
            ];
            \Log::debug('src='.$src);
            \Log::debug('srcurl='.$srcurl);

            if (strpos($srcurl, "://") > 0) {
                $headers = \Cache::get('headers_'.$src, ["Cache-Control: no-cache"]);
                \Log::debug('old headers='.PHP_EOL.var_export($headers, true));
                $curl = self::curlInit($srcurl, $headers);
                \Log::debug('curl_exec '.microtime());
                $html = curl_exec($curl);
                $rc = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                curl_close($curl);

                \Log::debug('HTTP_CODE='.$rc);
                if ($rc == 200) {
                    \Cache::put('headers_'.$src, $headers, config('timing.ttl.headers', 5));
                    \Log::debug('new headers='.PHP_EOL.var_export($headers, true));
                } else {
                    return \Cache::get('old_results_'.$src, ['summary' => $summary, 'drivers' => $drivers]);
                }
            } elseif (is_readable($srcurl)) {
                $hash = \Cache::get('hash_'.$src, '');
                \Log::debug('old hash='.$hash);
                $newhash = hash_file('sha256', $srcurl);
                
                if ($newhash != $hash) {
                    \Log::debug('new hash='.$newhash);
                    $html = file_get_contents($srcurl);
                }
                
                if ($html) {
                    \Cache::put('hash_'.$src, $newhash, config('timing.ttl.hash', 5));
                } else {
                    return \Cache::get('old_results_'.$src, ['summary' => $summary, 'drivers' => $drivers]);
                }
            }

            if ($html) {
                $dom = new \domDocument;

                \Log::debug('loadHTML '.microtime());
                @$dom->loadHTML($html);
                $dom->preserveWhiteSpace = false;
                \Log::debug('getElementsByTagName("table") '.microtime());
                $tables = $dom->getElementsByTagName('table');

                if ($tables->length > config('timing.table_pos', 2)) {
                    $rows = $tables->item(0)->getElementsByTagName('tr');
                    if ($rows->length > 2) {
                        $summary = self::getSummary($summary, $rows);
                    }
                    
                    \Log::debug('getElementsByTagName("tr") '.microtime());
                    $rows = $tables->item($tables->length - config('timing.table_pos', 2))->getElementsByTagName('tr');
                    $group = '';
                    $group_id = '';
                    $drvrow = 0;
                    $maxruns = 0;
                    $top_pax = 'na';

                    \Log::debug('foreach driver row '.microtime());
                    foreach ($rows as $row) {
                        $heading = $row->getElementsByTagName('th');
                        if (isset($heading[0])) {
                            if ($heading[0]->hasAttribute('colspan')) {
                                $group = trim($heading[0]->nodeValue);
                                $group_id = strtolower(str_replace('-', '', trim(explode(' - ', $group)[0])));
                            } else {
                                $group = '';
                                $group_id = '';
                            }

                            $first_total = '';
                            $last_total = '';
                            $drvrow = 0;
                        }
                        
                        $cols = $row->getElementsByTagName('td');
                        $best_raw = 'na';
                        if (isset($cols[1]) and isset($cols[2])) {
                            $idx_id = '';
                            $class = self::getClass($cols, $group_id, $idx_id);
                            if ($group == '') {
                                $group = self::getGroupFromClass($cols, $class, $idx_id);
                            }

                            $frcol = 4;
                            $driver = self::newDriver($group, $class, $cols, $frcol);

                            if ($cols->length > ($frcol +1)) {
                                if ($driver['class'] == '' and $driver['number'] == '') {
                                    $drvrow += 1;
                                } else {
                                    $drvrow = 1;
                                }

                                $drvidx = count($drivers) - ($drvrow - 1);
                                $lastval = trim($cols[$cols->length - 1]->nodeValue);
                                if ($lastval == '' or starts_with($lastval, '-')
                                    or starts_with($lastval, '[-]') or starts_with($lastval, '+')
                                ) {
                                    if ($driver['class'] == '' and $driver['number'] == '') {
                                        $totalpos = $cols->length - 1;
                                        if ($drvrow > 1 and $drivers[$drvidx]['class'] != ''
                                            and $drivers[$drvidx]['number'] != ''
                                        ) {
                                            $driver['pax'] = $drivers[$drvidx]['pax'];
                                            $driver['total'] = $drivers[$drvidx]['total'];
                                            if (is_numeric($lastval)) {
                                                $drivers[$drvidx]['difference'] = $lastval;
                                            }
                                        }
                                    } else {
                                        $totalpos = $cols->length - 2;
                                        $driver['total'] = trim($cols[$cols->length - 2]->nodeValue);
                                        if (is_numeric($lastval)) {
                                            $driver['difference'] = $lastval;
                                        }
                                    }
                                } else {
                                    $totalpos = $cols->length - 1;
                                    $driver['total'] = $lastval;
                                }

                                if ($first_total and is_numeric($driver['total'])) {
                                    $driver['from_first'] = '+'.number_format(
                                        $driver['total'] - $first_total,
                                        3,
                                        '.',
                                        ''
                                    );
                                }

                                if (!$first_total and is_numeric($driver['total'])) {
                                    $first_total = $driver['total'];
                                }

                                if (strlen($driver['difference']) == 0
                                    and $last_total
                                    and is_numeric($driver['total'])
                                ) {
                                    $driver['difference'] = '+'.number_format(
                                        $driver['total'] - $last_total,
                                        3,
                                        '.',
                                        ''
                                    );
                                }

                                if (is_numeric($driver['total'])) {
                                    $last_total = $driver['total'];
                                }

                                if ($summary['runs'] == 0) {
                                    $summary['runs'] = $totalpos - $frcol;
                                }

                                if (($totalpos - $frcol) < $summary['runs']) {
                                    $runs = $totalpos - $frcol;
                                } else {
                                    $runs = $summary['runs'];
                                }
                                
                                $pclass = substr($class, strlen($idx_id));
                                if ($idx_id != '') {
                                    $idx_mod = self::idxClassMod($pclass);
                                    if ($idx_mod != '') {
                                        $pclass = substr($pclass, 0, -strlen($idx_mod));
                                    }
                                } else {
                                    $idx_mod = '';
                                }

                                if (ends_with($pclass, 'l')) {
                                    $pclass = substr($pclass, 0, -1);
                                }

                                if (\Config::has('timing.pax.'.$pclass)) {
                                    $driver['pax'] = self::getClassPax($pclass, $idx_id, $idx_mod);
                                }

                                $hasruns = false;
                                for ($i=$frcol; $i < ($runs + $frcol); $i++) {
                                    $time = trim($cols[$i]->nodeValue);
                                    if (strlen($time) > 0) {
                                        $hasruns = true;
                                        if (($i-($frcol-1)) > $maxruns) {
                                            $maxruns = ($i-($frcol-1));
                                        }
                                    }

                                    if ($cols[$i]->firstChild and $cols[$i]->firstChild->nodeName == "font") {
                                        $driver['run'.($i-($frcol-1))] = '<b>'.$time.'</b>';
                                        $t = explode('+', $time);
                                        if (is_numeric($driver['total']) and $idx_id != '') {
                                            $raw = $t[0];
                                            if (is_numeric($t[0]) and isset($t[1]) and is_numeric($t[1])) {
                                                $raw = number_format($raw + $t[1] * 2, 3, '.', '');
                                            }
                                            $best_raw = $raw;
                                        }
                                    } else {
                                        $driver['run'.($i-($frcol-1))] = $time;
                                    }

                                    $runpax = self::driverRunPax($driver, $time);

                                    if ($cols[$i]->firstChild and $cols[$i]->firstChild->nodeName == "font") {
                                        $driver['runpax'.($i-($frcol-1))] = '<b>'.$runpax.'</b>';
                                    } else {
                                        $driver['runpax'.($i-($frcol-1))] = $runpax;
                                    }
                                
                                    if ($summary['runs'] <= 5 and ($drvrow % 2) == 0) {
                                        $drivers[$drvidx][
                                            'run'.($summary['runs'] + $i-($frcol-1))
                                        ] = $driver['run'.($i-($frcol-1))];
                                        $drivers[$drvidx][
                                            'runpax'.($summary['runs'] + $i-($frcol-1))
                                        ] = $driver['runpax'.($i-($frcol-1))];

                                        if (strlen($time) > 0 and ($summary['runs'] + $i-($frcol-1)) > $maxruns) {
                                            $maxruns = ($summary['runs'] + $i-($frcol-1));
                                        }
                                    }
                                }

                                if (is_numeric($driver['total']) and $idx_id != '') {
                                    $driver['best_raw'] = $best_raw;
                                } else {
                                    $driver['best_raw'] = $driver['total'];
                                }

                                $driver['best_pax'] = self::driverBestPax($driver, $idx_id);                                
                                if (is_numeric($driver['best_pax'])
                                    and (!is_numeric($top_pax) or $driver['best_pax'] < $top_pax)
                                ) {
                                    $top_pax = $driver['best_pax'];
                                }
                                
                                if ($driver['class'] == '' and $driver['number'] == '') {
                                    if ($drvrow > 1
                                        and $drivers[$drvidx]['class'] != ''
                                        and $drivers[$drvidx]['number'] != ''
                                    ) {
                                        if ($drivers[$drvidx]['best_raw'] == 'na') {
                                            $drivers[$drvidx]['best_raw'] = $driver['best_raw'];
                                            $drivers[$drvidx]['best_pax'] = $driver['best_pax'];
                                        }
                                    }
                                }
                            }

                            if ($hasruns or $driver['class'] != '' or $driver['number'] != '') {
                                if ($driver['class'] == '' and $driver['number'] == '') {
                                    $driver['total'] = '';
                                    $driver['difference'] = '';
                                    $driver['from_first'] = '';
                                }

                                if ($summary['runs'] > 5 or ($drvrow % 2) > 0) {
                                    array_push($drivers, $driver);
                                }
                            }
                        } else {
                            if (!$heading[0]) {
                                $group = '';
                                $group_id = '';
                            }

                            $first_total = '';
                            $last_total = '';
                            $drvrow = 0;
                        }
                    }
                    \Log::debug('end foreach '.microtime());
                }
            }

            if ($drivers) {
                $summary['runs'] = $maxruns;
                foreach (array_keys($drivers) as $key) {
                    if (is_numeric($top_pax) and is_numeric($drivers[$key]['best_pax'])) {
                        $drivers[$key]['paxp'] = number_format(
                            ($top_pax / $drivers[$key]['best_pax']) * 100,
                            1,
                            '.',
                            ''
                        );
                    }
                }

                \Cache::put(
                    'old_results_'.$src,
                    ['summary' => $summary, 'drivers' => $drivers],
                    config('timing.ttl.old_results', 240)
                );

                return ['summary' => $summary, 'drivers' => $drivers];
            } else {
                return \Cache::get('old_results_'.$src, ['summary' => $summary, 'drivers' => $drivers]);
            }
        });
    }
}
