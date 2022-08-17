<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
  <title>OPUS-MT - Leaderboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1"> 
  <link rel="stylesheet" href="index.css" type="text/css">
</head>
<body>

<?php


$leaderboard_url = 'https://raw.githubusercontent.com/Helsinki-NLP/OPUS-MT-leaderboard/master/scores';

$chart     = isset($_GET['chart'])      ? test_input($_GET['chart'])      : 'standard';
$srclang   = isset($_GET['src'])        ? test_input($_GET['src'])        : 'deu';
$trglang   = isset($_GET['trg'])        ? test_input($_GET['trg'])        : 'eng';
$benchmark = isset($_GET['test'])       ? test_input($_GET['test'])       : 'all';
$metric    = isset($_GET['metric'])     ? test_input($_GET['metric'])     : 'bleu';
if (isset($_GET['langpair'])){
    list($srclang,$trglang) = explode('-',$_GET['langpair']);
}
$langpair  = implode('-',[$srclang,$trglang]);
$showlang  = isset($_GET['scoreslang']) ? test_input($_GET['scoreslang']) : $langpair;



$benchmark_url = urlencode($benchmark);
$langpair_url  = urlencode($langpair);
$showlang_url = urlencode($showlang);
$metric_url = urlencode($metric);
$chart_url = urlencode($chart);

//////////////////////////////////////////////////////////////////////////

echo '<div class="header">';
echo "<form action=\"compare.php\" method=\"get\">";
echo 'select benchmark: <select name="test" id="langpair" onchange="this.form.submit()">';
echo "<option value=\"all\">all</option>";



$testsets = file(implode('/',[$leaderboard_url,'benchmarks.txt']));
foreach ($testsets as $testset){
    list($test,$langs) = explode("\t",$testset);
    $test_url = urlencode($test);
    if ($test == $benchmark){
        echo "<option value=\"$test_url\" selected>$test</option>";
        $testlangs = rtrim($langs);
    }
    else {
        echo "<option value=\"$test_url\">$test</option>";
    }
}
echo '</select>';

echo "<form action=\"compare.php\" method=\"get\">";
if (($benchmark == "all")){
    $langpairs = array_map('rtrim', file(implode('/',[$leaderboard_url,'langpairs.txt'])));
    unset($_GET['test']);
}
else{
    $langpairs = explode(' ',$testlangs);
}

echo '  select language pair: <select name="langpair" id="langpair" onchange="this.form.submit()">';
foreach ($langpairs as $l){
    if ($l == $langpair){
        echo "<option value=\"$l\" selected>$l</option>";
        $selected = $l;
    }
    else{
        echo "<option value=\"$l\">$l</option>";
    }
}
echo '</select>';
echo '  [<a href="index.php">compare scores<a/>]';
echo '  [<a href="releases.php">show release history<a/>]';
echo '</form>';
echo '<hr/></div>';

//////////////////////////////////////////////////////////////////////////


echo('<h1>Compare OPUS-MT models</h1>');

if (isset($_GET['model1']) && isset($_GET['model2'])){
    $model1_url = urlencode($_GET['model1']);
    $model2_url = urlencode($_GET['model2']);
    $url_param = "metric=$metric_url&model1=$model1_url&model2=$model2_url&langpair=$langpair_url";
    if ($chart == 'diff'){
        // echo("<div id=\"chart\"><img src=\"diff-barchart.php?$url_param&scoreslang=$showlang_url&test=$benchmark_url\" alt=\"barchart\" /></div>");
        echo("<div id=\"chart\"><img src=\"diff-barchart.php?$url_param&scoreslang=$showlang_url&test=$benchmark_url\" alt=\"barchart\" /><br/><ul><li>Chart Type: ");
        echo("[<a rel=\"nofollow\" href=\"compare.php?model1=$model1_url&model2=$model2_url&langpair=$langpair_url&scoreslang=$showlang_url&test=$benchmark_url&metric=$metric_url\">standard</a>]");
            echo('[diff]</li></ul></div>');
    }
    else{
        // echo("<div id=\"chart\"><img src=\"compare-barchart.php?$url_param&scoreslang=$showlang_url&test=$benchmark_url\" alt=\"barchart\" /></div>");
        echo("<div id=\"chart\"><img src=\"compare-barchart.php?$url_param&scoreslang=$showlang_url&test=$benchmark_url\" alt=\"barchart\" /><br/><ul><li>Chart Type: ");
        echo('[standard]');
        echo("[<a rel=\"nofollow\" href=\"compare.php?model1=$model1_url&model2=$model2_url&langpair=$langpair_url&scoreslang=$showlang_url&test=$benchmark_url&metric=$metric_url&chart=diff\">diff</a>]</li></ul></div>");
    }
    $url_param .= "&chart=$chart_url";
    $langpairs = print_score_table($_GET['model1'],$_GET['model2'],$showlang,$benchmark);

    $modelhome = 'https://object.pouta.csc.fi';
    $file1  = implode('/',[$modelhome,$_GET['model1']]).'.scores.txt';
    $file2  = implode('/',[$modelhome,$_GET['model2']]).'.scores.txt';
    $lines1 = file($file1);
    $lines2 = file($file2);

    // echo("(1) $file1</br>");
    // echo("(2) $file2</br>");
}





