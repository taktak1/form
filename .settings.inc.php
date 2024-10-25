<?php
//新しいセッションを開始、あるいは既存のセッションを再開
session_start();
//タイムゾーンをセット
date_default_timezone_set('Asia/Tokyo');
//タイトル設定
const TITLE="タイトル";
const FTDIR=__DIR__."/aggregate/finishout/";//アンケート完了後の制限時間経過後のディレクトリ
const FIDIR=__DIR__."/aggregate/finish/";   //アンケート完了済みのディレクトリ
const UNDIR=__DIR__."/aggregate/unfinish/"; //アンケート途中のファイルのディレクトリ
const CNDIR=__DIR__."/contents.csv";        //質問内容
const STRTP="0";                            //最初のページ
const LASTP="9999";                         //最後ページ
const TIMEL="99";                            //制限時間（時）
const ERROL=__DIR__."/error.log";           //エラーログの保存先
//言語を設定
mb_language('ja');
//文字のエンコードを設定
mb_internal_encoding('utf-8');
//PHP実行するプログラム側で発生したエラーを非表示
ini_set('display_startup_errors', false);
//エラーを非表示に設定
ini_set('display_errors', false);
//エラーログの保存先パスの設定
ini_set('error_log',ERROL);
//エラーを出力するレベルを設定 E_ALL:全てのエラーを表示
error_reporting(E_ALL);
//-------------------ここから関数-------------------//
//時間差を返す関数
function ftimechk($xfname){
    $xfname=str_split((string)$xfname,2);
    $xftime=new DateTime($xfname[0]."-".$xfname[1]."-".$xfname[2]." ".$xfname[3].":".$xfname[4].":".$xfname[5]);
    $xntime=new DateTime();
    //現在時間を代入
    $xntime->format('y-m-d H:i:s');
    
    $xtimed=$xntime->diff($xftime);
    $xhours = $xtimed->h;
    $xhours = $xhours+ ($xtimed->days*24);
    return $xhours;
}

//回答CSVの更新
function fcsvup($x_ipcd,$xapost){
    if($xapost["next"]==LASTP){
        //完了ファイルを設定
        $xfname=FIDIR.$x_ipcd."_".date("ymdHis").".csv"; 
    }else{
        //新しい時間のファイルを設定
        $xfname=UNDIR.$x_ipcd."_".date("ymdHis").".csv";
    }
    $xnwcsv=fopen($xfname,"w");
    if($xtorku=fopen($_SESSION['xfname'],"r")){
        //既存のcsvファイルを書込み
        while ($xcdata = fgetcsv($xtorku)) {
            if ($xcdata[1]!=""){
                fputcsv($xnwcsv,$xcdata);
            }
        }
        fclose($xtorku);
        //旧時間のファイルを削除
        unlink($_SESSION['xfname']);
    }
    //入力された値をcsvに入力
    foreach($xapost as $xrenso => $xvalue){
        //入力情報を書込み
        if($xrenso!="ticket"&&$xrenso!="submit"&&$xrenso!="next"&&$xrenso!="xquesr"&&$xrenso!='radio'){
            if($xvalue==""){
                $xvalue="回答なし";
            }
            fputcsv($xnwcsv,[$xapost["xquesr"],$xvalue]);
        }
    } 
    if($xapost["next"]!=LASTP){
        fputcsv($xnwcsv,[$xapost["next"],""]);
    }
    fclose($xnwcsv);
    return $xfname;
}

//質問内容を配列化 引数:質問NO
function fcntes($xqsnum){
    if(!($xcntes=fopen(CNDIR,"r"))){
        ferrex(CNDIR."のオープンに失敗しました。");
        return false;
    }
    //1行づつ読み込む
    while ($xcdata = fgetcsv($xcntes)) {
        if($xqsnum!=$xcdata[0]){
            continue;
        }
        //タイプを取得
        switch($xcdata[1]) {
            //文章のみ
            case 0:
                $xcsvco=[$xcdata[0],$xcdata[1],$xcdata[2],$xcdata[3]];
                break;
            //テキストボックス
            case 1:
                $xcount=count($xcdata);
                $xcheck=8;
                $xcsvco=[$xcdata[0],$xcdata[1],$xcdata[2],$xcdata[3],$xcdata[4],$xcdata[5],$xcdata[6],$xcdata[7]];
                while ($xcheck<$xcount){
                    if($xcdata[$xcheck]==""){
                        break;
                    }
                    array_push($xcsvco,$xcdata[$xcheck],$xcdata[$xcheck+1],$xcdata[$xcheck+2],$xcdata[$xcheck+3]);
                    $xcheck+=4;
                }
                break;
            //セレクト、ラジオ、オプションボタン
            case 2:
            case 3:
            case 4:
            case 5:
            case 6:
            case 7:
                $xcount=count($xcdata);
                $xcheck=7;
                $xcsvco=[$xcdata[0],$xcdata[1],$xcdata[2],$xcdata[3],$xcdata[4],$xcdata[5],$xcdata[6]];
                while ($xcheck<$xcount){
                    if($xcdata[$xcheck]==""){
                        break;
                    }
                    array_push($xcsvco,$xcdata[$xcheck]);
                    $xcheck+=1;
                }
        }
        break;
    }
    if(!(isset($xcsvco))){
        $xcsvco=false;
    }
    fclose($xcntes);
    return $xcsvco;
}

