/**
 * Created by 旭阳 on 2016/4/2.
 */
var dialogue = function (){
    var word;
    var model = 2;
    var blankboard =
        '<div style="padding-top: 300px">'+
            '<label style="width: 100%;font-size:80px;text-align: center;color: #aaa">Touch Me</label>'+
        '</div>';
    function init() {
        model = CrxzyOS.Cookie.getCookie("show_model")?CrxzyOS.Cookie.getCookie("show_model"):2;
        freshWord();
        bindEvent();
    }
    function bindEvent()
    {
        $("#dialogue_board").click(function(){
            $("#dialogue_board").html(
                "<div style=\"text-align: center;font-size: 72px\" class=\"list-group\">"+
                    "<a class=\"list-group-item\">"+ word[1]+ "</a>"+
                    "<a class=\"list-group-item\">"+ word[2]+ "</a>"+
                    "<a class=\"list-group-item\">"+ word[3]+ "</a>"+
                    "<a class=\"list-group-item\">"+ word[4]+ "</a>"+
                "</div>"
            );
        });
        $("#know_button").click(function(){
                $.post("index.php",{
                    'componentName':'dialogue',
                    'requireKind':'Answer',
                    'familiarity':word[5],
                    'nexttime':word[7],
                    'temp_status':word[8],
                    'wordId':word[0],
                    'answer':"yes"},function(html){
                    alert(html);
                });
                freshWord();
            }
        );
        $("#unknow_button").click(function(){
                console.log(word);
                $.post("index.php",
                    {'componentName':'dialogue',
                        'requireKind':'Answer',
                        'familiarity':word[5],
                        'nexttime':word[7],
                        'temp_status':word[8],
                        'wordId':word[0],
                        'answer':"no"}, function(html){
                    alert(html);
                });
                freshWord();
            }
        );
        $("#show_chinese").click(function(){
            model = 3;
            CrxzyOS.Cookie.setCookie("show_model",3);
            freshWord();
        });
        $("#show_japanese").click(function(){
            model = 2;
            CrxzyOS.Cookie.setCookie("show_model",2);
            freshWord();
        });
    }
    function freshWord(){
        $.post("index.php",{'componentName':'dialogue','requireKind':'GetWord'},function(html){
            $("#dialogue_word").html(html[model]);
            $("#dialogue_board").html(blankboard);
            word = html;
        },"json");
    }
    return {
        'freshWord':freshWord,
        'init':init,
    }
};

$(function(){
    dialogue = new dialogue;
    dialogue.init();
})