$models = file(implode('/',[$leaderboard_url,$langpair,'model-list.txt']));

if (isset($_GET['model1'])){
    echo('<div id="scores"><h2>Selected models</h2>');
    echo('<ul>');
    
    $parts = explode('/',$_GET['model1']);
    $m_pkg = array_shift($parts);
    $m_lang = array_shift($parts);
    $m_model = array_shift($parts);
    $m_url = urlencode($m_lang.'/'.$m_model);
    $p_url = urlencode($m_pkg);
    $m_link = "<a rel=\"nofollow\" href=\"index.php?model=$m_url&pkg=$p_url&langpair=$langpair_url&scoreslang=$showlang_url&test=$benchmark_url\">";

    echo('<li><b>Model 1 (blue):</b> '.$m_link.$_GET['model1'].'</a></li>');

    if (isset($_GET['model2'])){

        $parts = explode('/',$_GET['model2']);
        $m_pkg = array_shift($parts);
        $m_lang = array_shift($parts);
        $m_model = array_shift($parts);
        $m_url = urlencode($m_lang.'/'.$m_model);
        $p_url = urlencode($m_pkg);
        $m_link = "<a rel=\"nofollow\" href=\"index.php?model=$m_url&pkg=$p_url&langpair=$langpair_url&scoreslang=$showlang_url&test=$benchmark_url\">";
    
        echo('<li><b>Model 2 (orange):</b> '.$m_link.$_GET['model2'].'</a></li>');
        echo('<li><b>Model Langpair(s):</b> ');
        ksort($langpairs);
        foreach ($langpairs as $lp => $count){
            if ($lp == $showlang){
                echo("[$showlang]");
            }
            else{
                $lp_url = urlencode($lp);
                echo("[<a rel=\"nofollow\" href=\"compare.php?$url_param&scoreslang=$lp&test=$benchmark_url\">$lp_url</a>]");
            }
        }
        if ($showlang != 'all'){
            echo("[<a rel=\"nofollow\" href=\"compare.php?$url_param&scoreslang=all&test=$benchmark_url\">all</a>]");
        }
        echo('</li>');

        /*
        echo('<li><b>Chart Type:</b> ');
        if ($chart == "diff"){
            echo("[<a href=\"compare.php?model1=$model1_url&model2=$model2_url&langpair=$langpair_url&scoreslang=$showlang_url&test=$benchmark_url&metric=$metric_url\">standard</a>]");
            echo('[diff]');
        }
        else{
            echo('[standard]');
            echo("[<a href=\"compare.php?model1=$model1_url&model2=$model2_url&langpair=$langpair_url&scoreslang=$showlang_url&test=$benchmark_url&metric=$metric_url&chart=diff\">diff</a>]");
        }
        echo('</li>');
        */
        
        echo('<li><b>Metric(s):</b> ');
        $metrics = array('bleu', 'chrf');
        foreach ($metrics as $m){
            if ($m == $metric){
                echo("[$m]");
            }
            else{
                echo("[<a rel=\"nofollow\" href=\"compare.php?model1=$model1_url&model2=$model2_url&langpair=$langpair_url&scoreslang=$showlang_url&test=$benchmark_url&metric=$m\">$m</a>]");
            }
        }
        echo('</li>');



        // $model1_url = urlencode($_GET['model1']);
        // $model2_url = urlencode($_GET['model2']);
        // $url_param = "model1=$model1_url&model2=$model2_url&langpair=$langpair_url";
        echo('</ul><h2>Start with a new model</h2>');
    }
    else{
        echo('</ul>');
        echo('<h2>Select the second model to compare with</h2>');
    }
}
else{
    echo('<h2>Select a model</h2>');
}

