<?php


// ディレクトリは末尾スラッシュ無し

// 設定は env に記述して読み込みますので、下記の環境変数を env ファイルに定義してください
// DISCORD_CSV_PATH
//   Discord から dump した csv ファイルのフルパス
//
// CHECK_RESULT_FILE_PATH
//   結果を出力する csv ファイルを置くディレクトリ
//
// CHECK_RESULT_FILE_NAME
//   結果を出力する csv ファイルの名前
//
// AMZN_LINK_CHECK
//   amzn 短縮リンクをチェックするかどうか(true=チェックする、false=チェックしない)
//   チェックする場合はリダイレクト検証を行うため時間がかかります

return [
    'csvPath'          => env('DISCORD_CSV_PATH', ''), 
    'resultOutputPath' => env('CHECK_RESULT_FILE_PATH', ''),
    'resultFilename'   => env('CHECK_RESULT_FILE_NAME', ''),
    'amznLinkCheck'    => env('AMZN_LINK_CHECK', true),
    'sleepMicrotime'   => 300000,  // 1回の実行ごとの待機時間(マイクロ秒、1秒 = 1000000)
];
