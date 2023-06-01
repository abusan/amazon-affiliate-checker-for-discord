<?php

// 設定を記述します
// ディレクトリは末尾スラッシュ無し

return [
    'csvPath'          => '',   // DiscordからダンプしたCSVファイルのフルパス
    'resultOutputPath' => '',  // 結果ファイルの出力先ディレクトリパス
    'resultFilename'   => 'check_%d.csv', // 出力ファイル名(%d = 日付)
    'sleepMicrotime'   => 300000,  // 1回の実行ごとの待機時間(マイクロ秒、1秒 = 1000000)
];
