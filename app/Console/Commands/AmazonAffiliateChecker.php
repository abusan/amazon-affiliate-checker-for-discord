<?php

namespace App\Console\Commands;

use DateTimeImmutable;
use Illuminate\Console\Command;
use RuntimeException;
use SplFileObject;

class AmazonAffiliateChecker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:amazon-affiliate-checker';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    protected string $csvPath = '';
    protected string $resultOutputPath = '';
    protected string $resultFilename = '';
    protected bool $isAmznLinkCheck = false;
    protected int $sleeptime = 1000000;

    protected const CSV_COLUMN_AuthorID = 0;
    protected const CSV_COLUMN_Author = 1;
    protected const CSV_COLUMN_Date = 2;
    protected const CSV_COLUMN_Content = 3;
    protected const CSV_COLUMN_Attachments = 4;
    protected const CSV_COLUMN_Reactions = 5;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->sleeptime = config('checker.sleepMicrotime', 1000000);

        $this->isAmznLinkCheck = config('checker.amznLinkCheck'. false);

        $this->csvPath = config('checker.csvPath') ?? $this->csvPath;
        if (empty($this->csvPath)) {
            new RuntimeException("CSV filepath is empty. Check config.");
        }

        $resultOutputPath = config('checker.resultOutputPath');
        if (empty($resultOutputPath)) {
            new RuntimeException("ResultOutputPath is empty. check config.");
        }

        $resultFilename = config('checker.resultFilename');
        if (empty($resultFilename)) {
            new RuntimeException("ResultFilename is empty. check config.");
        }

        $this->resultOutputPath = $resultOutputPath;

        $now = new DateTimeImmutable();
        $this->resultFilename = str_replace('%d', $now->format('YmdHis'), $resultFilename);

        echo 'csv=' . $this->csvPath . PHP_EOL;

        $csvFile = new SplFileObject($this->csvPath, 'r');
        $csvFile->setFlags(SplFileObject::READ_CSV);

        if ($csvFile->isDir()) {
            new RuntimeException("Invalid cav file. check config.");
        }

        // 出力
        $now = date('YmdHis');
        $resultFile = str_replace('%d', $now, $this->resultOutputPath . '/' . $this->resultFilename);
        if (file_exists($resultFile)) {
            new RuntimeException("Result file already exists. check config.");
        }
        $resultFile = new SplFileObject($resultFile, 'a', $resultFile);
        $resultFile->fputcsv(
            [
                'seq',
                'execID',
                'AuthorID',
                'Author',
                'Date',
                'URL',
                'RedirectURL',
                'AffiliateID',
                'Content',
            ]
        );
        

        $counter = 1;
        $execID = 1;
        foreach ($csvFile as $line) {
            if (count($line) <= 1) continue;

            $authorID = $line[static::CSV_COLUMN_AuthorID];
            $author   = $line[static::CSV_COLUMN_Author];
            $date     = $line[static::CSV_COLUMN_Date];
            $content  = $line[static::CSV_COLUMN_Content];

            if(preg_match_all('(https?://[-_.!~*\'()a-zA-Z0-9;/?:@&=+$,%#]+)', $content, $result) !== false){
                foreach ($result[0] as $checkURL){

                    $affiliateID = '';
                    $redirectURL = '';

                    // Amazon?
                    if (strpos($checkURL, 'amazon') !== false) {
                        print $checkURL . PHP_EOL;

                        // アフィチェック
                        // ?から下を取得
                        $affiliateID = $this->checkAffiliate($checkURL);
                    }

                    // amzn?
                    if ($this->isAmznLinkCheck && strpos($checkURL, 'amzn') !== false) {
                        // curlで確認
                        $curl = curl_init();
                        curl_setopt($curl, CURLOPT_URL, $checkURL);
                        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
                        // curl_setopt($curl, CURLOPT_NOPROGRESS, false);
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                        curl_exec($curl);
                        $info = curl_getinfo($curl);
                        print $checkURL . ' -> ' . $info['redirect_url'] . PHP_EOL;

                        curl_close($curl);
                        
                        $redirectURL = $info['redirect_url'];
                        $affiliateID = $this->checkAffiliate($redirectURL);

                        // curl実行時のみsleep
                        usleep($this->sleeptime);
                    }

                    if ($affiliateID) {
                        // 日付を見やすい形に
                        // 03/24/2022 1:03 AM -> 2022-03-24 01:03
                        $postDate = DateTimeImmutable::createFromFormat('m/d/Y H:i A', $date);

                        $resultFile->fputcsv(
                            [
                                $counter,
                                $execID,
                                $authorID,
                                $author,
                                $postDate->format('Y-m-d H:i'),
                                $checkURL,
                                $redirectURL,
                                $affiliateID,
                                $content,
                            ]
                        );

                        $counter++;
                    }
                }

                $execID++;
            }
        }
    }

    /**
     * 指定されたURLがAmazonアフィリエイトのURLかどうかを判定し、アフィリエイトIDを返します
     *
     * @param string $checkURL
     * @return string 
     * @throws RuntimeException URLの形式が想定外の場合
     */
    protected function checkAffiliate(string $checkURL): string
    {
        $buf = explode('?', $checkURL);
        if (count($buf) === 1) return '';    // クエリ無しのため除外
        if (count($buf) > 2) {
            // URLが想定外(クエリパラメータが2つ以上ある)
            throw new RuntimeException("Invalid URL. checkURL = " . $checkURL);
        }
        $queryStr = $buf[1];
        $params = explode('&', $queryStr);
        foreach ($params as $paramPair) {
            $pair = explode('=', $paramPair);
            $key = $pair[0];
            if ($key === 'tag') {
                return $pair[1] ?? '';
            }
        }

        return '';
    }
}
