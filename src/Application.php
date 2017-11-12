<?php

namespace src;

class Application
{
    public function parsing()
    {
        $downloader = new DownloadHelper();
        $saver = new DBHelper();
        while (true) {
            try {
                $history = $downloader->load();
                if (!empty($history)) {
                    echo 'LOADED: ' . count($history) . PHP_EOL;
                    $saved = $saver->save($history);
                    echo "ADDED:\t$saved" . PHP_EOL;
                }
                echo 'Wait... ' . SLEEP . ' seconds' . PHP_EOL;
            } catch (\SQLiteException $sqlex) {
                echo 'DB ERROR!' . PHP_EOL;
                echo $sqlex->getMessage() . PHP_EOL;
            } catch (\Exception $ex) {
                echo 'APP ERROR!' . PHP_EOL;
                echo $ex->getMessage() . PHP_EOL;
            }
            sleep(SLEEP);
        }
    }

    public function prognosis($number)
    {
        $wanga = new Wanga();

        $num100 = floor($number * 100.0) / 100;
        $result100 = $wanga->prognosis($num100, Wanga::PREC_100);

        $num1000 = floor($number * 1000.0) / 1000;
        $result1000 = $wanga->prognosis($num1000, Wanga::PREC_1000);

        $result['prec100'] = $result100;
        $result['prec1000'] = $result1000;

        return json_encode($result);
    }
    
    public function green()
    {
        $db = new DBHelper();
        $greens = $db->getGreens();

        $distance = [];
        $fullDistance = [];
        for ($i = 0; $i < count($greens) - 1; $i++) {
            $dist = $greens[$i + 1]['game_id'] - $greens[$i]['game_id'];
            $distance[] = [
                'game_id' => $greens[$i]['game_id'],
                'distance' => $dist,
            ];
            $fullDistance[] = [
                'game_id' => $greens[$i]['game_id'],
                'distance' => $dist,
            ];
        }

        echo "AVG:\t" . array_sum(array_column($distance, 'distance')) / count(array_column($distance, 'distance')) . PHP_EOL;
        echo "MIN:\t" . min(array_column($distance, 'distance')) . PHP_EOL;
        echo "MAX:\t" . max(array_column($distance, 'distance')) . PHP_EOL;

        if (file_exists('greens.txt')) {
            unlink('greens.txt');
        }

        $result = '';
        foreach ($distance as $item) {
            $result .= $item['game_id'] . "\t" . $item['distance'] . PHP_EOL;
        }
        file_put_contents('greens.txt', $result, FILE_APPEND);

        echo 'SEE: greens.txt' . PHP_EOL;
        echo 'DONE!' . PHP_EOL;
    }
}
