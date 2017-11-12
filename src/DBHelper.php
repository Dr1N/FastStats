<?php

namespace src;

use SQLite3;
use SQLite3Result;

class DBHelper
{
    private $tblName = 'tbl_history';
    private $dbPath;
    private $sqlite;

    public function __construct()
    {
        $this->dbPath = DB_NAME;
        $this->sqlite = new SQLite3($this->dbPath);
    }

    public function save ($history)
    {
        $cnt = 0;
        foreach ($history as $item) {
            $rand1000 = floor($item->rand * 1000) / 1000.0;
            $rand100 = floor($item->rand * 100) / 100.0;
            $unixTime = strtotime($item->time);

            /*print_r($rand1000); echo PHP_EOL;
            print_r($rand100); echo PHP_EOL;
            print_r($unixTime); echo PHP_EOL;
            print_r($item->rand); echo PHP_EOL;
            print_r($item->hash); echo PHP_EOL;
            print_r($item->sector); echo PHP_EOL;
            print_r($item->color); echo PHP_EOL;

            die();*/

            $stmt = $this->sqlite->prepare("INSERT INTO {$this->tblName} (game_id, rand, hash, sector, color, time, rand_1000, rand_100) VALUES (:game_id, :rand, :hash, :sector, :color, :time, :rand_1000, :rand_100)");
            $stmt->bindValue(':game_id', $item->id, SQLITE3_INTEGER);
            $stmt->bindValue(':rand', $item->rand);
            $stmt->bindValue(':hash', $item->hash, SQLITE3_TEXT);
            $stmt->bindValue(':sector', $item->sector, SQLITE3_INTEGER);
            $stmt->bindValue(':color', $item->color, SQLITE3_INTEGER);
            $stmt->bindValue(':time', $unixTime, SQLITE3_INTEGER);
            $stmt->bindValue(':rand_1000', $rand1000, SQLITE3_FLOAT);
            $stmt->bindValue(':rand_100', $rand100, SQLITE3_FLOAT);
            if(($result = @$stmt->execute()) !== false) {
                $cnt++;
                $result->finalize();
            }
            $stmt->close();
        }

        return $cnt;
    }

    public function getGames($number, $prec = null)
    {
        if ($prec == null) {
            $prec = 'rand';
        } else {
            $prec = $prec == Wanga::PREC_1000 ? 'rand_1000' : 'rand_100';
        }
        $prec = $this->sqlite->escapeString($prec);
        $number = $this->sqlite->escapeString($number);
        $table = $this->sqlite->escapeString($this->tblName);
        $sql = "SELECT * FROM $table WHERE game_id IN (SELECT game_id + 1 FROM $table WHERE $prec = $number)";
        $result = $this->sqlite->query($sql);

        return $this->getArrayFromResult($result);
    }

    public function getGreens()
    {
        $table = $this->sqlite->escapeString($this->tblName);
        $sql = "SELECT id, game_id, sector FROM $table WHERE sector = 0 ORDER BY game_id";
        $result = $this->sqlite->query($sql);

        return $this->getArrayFromResult($result);
    }

    private function getArrayFromResult(SQLite3Result $sqlResult)
    {
        if ($sqlResult === false) {
            $code = $this->sqlite->lastErrorCode();
            $msg = $this->sqlite->lastErrorMsg();

            throw new \SQLiteException("Get Games error!\n$code\n$msg");
        }

        $result = [];
        while ($item = $sqlResult->fetchArray(SQLITE3_ASSOC)) {
            $result[] =  $item;
        }
        $sqlResult->finalize();

        return $result;
    }
}
