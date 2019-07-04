<?php
//OBIEKT
class Slajd {
    public $nr;
    public $tytul;
    public $nazwa = [];
    public $ekran = ['nr' => 0, 'ekran_txt' => '', 'lektor_txt' => '', 'mp3' => ''];
}
//MODUŁY
$dirs = array_filter(glob('*'), 'is_dir');

for ($lp = 1;$lp <= count($dirs);$lp++) {
    $fName = "M" . $lp;
    ///MODUŁ 1
    $fKey = 0;
    $key = 0;
    $started = false;
    $aTab = [];
    foreach (glob($fName . "/*.xml") as $file) {
        $aTab[] = (int)substr($file, 13, -9);
    }
    foreach (glob($fName . "/*.xml") as $filename) {
        if ($filename != $fName . "/manifest_en.xml" && $filename != $fName . "/stringtable_en.xml") {
            $tempKey = (int)substr($filename, 13, -9);
            $tempNr = (int)substr($filename, 17, -7);
            $tempDir = $fName . "/textaudio/" . substr($filename, 3, -7) . "_a_en.xml";
            $tempFiles = glob($tempDir);
            if (!isset($slajd[$tempKey])) $slajd[$tempKey] = new Slajd();
            if ($started == false) {
                //MANIFEST
                $xml = simplexml_load_file($fName . "/manifest_en.xml") or die("Error: Cannot create object");
                $fKey = $tempKey;
                $key = $tempKey;
                foreach ($xml->itemtitle as $title) {
                    if (!isset($slajd[$key])) $slajd[$key] = new Slajd();
                    if (in_array($key, $aTab)) {
                        $slajd[$key]->nr = $key;
                        $slajd[$key]->tytul = (string)$title;
                    } else {
                        $slajd[$key]->nr = $key;
                        $slajd[$key]->tytul = "WARNING! NO FILE";
                    }
                    $key++;
                }
                $started = true;
            }
            $xml = simplexml_load_file($filename) or die("Error: Cannot create object");
            $slajd[$tempKey]->nazwa[$tempNr] = (string)substr($filename, 3, -4);
            $slajd[$tempKey]->ekran[$tempNr]['nr'] = $tempNr;
            foreach ($xml->tf as $e_txt) {
                $slajd[$tempKey]->ekran[$tempNr]['ekran_txt'][] = (string)$e_txt;
            }
            if (count($tempFiles) > 0) {
                $xml = simplexml_load_file($tempDir) or die("Error: Cannot create object");
                $mp3Nr =1;
                foreach ($xml->au as $l_txt) {
                    $slajd[$tempKey]->ekran[$tempNr]['lektor_txt'][] = (string)$l_txt;
                    $slajd[$tempKey]->ekran[$tempNr]['mp3'][] = (string)substr($filename, 3, -6) . $mp3Nr . ".mp3";
                    $mp3Nr++;
                }
                
            }
        }
        $key++;
    }
    echo "<br>";
    echo $fName . '      <span style="color:green;font-size:2.25em">&#x2611;</span><br>';
    $delimiter = ';';
    if (count($slajd) > 0) {
        // prepare the file
        $fp = fopen($fName . '.csv', 'w');
        // Save header
        $header = [];
        array_push($header, "NO", "Slide Title" , "File Name" , "Screen Nr" , "Text on Screen" , "Audio Text" , "Mp3 File Name");
        fputcsv($fp, $header, $delimiter);
        $tempA;
        $tepmB;
        $ctrA = 0;
        $ctrB = 0;
        $ctrC = 0;
        $ctrD = 0;
        for ($i = $fKey;$i < (count($slajd) + $fKey);$i++) {
            //Pierwsza linia
            if (isset($slajd[$i]) && isset($slajd[$i]->nazwa[1])) {
                if (isset($slajd[$i]->ekran[1]['ekran_txt'][0])) {
                    if (!isset($slajd[$i]->ekran[1]['lektor_txt'][0])) {
                        $element = [$slajd[$i]->nr, $slajd[$i]->tytul, $slajd[$i]->nazwa[1], $slajd[$i]->ekran[1]['nr'], $slajd[$i]->ekran[1]['ekran_txt'][0], "" , ""];
                    } else {
                        $element = [$slajd[$i]->nr, $slajd[$i]->tytul, $slajd[$i]->nazwa[1], $slajd[$i]->ekran[1]['nr'], $slajd[$i]->ekran[1]['ekran_txt'][0], $slajd[$i]->ekran[1]['lektor_txt'][0], $slajd[$i]->ekran[1]['mp3'][0]];
                    }
                } else {
                    if (!isset($slajd[$i]->ekran[1]['lektor_txt'][0])) {
                        echo "<br> i = ".$i;
                        $element = [$slajd[$i]->nr, $slajd[$i]->tytul, $slajd[$i]->nazwa[1], $slajd[$i]->ekran[1]['nr'], "", "", ""];
                    } else {
                        $element = [$slajd[$i]->nr, $slajd[$i]->tytul, $slajd[$i]->nazwa[1], $slajd[$i]->ekran[1]['nr'], "", $slajd[$i]->ekran[1]['lektor_txt'][0], $slajd[$i]->ekran[1]['mp3'][0]];
                    }
                }
                fputcsv($fp, (array)$element, $delimiter);
            }
            //koniec Pierwszej lini
            //Sprawdzanie długości
            if (isset($slajd[$i]->ekran[1]['ekran_txt'][0])) $ctrA = count($slajd[$i]->ekran[1]['ekran_txt']);
            else $ctrA = 0;
            if (isset($slajd[$i]->ekran[1]['lektor_txt'][0])) $ctrB = count($slajd[$i]->ekran[1]['lektor_txt']);
            else $ctrB = 0;
            //porównianie, z pierwszej warstwy
            if (isset($slajd[$i]->ekran[1]['ekran_txt'][0]) || isset($slajd[$i]->ekran[1]['lektor_txt'][0])) {
                if ($ctrA >= $ctrB) {
                    for ($q = 1;$q < (count($slajd[$i]->ekran[1]['ekran_txt']));$q++) {
                        if (isset($slajd[$i]->ekran[1]['lektor_txt'][$q])) $element = ["", "", "", "", $slajd[$i]->ekran[1]['ekran_txt'][$q], $slajd[$i]->ekran[1]['lektor_txt'][$q], $slajd[$i]->ekran[1]['mp3'][$q]];
                        else $element = ["", "", "", "", $slajd[$i]->ekran[1]['ekran_txt'][$q], "", ""];
                        fputcsv($fp, (array)$element, $delimiter);
                    }
                } else {
                    for ($w = 1;$w < (count($slajd[$i]->ekran[1]['lektor_txt']));$w++) {
                        if (isset($slajd[$i]->ekran[1]['ekran_txt'][$w])) $element = ["", "", "", "", $slajd[$i]->ekran[1]['ekran_txt'][$w], $slajd[$i]->ekran[1]['lektor_txt'][$w], $slajd[$i]->ekran[1]['mp3'][$w]];
                        else $element = ["", "", "", "", "", $slajd[$i]->ekran[1]['lektor_txt'][$w], $slajd[$i]->ekran[1]['mp3'][$w]];
                        fputcsv($fp, (array)$element, $delimiter);
                    }
                }
            }
            //Następne ekrany
            if (isset($slajd[$i]->nazwa[2])) {
                for ($r = 2;$r <= (count($slajd[$i]->nazwa));$r++) {
                    if ($slajd[$i]->nazwa[$r]) {
                        if (isset($slajd[$i]->ekran[$r]['ekran_txt'][0])) {
                            if (!isset($slajd[$i]->ekran[$r]['lektor_txt'][0])) {
                                $element = [$slajd[$i]->nr, $slajd[$i]->tytul, $slajd[$i]->nazwa[$r], $slajd[$i]->ekran[$r]['nr'], $slajd[$i]->ekran[$r]['ekran_txt'][0], ""];
                            } else {
                                $element = [$slajd[$i]->nr, $slajd[$i]->tytul, $slajd[$i]->nazwa[$r], $slajd[$i]->ekran[$r]['nr'], $slajd[$i]->ekran[$r]['ekran_txt'][0], $slajd[$i]->ekran[$r]['lektor_txt'][0], $slajd[$i]->ekran[$r]['mp3'][0]];
                            }
                        } else {
                            if (!isset($slajd[$i]->ekran[$r]['lektor_txt'][0])) {
                                $element = [$slajd[$i]->nr, $slajd[$i]->tytul, $slajd[$i]->nazwa[$r], $slajd[$i]->ekran[$r]['nr'], "", ""];
                            } else {
                                $element = [$slajd[$i]->nr, $slajd[$i]->tytul, $slajd[$i]->nazwa[$r], $slajd[$i]->ekran[$r]['nr'], "", $slajd[$i]->ekran[$r]['lektor_txt'][0], $slajd[$i]->ekran[$r]['mp3'][0]];
                            }
                        }
                    }
                    fputcsv($fp, (array)$element, $delimiter);
                    //koniec Pierwszej lini
                    //Sprawdzanie długości
                    if (isset($slajd[$i]->ekran[$r]['ekran_txt'][0])) $ctrC = count($slajd[$i]->ekran[$r]['ekran_txt']);
                    else $ctrA = 0;
                    if (isset($slajd[$i]->ekran[$r]['lektor_txt'][0])) $ctrD = count($slajd[$i]->ekran[$r]['lektor_txt']);
                    else $ctrB = 0;
                    //porównianie, z pierwszej warstwy
                    if (isset($slajd[$i]->ekran[$r]['ekran_txt'][0]) || isset($slajd[$i]->ekran[$r]['lektor_txt'][0])) {
                        if ($ctrC >= $ctrD) {
                            for ($q = 1;$q < (count($slajd[$i]->ekran[$r]['ekran_txt']));$q++) {
                                if (isset($slajd[$i]->ekran[$r]['lektor_txt'][$q])) $element = ["", "", "", "", $slajd[$i]->ekran[$r]['ekran_txt'][$q], $slajd[$i]->ekran[$r]['lektor_txt'][$q]];
                                else $element = ["", "", "", "", $slajd[$i]->ekran[$r]['ekran_txt'][$q], ""];
                                fputcsv($fp, (array)$element, $delimiter);
                            }
                        } else {
                            for ($w = 1;$w < (count($slajd[$i]->ekran[$r]['lektor_txt']));$w++) {
                                if (isset($slajd[$i]->ekran[$r]['ekran_txt'][$w])) $element = ["", "", "", "", $slajd[$i]->ekran[$r]['ekran_txt'][$w], $slajd[$i]->ekran[$r]['lektor_txt'][$w], $slajd[$i]->ekran[$r]['mp3'][$w]];
                                else $element = ["", "", "", "", "", $slajd[$i]->ekran[$r]['lektor_txt'][$w], $slajd[$i]->ekran[$r]['mp3'][$w]];
                                fputcsv($fp, (array)$element, $delimiter);
                            }
                        }
                    }
                }
            }
        }
    }
    
}
fclose($fp);
function lekDir($fName, $sName) {
    $tempDirA = $fName . "/textaudio/*" . $sName . "*.xml";
    return count(glob($tempDirA));
}
function txtDir($fName, $sName) {
    $tempDirA = $fName . "/*" . $sName . "*.xml";
    return count(glob($tempDirA));
}
?>