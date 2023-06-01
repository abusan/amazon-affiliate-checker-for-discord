<?php

// 設定はenvに記述して読み込む
// ディレクトリは末尾スラッシュ無し
// DISCORD_CSV_PATH
//   Discord から dump した csv ファイルのフルパス
// CHECK_RESULT_FILE_PATH
//   結果を出力する csv ファイルを置くディレクトリ
// CHECK_RESULT_FILE_NAME
//   結果を出力する csv ファイルの名前

return [
    'csvPath'          => env('DISCORD_CSV_PATH', ''),   // DiscordからダンプしたCSVファイルのパス
    'resultOutputPath' => env('CHECK_RESULT_FILE_PATH', ''),  // 結果ファイルの出力パス
    'resultFilename'   => env('CHECK_RESULT_FILE_NAME', ''), // 出力ファイル名(%d = 日付)
    'sleepMicrotime'   => 300000,  // 1回の実行ごとの待機時間(マイクロ秒、1秒 = 1000000)
];