//完成途中のファイルの検索 引数:IPアドレス(ファイル名)
function fcheck($x_ipcd,$xdirec){
    $xhadle = opendir($xdirec);
    //逐次ファイル名を得る
    while (false !== ($xfname = readdir($xhadle))) {
        //エントリがファイルの時だけ出力する
        if(is_file($xdirec . $xfname)){
            if(strstr($xfname,"_",true)==$x_ipcd){
                $xtimec=str_replace([".csv","_"],"",strstr($xfname,"_",false));
                if(ftimechk($xtimec)>=TIMEL){
                    if($xdirec===UNDIR){
                        //未完了の場合はリセット
                        unlink(UNDIR . $xfname);
                    }else{
                        //制限時間経過後のフォルダへ移動
                        rename(FIDIR.$xfname,FTDIR.$xfname);
                    }
                    break;
                }else{
                    if($xdirec===UNDIR){
                        return (UNDIR . $xfname);
                    }else{
                        //完了後一定時間経過してない場合true
                        return true;
                    }
                }
            }
        } 
    }
    if($xdirec===FIDIR){
        return false;
    }
    $xfname=UNDIR.$x_ipcd."_".date("ymdHis").".csv";
    touch($xfname);
    return $xfname;
}

//表示する質問番号を取得
function fcocnt($xfname){
    $xcntes=fopen($xfname,"r");
    $xnextq=STRTP;
    //最後の番号を取得する
    while ($xcdata = fgetcsv($xcntes)) {
        $xnextq=$xcdata[0];
    }
    fclose($xcntes);
    
    return $xnextq;
}

//ページの内容を作成
function fmakez($value){
    switch($value[1]) {
        case 0://文章のみ
            $xconts=fmidsi($value[2],$value[3]);
            break;
        case 1://テキストボックス・テキストエリア
            $xconts=fmidsi($value[2],$value[3]).ftxtmak($value);
            break;
        case 2://セレクトボックス
        case 3:
            $xconts=fmidsi($value[2],"").fselec($value);
            break;
        case 4://ラジオボタン
        case 5:
            $xconts=fmidsi($value[2],"").fradio($value);
            break;
        case 6://チェックボックス
        case 7:
            $xconts=fmidsi($value[2],"").fchkbt($value);
            break;
        default:
            ferrex("タイプNOが存在しません");
            $xconts="ただいま設定中です";
    }
    return $xconts;
}

//見出しとnextの値を設定
function fmidsi($xkeynm,$xkeyhs){
    return "<p class='qestion'>".$xkeynm."</p>\n".
    "<input type='hidden' id='next' name='next' value='".$xkeyhs."' >\n";
}
//テキストボックス,テキストエリアの要素作成
function ftxtmak($value){
    $xmaxh=count($value);
    $xcount=4;
    $xconts="";
    while($xcount<$xmaxh){
        if($value[$xcount+1]!=""){
            $xconts.="<label>".$value[$xcount+1]."</label>\n";
        }
        if($value[$xcount]!=0){
            $xconts.="<textarea name='ta".$xcount."' autocomplete='off' "; 
        }else{
            $xconts.="<input type='text' name='tx".$xcount."' autocomplete='off' "; 
        }
        //文字数上限が０以外の場合設定する
        if($value[$xcount+2]!=0){
            $xconts.="minlength='".$value[$xcount+2]."'";
        }
        //必須判定 0:任意 1:必須
        if($value[$xcount+3]==1){
            $xconts.=" required ";
        }
        $xconts.=">\n";

        if($value[$xcount]!=0){
            $xconts.="</textarea>\n"; 
        }
        //文字数上限がある場合設定する
        if($value[$xcount+2]!=0 && $value[$xcount+2]!=""){
            $xconts.="<p class='atten'>(".$value[$xcount+2]."文字以上入力)</p>\n";
        }
        $xcount+=4;
    }
    return $xconts;
}
//セレクトボックスの要素作成
function fselec($value){
    if($value[1]==3){
        $value=frands($value);
    }
    global $ahairt;
    $xmaxh=count($value);
    $xcount=3;
    $xconts="<input type='hidden' id='svalue' name='svalue'>\n";
    $xconts.="<select id='select' onchange='fselct(this.value)' required >\n";
    $xconts.="<option value='' disabled selected>選択してください</option>\n";
    while($xcount<$xmaxh){
    $xmaxh=count($value);
        $xconts.="<option value='sen".$xcount."' >".$value[$xcount+1]."</option>\n";
        $ahairt["sen".$xcount]=[$value[$xcount],$value[$xcount+1]];
        $xcount+=2;
    }
    $xconts.="</select>";
    return $xconts;
}
//ラジオボタンの要素作成
function fradio($value){
    global $ahairt;
    if($value[1]==5){
        $value=frands($value);
    }
    $xmaxh=count($value);
    $xcount=3;
    $xconts="<input type='hidden' id='svalue' name='svalue'>\n";
    $xconts.="<div class='rachk'>";
    while($xcount<$xmaxh){
    $xmaxh=count($value);
        $xconts.="<article>\n<input type='radio' id='radio' name='radio' onchange='fselct(this.value)' value='sen".$xcount."' required >\n<div>\n".$value[$xcount+1]."\n</div>\n</article>\n";
        $ahairt["sen".$xcount]=[$value[$xcount],$value[$xcount+1]];
        $xcount+=2;
    }
    $xconts.="</div>";
    return $xconts;
}

