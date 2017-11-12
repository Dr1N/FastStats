<?php

namespace src;

class Wanga
{
    const PREC_100 = '100';
    const PREC_1000 = '1000';
    
    public function prognosis($number, $prec)
    {
        $result['success'] = false;
        try {
            $db = new DBHelper();
            $games = $db->getGames($number, $prec);
            $result['count'] = count($games);
            $red = $black = $green = 0;
            foreach ($games as $game) {
               switch ($game['color'])
               {
                   case 1:
                       $green++;
                       break;
                   case 2:
                       $red++;
                       break;
                   case 3:
                       $black++;
                       break;
               }
            }
            if ($result['count'] != 0) {
                $result['red'] = round($red * 100.0/ $result['count'], 2);
                $result['black'] = round($black * 100.0/ $result['count'], 2);
                $result['green'] = round($green * 100.0/ $result['count'], 2);
            } else {
                $result['red'] = $result['black'] = $result['green'] = 0;
            }
            $result['success'] = true;
        } catch (\SQLiteException $sqlex) {
            $result['success'] = false;
            $result['message'] = $sqlex->getMessage();
        } catch (\Exception $ex) {
            $result['success'] = false;
            $result['message'] = $ex->getMessage();
        }

        return $result;
    }
}