$sorted_models = array();
foreach ($models as $model){
    $parts = explode('-',rtrim($model));
    $day = array_pop($parts);
    $month = array_pop($parts);
    $year = array_pop($parts);
    $sorted_models[$model] = "$year$month$day";
}
arsort($sorted_models);


echo("<ul>");
// foreach ($models as $model){
foreach ($sorted_models as $model => $release){
    $parts = explode('/',rtrim($model));
    $modelzip = array_pop($parts);
    $modellang = array_pop($parts);
    $modelpkg = array_pop($parts);
    $modelbase = substr($modelzip, 0, -4);

    if (isset($_GET['model1']) && ! isset($_GET['model2'])){
        $modelA = urlencode($_GET['model1']);
        $modelB = urlencode(implode('/',[$modelpkg, $modellang, $modelbase]));
        if ($modelA == $modelB){
            echo("<li>$modellang/$modelbase</li>");
        }
        else{
            echo("<li><a rel=\"nofollow\" href=\"compare.php?model1=$modelA&model2=$modelB&langpair=$langpair_url&test=$benchmark_url&scoreslang=$showlang_url&metric=$metric_url\">$modellang/$modelbase</a></li>");
        }
    }
    else{
        $modelA = urlencode(implode('/',[$modelpkg, $modellang, $modelbase]));
        echo("<li><a rel=\"nofollow\" href=\"compare.php?model1=$modelA&langpair=$langpair_url&test=$benchmark_url&scoreslang=$showlang_url&metric=$metric_url\">$modellang/$modelbase</a></li>");
    }   
}
echo("</ul></div>");