//チェックボックスの要素作成
function fchkbt($value){
    //カウント上限
    global $amaxch;
    global $aminch;
    global $achkss;
    if($value[3]==""||$value[3]==0){
        $amaxch=999;
    }else{
        $amaxch=(int)$value[3];
    }
    if($value[4]==0||$value[4]==""){
        $aminch=1;
    }else{
        $aminch=(int)$value[4];
    }
    $xmaxh=count($value);
    for($i=5;$i<=$xmaxh;$i=$i+2){
        if(!(isset($value[$i]))||$value[$i]==""){
            break;
        }
        $achkss[]=["chk".$i,$value[$i],$value[$i+1]];
        $value[$i]="chk".$i;
    }
    if($value[1]==7){
        $value=frando($value);
    }
    $xcount=5;
    $xconts="<div class='rachk'>";
    while($xcount<$xmaxh){
        $xconts.="<article>\n<input type='checkbox' id='".$value[$xcount]."' name='".$value[$xcount]."' value='".$value[$xcount+1]."'>\n<div>\n".$value[$xcount+1]."\n</div>\n</article>\n";
        $xcount+=2;
    }
    $xconts.="</div>";
    if($amaxch!=0||$aminch!=0){
        if($amaxch!=0 && $aminch!=0&&$amaxch!=999){
            $xconts.="<p class='atten'>(最小".$aminch."個 最大".$amaxch."個 )</p>\n";
        }else{
            if($aminch!=0){
                $xconts.="<p class='atten'>(最小".$aminch."個)</p>\n";
            }else{
                $xconts.="<p class='atten'>(最大".$amaxch."個)</p>\n";
            }
        }
        
    }

    return $xconts;
}

//フォルダチェック
function ffolchk(){
    $xhaire=[FTDIR,FIDIR,UNDIR];
    foreach($xhaire as $xdirrr){
        if(!(is_dir($xdirrr))){
            ferrex($xdirrr."が存在しません");
            return true;
        }
    }
    return false;
}

//エラーログ出力 引数:出力する文章
function ferrex($xkywrd){
    file_put_contents(ERROL,date("Y_m_d_H :").$xkywrd."\n",FILE_APPEND);
}

//ランダム化しデータを返す(ラジオ、セレクト用)
function frands($xvalue){
    $xhiret=[$xvalue[0],$xvalue[1],$xvalue[2]];
    array_splice($xvalue,0,3);
    $xhimax=count($xvalue)/2-1;

    while($xhimax>=0){
        $xrandk=rand(0,$xhimax)*2;
        array_push($xhiret,$xvalue[$xrandk],$xvalue[$xrandk+1]);
        array_splice($xvalue,$xrandk,2);
        --$xhimax;
    }
    return $xhiret;
}
//ランダム化しデータを返す(オプション用)
function frando($xvalue){
    $xhiret=[$xvalue[0],$xvalue[1],$xvalue[2],$xvalue[3],$xvalue[4]];
    array_splice($xvalue,0,5);
    $xhimax=count($xvalue)/2-1;

    while($xhimax>=0){
        $xrandk=rand(0,$xhimax)*2;
        array_push($xhiret,$xvalue[$xrandk],$xvalue[$xrandk+1]);
        array_splice($xvalue,$xrandk,2);
        --$xhimax;
    }
    return $xhiret;
}
