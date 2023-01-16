<?php
require_once (".settings.inc.php");

//完了・未完了フォルダが存在するかの確認
//プログラム終了要因
if(ffolchk()){
    echo "ただいま設定中です";
    exit;
}

//IPアドレスを取得
$x_ipcd=str_replace(':','',$_SERVER["REMOTE_ADDR"]);

//submitとticketが存在しかつ$_POSTと$_SESSIONのticketが同じ値かチェック(2重送信チェック)
if (isset($_POST["submit"])&&
    isset($_POST['ticket'])  &&
    isset($_SESSION['ticket']) &&
    $_POST['ticket'] == $_SESSION['ticket']){
    if($_POST["xquesr"]!=LASTP){
        //csvを更新する
        $xfname=fcsvup($x_ipcd,$_POST);
    }

    //次のページの番号を取得
    $xquesr=$_POST["next"];
}else{
    //回答完了してるかの確認
    if(fcheck($x_ipcd,FIDIR)){
        $xquesr=LASTP;
    }else{
        //回答途中かどうかの確認
        $xfname=fcheck($x_ipcd,UNDIR);
        //質問番号を取得
        $xquesr=fcocnt($xfname);
    }
}

//質問内容を取り出し
if (!($xqushi=fcntes($xquesr))){
    echo "ただいま設定中です";
    exit;    
}
//要素の作成
$xconts=fmakez($xqushi);
$_SESSION["ticket"]=md5(uniqid(rand(), TRUE));
$_SESSION["xfname"]=$xfname;
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
    <title><?= TITLE?></title>
</head>
<body>
<header>
    <?= TITLE?>
</header>
<main>
    <form method="post" action="form.php"  <?php if(isset($achkss)) echo "onsubmit='return frequi()'"?>>
        <?=$xconts?>
        <input type="hidden" name="ticket" value="<?= $_SESSION["ticket"];?>">
    <?php if($xquesr!=LASTP):?>
        <input type="hidden" name="xquesr" value="<?= $xquesr;?>" >
    <?php endif?>
    <?php if($xquesr!=LASTP):?>
        <button type="submit" name="submit">次へ</button>
    <?php endif ?>
    </form>
</main>
<font size=1>powered by <a href="https://github.com/taktak1/form"> form </a></font> 
</body>
</html>
<script>
<?php if(isset($achkss)):?>
    
    //phpから配列を継承xmashi[選択された名前=>移動先番号]
    const xmachk=<?= json_encode($achkss, JSON_UNESCAPED_UNICODE); ?>;
    //チェックボックス用変数(最大)
    const xopcou=<?= json_encode($amaxch, JSON_UNESCAPED_UNICODE); ?>;
    //チェックボックス用変数(最小)
    const xopmin=<?= json_encode($aminch, JSON_UNESCAPED_UNICODE); ?>;
    //チェックボックス用関数 選択上限を設定する
    function frequi(){
        //選択個数
        let xchkco=0;
        //カウント
        let i=0;
        //遷移先設定
        let chk=0;
        for (const elem of xmachk) {
            if(document.getElementById(elem[0]).checked){
                if(chk===0){
                    document.getElementById("next").value=elem[1];
                    chk=1;
                }
                ++xchkco;
            }
            ++i;
        }
        if(xchkco<xopmin){
            alert("選択は"+xopmin+"以上でお願いします。");
            return false;
        }
        if(xchkco>xopcou){
            alert("選択は"+xopcou+"以下でお願いします。");
            return false;
        }
        return true;
    }
<?php endif?>
<?php if(isset($ahairt)):?>
//セレクトボックスラジオボックス用関数
//選択された値によって遷移先を変更する
function fselct(value){
    //phpから配列を継承xmashi[選択された名前=>移動先番号]
    const xmashi=<?= json_encode($ahairt, JSON_UNESCAPED_UNICODE); ?>;
    document.getElementById("next").value = xmashi[value][0];
    document.getElementById("svalue").value=xmashi[value][1];
}
<?php endif?>
</script>
<style>
/*-----共通-----*/
*{
    padding:5px 0;
}
html,body{
    background-color:rgb(230, 230, 222);
    width: 100%;
    height: 100%;
}
main,header{
    width: 90%;
    max-width: 700px;
    margin: 0 auto;
}
header{
    font-size:20px;
    text-align: center;
    background-color: rgb(66, 65, 65);
    color: white;
    border-radius:10px 10px 0px 0px ;
    margin-top: 100px;
}
main{
    padding-top:20px;
    background-color: white;
    border-radius: 0px 0px 10px 10px;
    margin: 0 auto;
}
form{
    margin:0 10px;
    display: flex;
    flex-flow: column;
    justify-content: center;
    flex-wrap: wrap;
    align-items: center;
}
.qestion{
    margin:10px 0;
}
button{
    margin-top: 10px;
    background-color: rgb(24, 184, 224);
    font-size :18px;
    color:bisque;
    width:100%;
    height: 3em;
    border-radius: 10px 10px 10px 10px;
    font-family: inherit;
    font-size: inherit;
}

/*-----文字数制限　選択数上限-----*/
.atten{
    margin-top: 5px;
    color:rgb(248, 77, 77);
    font-size: 15px;
}

/*-----テキストボックス-----*/
input[type=text], textarea{
    padding-left: 4%;
    width: 70%;
    border-radius: 4px;
    border: none;
    box-shadow: 0 0 0 1px #ccc inset;
    appearance: none;
    font-size: 1em;
    text-align: center;
    -webkit-appearance: none;
    -moz-appearance: none;
    font-family: inherit;
    font-size: inherit;
}
input[type=text]:focus,textarea:focus {
    outline: 0;
    box-shadow: 0 0 0 2px rgb(33, 150, 243) inset;
}
input[type=text]{
    height: 2.4em;
}
textarea{
    height: 5em;
}
label{
    padding-top: 15px;
}

/*-----セレクトボックス-----*/
select{ 
    padding: 12px;
    width: 70%;
    font-size: 16px;
}

/*-----ラジオ・チェックボックス-----*/
.rachk{
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
}
article,article input {
    width:160px;
    height: 120px;
}
article {
    position: relative;
    margin: 5px;
}
article div {
    width: 100%;
    height: 100%;
    display: flex;
    border: 2px solid #535353;
    box-sizing: border-box;
    justify-content: center;
    align-items: center;
    line-height: 25px;
    transition: .5s ease;
    text-align: center;
    word-break: break-all ;
}
input[type=radio],input[type=checkbox] {
    position: absolute;
    top: 0;
    left: 0;
    opacity: 0;
    padding:5px 0px;
    margin: 0px;
}
input[type=radio]:checked ~ div ,input[type=checkbox]:checked ~ div {
    background-color: #333333;
    color:beige;
}
</style>