function print_score_table($model1,$model2,$langpair='all',$benchmark='all'){
    global $metric, $langpair_url, $chart_url;

    $modelhome = 'https://object.pouta.csc.fi';
    $file1  = implode('/',[$modelhome,$_GET['model1']]).'.scores.txt';
    $file2  = implode('/',[$modelhome,$_GET['model2']]).'.scores.txt';

    $scores = array();
    $langpairs = array();
    $lines = file($file2);

    foreach($lines as $line) {
        $array = explode("\t", $line);
        $langpairs[$array[0]]++;
        if ($langpair == 'all' || $langpair == $array[0]){
            if ($benchmark == 'all' || $benchmark == $array[1]){
                $key = $array[0].'/'.$array[1];
                $scores[$key] = $line;
            }
        }
    }

    $lines = file($file1);
    $metric_url = urlencode($metric);
    $model1_url = urlencode($_GET['model1']);
    $model2_url = urlencode($_GET['model2']);
    $showlang_url = urlencode($langpair);
    $benchmark_url = urlencode($benchmark);
    $url_param = "metric=$metric_url&model1=$model1_url&model2=$model2_url&langpair=$langpair_url&chart=$chart_url";

    $avg_score1 = 0;
    $avg_score2 = 0;
    $count_scores1 = 0;
    $count_scores2 = 0;

    $common_langs = array();
    $testsets = array();
    
    echo('<div id="scores"><div class="query"><table>');
    echo("<tr><th>ID</th><th>Language</th><th>Benchmark ($metric)</th><th>Model 1</th><th>Model 2</th><th>Diff</th></tr>");
    $id = 0;
    foreach ($lines as $line){
        $parts = explode("\t",$line);
        $score1 = $metric == 'bleu' ? $parts[3] : $parts[2];
        $key = $parts[0].'/'.$parts[1];
        $testsets[$parts[1]]++;
        if (array_key_exists($parts[0],$langpairs)){
            $common_langs[$parts[0]]++;
        }


        if (array_key_exists($key,$scores)){
            $parts2 = explode("\t",$scores[$key]);
            $score2 = $metric == 'bleu' ? $parts2[3] : $parts2[2];
            $score2_exists = true;

            $diff = $score1 - $score2;
            if ($metric == 'bleu'){
                $diff_pretty = sprintf('%4.1f',$diff);
            }
            else{
                $diff_pretty = sprintf('%5.3f',$diff);
            }

            if ($langpair == 'all' || $langpair == $parts[0]){
                if ($benchmark == 'all' || $benchmark == $parts[1]){
                    $avg_score1 += $score1;
                    $count_scores1++;
                    $avg_score2 += $score2;
                    $count_scores2++;

                    $lang_url = urlencode($parts[0]);
                    $test_url = urlencode($parts[1]);
                    $langlink = "<a rel=\"nofollow\" href=\"compare.php?$url_param&scoreslang=$lang_url&test=$benchmark_url\">$parts[0]</a>";
                    $testlink = "<a rel=\"nofollow\" href=\"compare.php?$url_param&scoreslang=$showlang_url&test=$test_url\">$parts[1]</a>";
                    echo("<tr><td>$id</td><td>$langlink</td><td>$testlink</td><td>$score1</td><td>$score2</td><td>$diff_pretty</td></tr>");
                    $id++;
                }
            }
        }
    }
        
        /*
        if (array_key_exists($key,$scores)){
            $parts2 = explode("\t",$scores[$key]);
            $score2 = $metric == 'bleu' ? $parts2[3] : $parts2[2];
            $score2_exists = true;
        }
        else{
            $score2 = 0;
            $score2_exists = false;
        }

        $diff = $score1 - $score2;
        if ($metric == 'bleu'){
            $diff_pretty = sprintf('%4.1f',$diff);
        }
        else{
            $diff_pretty = sprintf('%5.3f',$diff);
        }

        if ($langpair == 'all' || $langpair == $parts[0]){
            if ($benchmark == 'all' || $benchmark == $parts[1]){
                $avg_score1 += $score1;
                $count_scores1++;
                if ($score2_exists){
                    $avg_score2 += $score2;
                    $count_scores2++;
                }

                $lang_url = urlencode($parts[0]);
                $test_url = urlencode($parts[1]);
                $langlink = "<a rel=\"nofollow\" href=\"compare.php?$url_param&scoreslang=$lang_url&test=$benchmark_url\">$parts[0]</a>";
                $testlink = "<a rel=\"nofollow\" href=\"compare.php?$url_param&scoreslang=$showlang_url&test=$test_url\">$parts[1]</a>";
                echo("<tr><td>$id</td><td>$langlink</td><td>$testlink</td><td>$score1</td><td>$score2</td><td>$diff_pretty</td></tr>");
                $id++;
            }
        }
    }
        */
    if ($count_scores1 > 1){
        $avg_score1 /= $count_scores1;
    }
    if ($count_scores2 > 1){
        $avg_score2 /= $count_scores2;
    }
    $diff = $avg_score1 - $avg_score2;
    
    if ($metric == 'bleu'){
        $avg1 = sprintf('%4.1f',$avg_score1);
        $avg2 = sprintf('%4.1f',$avg_score2);
        $diff = sprintf('%4.1f',$diff);
    }
    else{
        $avg1 = sprintf('%5.3f',$avg_score1);
        $avg2 = sprintf('%5.3f',$avg_score2);
        $diff = sprintf('%5.3f',$diff);
    }
    echo("<tr><th></th><th></th><th>average</th><th>$avg1</th><th>$avg2</th><th>$diff</th></tr>");

    if ($langpair != 'all' || $benchmark != 'all'){
        $langlink = '';
        $testlink = '';
        if ($langpair != 'all'){
            if (sizeof($common_langs) > 1){
                $langlink = "<a rel=\"nofollow\" href=\"compare.php?$url_param&scoreslang=all&test=$benchmark_url\">show all</a>";
            }
        }
        if ($benchmark != 'all'){
            if (sizeof($testsets) > 1){
                $testlink = "<a rel=\"nofollow\" href=\"compare.php?$url_param&scoreslang=$showlang_url&test=all\">show all</a>";
            }
        }
        if ($langlink != '' || $testlink != ''){
            echo("<tr><td></td><td>$langlink</td><td>$testlink</td><td></td><td></td><td></td></tr>");
        }
    }

    echo('</table></div></div>');
    return $common_langs;
}


function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}




?>
</body>
</html>
