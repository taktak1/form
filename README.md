This is a questionnaire form that rejects duplicate answers without imposing a burden on the user such as account registration.
This also doesn't let you go back to the previous question and redo the answer. The execution environment is php only, and no database is required.

アカウント登録等の負担をユーザに課す事無く、重複回答を拒絶するアンケートフォームです。前の質問に戻って回答のやり直しもさせません。 実行環境はphpのみで、データベースも必要ありません。


Este es un formulario de cuestionario que rechaza las respuestas duplicadas sin imponer una carga al usuario, como el registro de la cuenta.

Este é um formulário de questionário que rejeita respostas duplicadas sem impor um ônus ao usuário, como o registro da conta.

Il s'agit d'un formulaire de questionnaire qui rejette les réponses en double sans imposer à l'utilisateur une charge telle que l'enregistrement d'un compte.

Dies ist ein Fragebogenformular, das doppelte Antworten ablehnt, ohne den Benutzer zu belasten, wie z. B. eine Kontoregistrierung.

這是一種問卷調查表，可以拒絕重複的答案，而不會給用戶帶來諸如帳戶註冊之類的負擔。







***

***






**Structure of contents.csv**
```
question_no,type_no,description, ...
```


**type_no**
> 0 text only
> 
> 1 textbox textarea
> 
> 2 selectbox 
>
> 3 selectbox(random)
> 
> 4 radio button
> 
> 5 radio button(random)
> 
> 6 check button
> 
> 7 check button(random)





**0 text only**
```
question_no,0,description, Transition_destination_no
```


**1 textbox textarea**

```
question_no,1,description, Transition_destination_no, (0 textbox | 1textarea ), 0 , Character_limit, (0  Not required| 1  required)
```



